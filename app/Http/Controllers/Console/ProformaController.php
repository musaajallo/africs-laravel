<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Concerns\RendersPdf;
use App\Http\Controllers\Controller;
use App\Http\Requests\Console\ProformaRequest;
use App\Models\Client;
use App\Models\Proforma;
use App\Models\Project;
use App\Support\ActivityPresenter;
use App\Support\ProformaMeta;
use App\Support\Sequence;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ProformaController extends Controller
{
    use RendersPdf;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Proforma::class);

        $filters = [
            'search' => $request->string('search')->trim()->value(),
            'status' => $request->string('status')->trim()->value(),
            'client' => $request->integer('client') ?: null,
        ];

        $proformas = Proforma::query()
            ->with('client:id,name')
            ->search($filters['search'])
            ->when(
                in_array($filters['status'], ProformaMeta::statusKeys(), true),
                fn ($q) => $q->where('status', $filters['status']),
                fn ($q) => $q->whereIn('status', ProformaMeta::OPEN_STATUSES),
            )
            ->when($filters['client'], fn ($q) => $q->where('client_id', $filters['client']))
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Proforma $proforma) => [
                'id' => $proforma->id,
                'number' => $proforma->number,
                'client' => $proforma->client?->name,
                'status' => $proforma->status,
                'currency' => $proforma->currency,
                'total' => $proforma->total,
                'issue_date' => $proforma->issue_date?->toDateString(),
                'valid_until' => $proforma->valid_until?->toDateString(),
                'can_convert' => $proforma->canBeConverted(),
            ]);

        return Inertia::render('Console/Proformas/Index', [
            'proformas' => $proformas,
            'filters' => $filters,
            'statuses' => ProformaMeta::STATUSES,
            'clients' => Client::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Proforma::class);

        return Inertia::render('Console/Proformas/Create', [
            ...$this->formOptions(),
            'presetClientId' => $request->integer('client') ?: null,
            'presetProjectId' => $request->integer('project') ?: null,
            'nextNumber' => Sequence::peek('proforma', 'PRO'),
        ]);
    }

    public function store(ProformaRequest $request): RedirectResponse
    {
        $this->authorize('create', Proforma::class);

        $proforma = DB::transaction(function () use ($request) {
            $attributes = $request->documentAttributes();
            $attributes['tax_label'] = $attributes['tax_label'] ?: Settings::get('billing.tax_label', 'VAT');

            $proforma = new Proforma($attributes);
            $proforma->number = Sequence::next('proforma', 'PRO');
            $proforma->created_by = $request->user()->id;
            $proforma->save();

            $proforma->lines()->createMany($request->lineRows());
            $proforma->load('lines');
            $proforma->saveWithTotals();

            return $proforma;
        });

        return redirect()
            ->route('console.proformas.show', $proforma)
            ->with('success', "Proforma {$proforma->number} created.");
    }

    public function show(Proforma $proforma): Response
    {
        $this->authorize('view', $proforma);

        $proforma->load(['client:id,name', 'project:id,name', 'lines', 'createdBy:id,name', 'convertedInvoice:id,number']);

        return Inertia::render('Console/Proformas/Show', [
            'proforma' => $this->present($proforma),
            'manualStatuses' => ProformaMeta::MANUAL_STATUSES,
            'activity' => Activity::forSubject($proforma)
                ->with('causer:id,name')
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn (Activity $a) => ActivityPresenter::present($a)),
        ]);
    }

    public function edit(Proforma $proforma): Response
    {
        $this->authorize('update', $proforma);

        $proforma->load(['lines', 'client:id,name']);

        return Inertia::render('Console/Proformas/Edit', [
            'proforma' => $this->present($proforma),
            ...$this->formOptions(),
        ]);
    }

    public function update(ProformaRequest $request, Proforma $proforma): RedirectResponse
    {
        $this->authorize('update', $proforma);

        DB::transaction(function () use ($request, $proforma) {
            $attributes = $request->documentAttributes();
            $attributes['tax_label'] = $attributes['tax_label'] ?: Settings::get('billing.tax_label', 'VAT');
            $proforma->update($attributes);

            $proforma->lines()->delete();
            $proforma->lines()->createMany($request->lineRows());
            $proforma->load('lines');
            $proforma->saveWithTotals();
        });

        return redirect()
            ->route('console.proformas.show', $proforma)
            ->with('success', 'Proforma updated.');
    }

    public function destroy(Proforma $proforma): RedirectResponse
    {
        $this->authorize('delete', $proforma);

        $proforma->delete();

        return redirect()
            ->route('console.proformas.index')
            ->with('success', "Proforma {$proforma->number} archived.");
    }

    public function restore(int $proforma): RedirectResponse
    {
        $proforma = Proforma::onlyTrashed()->findOrFail($proforma);
        $this->authorize('restore', $proforma);

        $proforma->restore();

        return redirect()
            ->route('console.proformas.show', $proforma)
            ->with('success', 'Proforma restored.');
    }

    /** Move a proforma through its lifecycle by hand. */
    public function status(Request $request, Proforma $proforma): RedirectResponse
    {
        $this->authorize('manage', $proforma);

        $data = $request->validate([
            'status' => ['required', Rule::in(ProformaMeta::MANUAL_STATUSES)],
        ]);

        if ($proforma->isConverted()) {
            throw ValidationException::withMessages([
                'status' => 'A converted proforma is locked.',
            ]);
        }

        $proforma->update(['status' => $data['status']]);

        return back()->with('success', "Proforma marked {$data['status']}.");
    }

    /** Convert a proforma into a draft invoice. */
    public function convert(Request $request, Proforma $proforma): RedirectResponse
    {
        $this->authorize('manage', $proforma);

        if (! $proforma->canBeConverted()) {
            throw ValidationException::withMessages([
                'status' => 'This proforma has already been converted to an invoice.',
            ]);
        }

        $invoice = $proforma->convertToInvoice($request->user()->id);

        return redirect()
            ->route('console.invoices.show', $invoice)
            ->with('success', "{$proforma->number} converted to {$invoice->number}.");
    }

    public function pdf(Request $request, Proforma $proforma): HttpResponse
    {
        $this->authorize('view', $proforma);

        $proforma->load(['client', 'project:id,name', 'lines']);

        return $this->pdfResponse($request, 'pdf.document', [
            'doc' => $proforma,
            'kind' => 'Proforma',
            'company' => Settings::get('company'),
            'base' => Settings::baseCurrency(),
        ], "{$proforma->number}.pdf");
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
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'client_id']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function present(Proforma $proforma): array
    {
        return [
            'id' => $proforma->id,
            'number' => $proforma->number,
            'status' => $proforma->status,
            'client_id' => $proforma->client_id,
            'client' => $proforma->client?->name,
            'project_id' => $proforma->project_id,
            'project' => $proforma->project?->name,
            'currency' => $proforma->currency,
            'fx_rate' => (string) $proforma->fx_rate,
            'issue_date' => $proforma->issue_date?->toDateString(),
            'valid_until' => $proforma->valid_until?->toDateString(),
            'tax_label' => $proforma->tax_label,
            'tax_rate' => (string) $proforma->tax_rate,
            'notes' => $proforma->notes,
            'terms' => $proforma->terms,
            'subtotal' => $proforma->subtotal,
            'tax_total' => $proforma->tax_total,
            'total' => $proforma->total,
            'base_total' => $proforma->base_total,
            'converted_invoice' => $proforma->convertedInvoice
                ? ['id' => $proforma->convertedInvoice->id, 'number' => $proforma->convertedInvoice->number]
                : null,
            'created_by' => $proforma->createdBy?->name,
            'created_at' => $proforma->created_at?->toDateString(),
            'editable' => $proforma->isEditable(),
            'can_convert' => $proforma->canBeConverted(),
            'archived' => $proforma->trashed(),
            'lines' => $proforma->lines->map(fn ($line) => [
                'id' => $line->id,
                'description' => $line->description,
                'quantity' => (string) $line->quantity,
                'unit_price' => (string) $line->unit_price,
                'line_total' => (string) $line->line_total,
            ])->all(),
        ];
    }
}
