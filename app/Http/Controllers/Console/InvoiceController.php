<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Http\Requests\Console\InvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Support\ActivityPresenter;
use App\Support\InvoiceMeta;
use App\Support\Sequence;
use App\Support\Settings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Invoice::class);

        $filters = [
            'search' => $request->string('search')->trim()->value(),
            'status' => $request->string('status')->trim()->value(),
            'client' => $request->integer('client') ?: null,
        ];

        $invoices = Invoice::query()
            ->with('client:id,name')
            ->search($filters['search'])
            ->when(
                in_array($filters['status'], InvoiceMeta::statusKeys(), true),
                fn ($q) => $q->where('status', $filters['status']),
                fn ($q) => $q->whereIn('status', InvoiceMeta::OPEN_STATUSES),
            )
            ->when($filters['client'], fn ($q) => $q->where('client_id', $filters['client']))
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Invoice $invoice) => [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'client' => $invoice->client?->name,
                'status' => $invoice->status,
                'currency' => $invoice->currency,
                'total' => $invoice->total,
                'issue_date' => $invoice->issue_date?->toDateString(),
                'due_date' => $invoice->due_date?->toDateString(),
            ]);

        return Inertia::render('Console/Invoices/Index', [
            'invoices' => $invoices,
            'filters' => $filters,
            'statuses' => InvoiceMeta::STATUSES,
            'clients' => Client::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Invoice::class);

        return Inertia::render('Console/Invoices/Create', [
            ...$this->formOptions(),
            'presetClientId' => $request->integer('client') ?: null,
            'presetProjectId' => $request->integer('project') ?: null,
            'nextNumber' => Sequence::peek('invoice', 'INV'),
        ]);
    }

    public function store(InvoiceRequest $request): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $invoice = DB::transaction(function () use ($request) {
            $attributes = $request->documentAttributes();
            $attributes['tax_label'] = $attributes['tax_label'] ?: Settings::get('billing.tax_label', 'VAT');

            $invoice = new Invoice($attributes);
            $invoice->number = Sequence::next('invoice', 'INV');
            $invoice->created_by = $request->user()->id;
            $invoice->save();

            $invoice->lines()->createMany($request->lineRows());
            $invoice->load('lines');
            $invoice->saveWithTotals();

            return $invoice;
        });

        return redirect()
            ->route('console.invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->number} created.");
    }

    public function show(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        $invoice->load([
            'client:id,name', 'project:id,name', 'lines', 'createdBy:id,name', 'proforma:id,number',
            'allocations.payment' => fn ($q) => $q->select('id', 'number', 'paid_on', 'method', 'currency'),
        ]);

        return Inertia::render('Console/Invoices/Show', [
            'invoice' => $this->present($invoice),
            'manualStatuses' => InvoiceMeta::MANUAL_STATUSES,
            'activity' => Activity::forSubject($invoice)
                ->with('causer:id,name')
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn (Activity $a) => ActivityPresenter::present($a)),
        ]);
    }

    public function edit(Invoice $invoice): Response
    {
        $this->authorize('update', $invoice);

        $invoice->load(['lines', 'client:id,name']);

        return Inertia::render('Console/Invoices/Edit', [
            'invoice' => $this->present($invoice),
            ...$this->formOptions(),
        ]);
    }

    public function update(InvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        DB::transaction(function () use ($request, $invoice) {
            $attributes = $request->documentAttributes();
            $attributes['tax_label'] = $attributes['tax_label'] ?: Settings::get('billing.tax_label', 'VAT');
            $invoice->update($attributes);

            $invoice->lines()->delete();
            $invoice->lines()->createMany($request->lineRows());
            $invoice->load('lines');
            $invoice->saveWithTotals();
        });

        return redirect()
            ->route('console.invoices.show', $invoice)
            ->with('success', 'Invoice updated.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorize('delete', $invoice);

        $invoice->delete();

        return redirect()
            ->route('console.invoices.index')
            ->with('success', "Invoice {$invoice->number} archived.");
    }

    public function restore(int $invoice): RedirectResponse
    {
        $invoice = Invoice::onlyTrashed()->findOrFail($invoice);
        $this->authorize('restore', $invoice);

        $invoice->restore();

        return redirect()
            ->route('console.invoices.show', $invoice)
            ->with('success', 'Invoice restored.');
    }

    public function status(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('manage', $invoice);

        $data = $request->validate([
            'status' => ['required', Rule::in(InvoiceMeta::MANUAL_STATUSES)],
        ]);

        $invoice->update(['status' => $data['status']]);

        return back()->with('success', "Invoice marked {$data['status']}.");
    }

    public function pdf(Invoice $invoice): HttpResponse
    {
        $this->authorize('view', $invoice);

        $invoice->load(['client', 'project:id,name', 'lines']);

        $pdf = Pdf::loadView('pdf.document', [
            'doc' => $invoice,
            'kind' => 'Invoice',
            'company' => Settings::get('company'),
        ]);

        return $pdf->download("{$invoice->number}.pdf");
    }

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [
            'currencies' => Settings::enabledCurrencies(),
            'baseCurrency' => Settings::baseCurrency(),
            'defaultTax' => [
                'label' => Settings::get('billing.tax_label', 'VAT'),
                'rate' => (float) Settings::get('billing.tax_rate', 0),
            ],
            'paymentTermsDays' => Settings::paymentTermsDays(),
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'client_id']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function present(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'number' => $invoice->number,
            'status' => $invoice->status,
            'client_id' => $invoice->client_id,
            'client' => $invoice->client?->name,
            'project_id' => $invoice->project_id,
            'project' => $invoice->project?->name,
            'proforma' => $invoice->proforma
                ? ['id' => $invoice->proforma->id, 'number' => $invoice->proforma->number]
                : null,
            'currency' => $invoice->currency,
            'fx_rate' => (string) $invoice->fx_rate,
            'issue_date' => $invoice->issue_date?->toDateString(),
            'due_date' => $invoice->due_date?->toDateString(),
            'tax_label' => $invoice->tax_label,
            'tax_rate' => (string) $invoice->tax_rate,
            'notes' => $invoice->notes,
            'terms' => $invoice->terms,
            'subtotal' => $invoice->subtotal,
            'tax_total' => $invoice->tax_total,
            'total' => $invoice->total,
            'base_total' => $invoice->base_total,
            'amount_paid' => $invoice->amount_paid,
            'balance' => $invoice->balance(),
            'payments' => $invoice->relationLoaded('allocations')
                ? $invoice->allocations->map(fn ($allocation) => [
                    'id' => $allocation->payment?->id,
                    'number' => $allocation->payment?->number,
                    'paid_on' => $allocation->payment?->paid_on?->toDateString(),
                    'method' => $allocation->payment?->method,
                    'amount' => $allocation->amount,
                ])->filter(fn ($row) => $row['id'] !== null)->values()->all()
                : [],
            'created_by' => $invoice->createdBy?->name,
            'created_at' => $invoice->created_at?->toDateString(),
            'editable' => $invoice->isEditable(),
            'archived' => $invoice->trashed(),
            'lines' => $invoice->lines->map(fn ($line) => [
                'id' => $line->id,
                'description' => $line->description,
                'quantity' => (string) $line->quantity,
                'unit_price' => (string) $line->unit_price,
                'line_total' => (string) $line->line_total,
            ])->all(),
        ];
    }
}
