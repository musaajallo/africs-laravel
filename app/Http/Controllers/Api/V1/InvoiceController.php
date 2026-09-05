<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Console\InvoiceRequest;
use App\Http\Resources\Api\V1\InvoiceResource;
use App\Models\Invoice;
use App\Models\Proforma;
use App\Support\InvoiceMeta;
use App\Support\Sequence;
use App\Support\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Invoice::class);

        $invoices = Invoice::query()
            ->with('lines')
            ->search($request->string('search')->trim()->value())
            ->when(
                in_array($request->query('status'), InvoiceMeta::statusKeys(), true),
                fn ($q) => $q->where('status', $request->query('status')),
            )
            ->when($request->integer('client'), fn ($q, $id) => $q->where('client_id', $id))
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 25), 100));

        return InvoiceResource::collection($invoices);
    }

    public function show(Invoice $invoice): InvoiceResource
    {
        $this->authorize('view', $invoice);

        return new InvoiceResource($invoice->load('lines'));
    }

    public function store(InvoiceRequest $request): JsonResponse
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

        return (new InvoiceResource($invoice->load('lines')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(InvoiceRequest $request, Invoice $invoice): InvoiceResource
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

        return new InvoiceResource($invoice->load('lines'));
    }

    public function destroy(Invoice $invoice): Response
    {
        $this->authorize('delete', $invoice);

        $invoice->delete();

        return response()->noContent();
    }

    /** Convert a proforma into a draft invoice. */
    public function convert(Request $request, Proforma $proforma): JsonResponse
    {
        $this->authorize('create', Invoice::class);

        if (! $proforma->canBeConverted()) {
            throw ValidationException::withMessages([
                'status' => 'This proforma has already been converted to an invoice.',
            ]);
        }

        $invoice = $proforma->convertToInvoice($request->user()->id);

        return (new InvoiceResource($invoice->load('lines')))
            ->response()
            ->setStatusCode(201);
    }
}
