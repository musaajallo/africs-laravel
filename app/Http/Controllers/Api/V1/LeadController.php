<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Console\LeadFormRequest;
use App\Http\Resources\Api\V1\LeadResource;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LeadController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Lead::class);

        $leads = Lead::query()
            ->search($request->string('search')->trim()->value())
            ->when(
                in_array($request->query('status'), Lead::STATUSES, true),
                fn ($q) => $q->where('status', $request->query('status')),
            )
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 25), 100));

        return LeadResource::collection($leads);
    }

    public function show(Lead $lead): LeadResource
    {
        $this->authorize('view', $lead);

        return new LeadResource($lead);
    }

    public function store(LeadFormRequest $request): JsonResponse
    {
        $this->authorize('create', Lead::class);

        $lead = Lead::create($request->leadAttributes());

        return (new LeadResource($lead))->response()->setStatusCode(201);
    }
}
