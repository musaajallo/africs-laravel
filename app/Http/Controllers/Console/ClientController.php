<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Http\Requests\Console\ClientRequest;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Client::class);

        $filters = [
            'search' => $request->string('search')->trim()->value(),
            'status' => $request->string('status')->trim()->value(),
        ];

        $clients = Client::query()
            ->with('owner:id,name')
            ->withCount('contacts')
            ->search($filters['search'])
            ->when(
                in_array($filters['status'], ['active', 'inactive'], true),
                fn ($query) => $query->where('status', $filters['status']),
            )
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Client $client) => [
                'id' => $client->id,
                'name' => $client->name,
                'type' => $client->type,
                'status' => $client->status,
                'city' => $client->city,
                'country' => $client->country,
                'currency' => $client->currency,
                'owner' => $client->owner?->name,
                'contacts_count' => $client->contacts_count,
            ]);

        return Inertia::render('Console/Clients/Index', [
            'clients' => $clients,
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Client::class);

        return Inertia::render('Console/Clients/Create', $this->formOptions());
    }

    public function store(ClientRequest $request): RedirectResponse
    {
        $this->authorize('create', Client::class);

        $client = DB::transaction(function () use ($request) {
            $client = new Client($request->clientAttributes());
            $client->created_by = $request->user()->id;
            $client->save();

            $this->syncContacts($client, $request->contactRows());

            return $client;
        });

        return redirect()
            ->route('console.clients.show', $client)
            ->with('success', 'Client created.');
    }

    public function show(Client $client): Response
    {
        $this->authorize('view', $client);

        $client->load(['contacts' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('name'), 'owner:id,name', 'createdBy:id,name']);

        return Inertia::render('Console/Clients/Show', [
            'client' => $this->present($client),
        ]);
    }

    public function edit(Client $client): Response
    {
        $this->authorize('update', $client);

        $client->load(['contacts' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('name')]);

        return Inertia::render('Console/Clients/Edit', [
            'client' => $this->present($client),
            ...$this->formOptions(),
        ]);
    }

    public function update(ClientRequest $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        DB::transaction(function () use ($request, $client) {
            $client->update($request->clientAttributes());
            $this->syncContacts($client, $request->contactRows());
        });

        return redirect()
            ->route('console.clients.show', $client)
            ->with('success', 'Client updated.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        $client->delete();

        return redirect()
            ->route('console.clients.index')
            ->with('success', 'Client archived.');
    }

    public function restore(int $client): RedirectResponse
    {
        $client = Client::onlyTrashed()->findOrFail($client);
        $this->authorize('restore', $client);

        $client->restore();

        return redirect()
            ->route('console.clients.show', $client)
            ->with('success', 'Client restored.');
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    protected function syncContacts(Client $client, array $rows): void
    {
        $keptIds = [];

        foreach ($rows as $row) {
            $attributes = collect($row)->except('id')->all();

            $contact = isset($row['id'])
                ? $client->contacts()->whereKey($row['id'])->first()
                : null;

            if ($contact) {
                $contact->update($attributes);
            } else {
                $contact = $client->contacts()->create($attributes);
            }

            $keptIds[] = $contact->id;
        }

        $client->contacts()->whereKeyNot($keptIds)->delete();
    }

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [
            'currencies' => ClientRequest::CURRENCIES,
            'owners' => User::query()->active()->orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function present(Client $client): array
    {
        return [
            'id' => $client->id,
            'name' => $client->name,
            'type' => $client->type,
            'status' => $client->status,
            'email' => $client->email,
            'phone' => $client->phone,
            'website' => $client->website,
            'tax_number' => $client->tax_number,
            'currency' => $client->currency,
            'billing_address' => $client->billing_address,
            'city' => $client->city,
            'country' => $client->country,
            'notes' => $client->notes,
            'owner_id' => $client->owner_id,
            'owner' => $client->owner?->name,
            'created_by' => $client->createdBy?->name,
            'created_at' => $client->created_at?->toDateString(),
            'archived' => $client->trashed(),
            'contacts' => $client->contacts->map(fn ($contact) => [
                'id' => $contact->id,
                'name' => $contact->name,
                'title' => $contact->title,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'is_primary' => $contact->is_primary,
                'notes' => $contact->notes,
            ])->all(),
        ];
    }
}
