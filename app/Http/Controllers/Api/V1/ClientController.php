<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Console\ClientRequest;
use App\Http\Resources\Api\V1\ClientResource;
use App\Models\Client;
use App\Support\ClientTypes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Client::class);

        $clients = Client::query()
            ->with(['tags:id,name'])
            ->withCount('contacts')
            ->search($request->string('search')->trim()->value())
            ->when(
                in_array($request->query('status'), ['active', 'inactive'], true),
                fn ($q) => $q->where('status', $request->query('status')),
            )
            ->when(
                in_array($request->query('type'), ClientTypes::TYPES, true),
                fn ($q) => $q->where('type', $request->query('type')),
            )
            ->tagged($request->string('tag')->trim()->value() ?: null)
            ->orderBy('name')
            ->paginate(min((int) $request->integer('per_page', 25), 100));

        return ClientResource::collection($clients);
    }

    public function show(Client $client): ClientResource
    {
        $this->authorize('view', $client);

        return new ClientResource($client->load(['contacts', 'tags:id,name']));
    }

    public function store(ClientRequest $request): JsonResponse
    {
        $this->authorize('create', Client::class);

        $client = DB::transaction(function () use ($request) {
            $client = new Client($request->clientAttributes());
            $client->created_by = $request->user()->id;
            $client->save();
            $client->syncTagsByName($request->tagNames());

            return $client;
        });

        return (new ClientResource($client->load(['contacts', 'tags:id,name'])))
            ->response()
            ->setStatusCode(201);
    }

    public function update(ClientRequest $request, Client $client): ClientResource
    {
        $this->authorize('update', $client);

        DB::transaction(function () use ($request, $client) {
            $client->update($request->clientAttributes());
            $client->syncTagsByName($request->tagNames());
        });

        return new ClientResource($client->load(['contacts', 'tags:id,name']));
    }

    public function destroy(Client $client): Response
    {
        $this->authorize('delete', $client);

        $client->delete();

        return response()->noContent();
    }
}
