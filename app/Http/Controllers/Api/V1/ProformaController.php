<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Console\ProformaRequest;
use App\Http\Resources\Api\V1\ProformaResource;
use App\Models\Proforma;
use App\Support\ProformaMeta;
use App\Support\Sequence;
use App\Support\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ProformaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Proforma::class);

        $proformas = Proforma::query()
            ->with('lines')
            ->search($request->string('search')->trim()->value())
            ->when(
                in_array($request->query('status'), ProformaMeta::statusKeys(), true),
                fn ($q) => $q->where('status', $request->query('status')),
            )
            ->when($request->integer('client'), fn ($q, $id) => $q->where('client_id', $id))
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 25), 100));

        return ProformaResource::collection($proformas);
    }

    public function show(Proforma $proforma): ProformaResource
    {
        $this->authorize('view', $proforma);

        return new ProformaResource($proforma->load('lines'));
    }

    public function store(ProformaRequest $request): JsonResponse
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

        return (new ProformaResource($proforma->load('lines')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(ProformaRequest $request, Proforma $proforma): ProformaResource
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

        return new ProformaResource($proforma->load('lines'));
    }

    public function destroy(Proforma $proforma): Response
    {
        $this->authorize('delete', $proforma);

        $proforma->delete();

        return response()->noContent();
    }
}
