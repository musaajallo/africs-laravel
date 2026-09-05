<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Console\PaymentRequest;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Models\Payment;
use App\Support\Sequence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Payment::class);

        $payments = Payment::query()
            ->with('allocations')
            ->search($request->string('search')->trim()->value())
            ->when($request->integer('client'), fn ($q, $id) => $q->where('client_id', $id))
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 25), 100));

        return PaymentResource::collection($payments);
    }

    public function show(Payment $payment): PaymentResource
    {
        $this->authorize('view', $payment);

        return new PaymentResource($payment->load('allocations'));
    }

    public function store(PaymentRequest $request): JsonResponse
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

        return (new PaymentResource($payment->load('allocations')))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Payment $payment): Response
    {
        $this->authorize('delete', $payment);

        DB::transaction(function () use ($payment) {
            $payment->applyAllocations([]);
            $payment->delete();
        });

        return response()->noContent();
    }
}
