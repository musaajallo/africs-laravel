<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Http\Requests\Console\PaymentRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Support\ActivityPresenter;
use App\Support\Sequence;
use App\Support\Settings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Payment::class);

        $filters = [
            'search' => $request->string('search')->trim()->value(),
            'method' => $request->string('method')->trim()->value(),
            'client' => $request->integer('client') ?: null,
        ];

        $payments = Payment::query()
            ->with('client:id,name')
            ->withCount('allocations')
            ->search($filters['search'])
            ->when($filters['method'], fn ($q) => $q->where('method', $filters['method']))
            ->when($filters['client'], fn ($q) => $q->where('client_id', $filters['client']))
            ->orderByDesc('paid_on')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Payment $payment) => [
                'id' => $payment->id,
                'number' => $payment->number,
                'client' => $payment->client?->name,
                'currency' => $payment->currency,
                'amount' => $payment->amount,
                'allocated_amount' => $payment->allocated_amount,
                'method' => $payment->method,
                'paid_on' => $payment->paid_on?->toDateString(),
                'allocations_count' => $payment->allocations_count,
            ]);

        return Inertia::render('Console/Payments/Index', [
            'payments' => $payments,
            'filters' => $filters,
            'methods' => Settings::paymentMethods(),
            'clients' => Client::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Payment::class);

        $invoice = $request->integer('invoice')
            ? Invoice::with('client:id,name')->find($request->integer('invoice'))
            : null;

        return Inertia::render('Console/Payments/Create', [
            ...$this->formOptions(),
            'nextNumber' => Sequence::peek('receipt', 'RCT'),
            'presetInvoice' => $invoice ? [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'client_id' => $invoice->client_id,
                'client' => $invoice->client?->name,
                'currency' => $invoice->currency,
                'balance' => $invoice->balance(),
            ] : null,
        ]);
    }

    public function store(PaymentRequest $request): RedirectResponse
    {
        $this->authorize('create', Payment::class);

        $payment = DB::transaction(function () use ($request) {
            $payment = new Payment($request->paymentAttributes());
            $payment->number = Sequence::next('receipt', 'RCT');
            $payment->created_by = $request->user()->id;
            $payment->save();

            $payment->applyAllocations($request->allocationRows());

            return $payment;
        });

        return redirect()
            ->route('console.payments.show', $payment)
            ->with('success', "Payment {$payment->number} recorded.");
    }

    public function show(Payment $payment): Response
    {
        $this->authorize('view', $payment);

        $payment->load(['client:id,name', 'createdBy:id,name', 'allocations.invoice:id,number,currency,total,amount_paid,status']);

        return Inertia::render('Console/Payments/Show', [
            'payment' => $this->present($payment),
            'activity' => Activity::forSubject($payment)
                ->with('causer:id,name')
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn (Activity $a) => ActivityPresenter::present($a)),
        ]);
    }

    public function edit(Payment $payment): Response
    {
        $this->authorize('update', $payment);

        $payment->load(['client:id,name', 'allocations']);

        return Inertia::render('Console/Payments/Edit', [
            'payment' => $this->present($payment),
            ...$this->formOptions(),
        ]);
    }

    public function update(PaymentRequest $request, Payment $payment): RedirectResponse
    {
        $this->authorize('update', $payment);

        DB::transaction(function () use ($request, $payment) {
            $payment->update($request->paymentAttributes());
            $payment->applyAllocations($request->allocationRows());
        });

        return redirect()
            ->route('console.payments.show', $payment)
            ->with('success', 'Payment updated.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $this->authorize('delete', $payment);

        DB::transaction(function () use ($payment) {
            $payment->applyAllocations([]); // releases every invoice it touched
            $payment->delete();
        });

        return redirect()
            ->route('console.payments.index')
            ->with('success', "Payment {$payment->number} removed.");
    }

    public function restore(int $payment): RedirectResponse
    {
        $payment = Payment::onlyTrashed()->findOrFail($payment);
        $this->authorize('restore', $payment);

        $payment->restore();

        return redirect()
            ->route('console.payments.show', $payment)
            ->with('success', 'Payment restored. Re-allocate it to invoices as needed.');
    }

    public function pdf(Payment $payment): HttpResponse
    {
        $this->authorize('view', $payment);

        $payment->load(['client', 'allocations.invoice:id,number']);

        $pdf = Pdf::loadView('pdf.receipt', [
            'payment' => $payment,
            'company' => Settings::get('company'),
        ]);

        return $pdf->download("{$payment->number}.pdf");
    }

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [
            'currencies' => Settings::enabledCurrencies(),
            'baseCurrency' => Settings::baseCurrency(),
            'methods' => Settings::paymentMethods(),
            'clients' => Client::orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * Open invoices for a client in a given currency, for the allocation picker.
     */
    public function invoicesForClient(Request $request): array
    {
        $this->authorize('create', Payment::class);

        return Invoice::query()
            ->where('client_id', $request->integer('client'))
            ->where('currency', strtoupper((string) $request->string('currency')))
            ->whereNotIn('status', ['draft', 'void'])
            ->orderBy('due_date')
            ->get(['id', 'number', 'currency', 'total', 'amount_paid', 'due_date', 'status'])
            ->map(fn (Invoice $invoice) => [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'total' => $invoice->total,
                'balance' => $invoice->balance(),
                'due_date' => $invoice->due_date?->toDateString(),
                'status' => $invoice->status,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function present(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'number' => $payment->number,
            'client_id' => $payment->client_id,
            'client' => $payment->client?->name,
            'currency' => $payment->currency,
            'fx_rate' => (string) $payment->fx_rate,
            'amount' => $payment->amount,
            'allocated_amount' => $payment->allocated_amount,
            'unallocated_amount' => $payment->unallocatedAmount(),
            'method' => $payment->method,
            'reference' => $payment->reference,
            'paid_on' => $payment->paid_on?->toDateString(),
            'notes' => $payment->notes,
            'created_by' => $payment->createdBy?->name,
            'created_at' => $payment->created_at?->toDateString(),
            'archived' => $payment->trashed(),
            'allocations' => $payment->allocations->map(fn ($allocation) => [
                'invoice_id' => $allocation->invoice_id,
                'invoice_number' => $allocation->invoice?->number,
                'invoice_status' => $allocation->invoice?->status,
                'amount' => $allocation->amount,
            ])->all(),
        ];
    }
}
