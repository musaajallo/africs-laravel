<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Http\Requests\Console\ClientRequest;
use App\Models\Client;
use App\Models\Tag;
use App\Models\User;
use App\Support\ActivityPresenter;
use App\Support\ClientTypes;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ClientController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Client::class);

        $filters = [
            'search' => $request->string('search')->trim()->value(),
            'status' => $request->string('status')->trim()->value(),
            'type' => $request->string('type')->trim()->value(),
            'tag' => $request->string('tag')->trim()->value(),
        ];

        $clients = Client::query()
            ->with(['owner:id,name', 'tags:id,name,slug,color'])
            ->withCount('contacts')
            ->search($filters['search'])
            ->when(
                in_array($filters['status'], ['active', 'inactive'], true),
                fn ($query) => $query->where('status', $filters['status']),
            )
            ->when(
                in_array($filters['type'], ClientTypes::TYPES, true),
                fn ($query) => $query->where('type', $filters['type']),
            )
            ->tagged($filters['tag'] ?: null)
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Client $client) => [
                'id' => $client->id,
                'name' => $client->name,
                'type' => $client->type,
                'category' => $client->category,
                'status' => $client->status,
                'city' => $client->city,
                'country' => $client->country,
                'currency' => $client->currency,
                'owner' => $client->owner?->name,
                'contacts_count' => $client->contacts_count,
                'tags' => $client->tags->map(fn (Tag $tag) => [
                    'name' => $tag->name, 'color' => $tag->color,
                ])->all(),
            ]);

        return Inertia::render('Console/Clients/Index', [
            'clients' => $clients,
            'filters' => $filters,
            'types' => ClientTypes::TYPES,
            'tags' => Tag::orderBy('name')->get(['name', 'slug', 'color']),
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
            $client->syncTagsByName($request->tagNames());

            return $client;
        });

        $this->logTagChange($client, [], $request->tagNames());

        return redirect()
            ->route('console.clients.show', $client)
            ->with('success', 'Client created.');
    }

    public function show(Client $client): Response
    {
        $this->authorize('view', $client);

        $client->load([
            'contacts' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('name'),
            'owner:id,name', 'createdBy:id,name', 'tags:id,name,slug,color',
            'projects' => fn ($q) => $q->orderByDesc('created_at'),
            'proformas' => fn ($q) => $q->latest('issue_date')->latest('id')->limit(10),
        ]);

        $activity = Activity::forSubject($client)
            ->with('causer:id,name')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (Activity $a) => ActivityPresenter::present($a));

        return Inertia::render('Console/Clients/Show', [
            'client' => $this->present($client),
            'activity' => $activity,
        ]);
    }

    public function edit(Client $client): Response
    {
        $this->authorize('update', $client);

        $client->load([
            'contacts' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('name'),
            'tags:id,name,slug,color',
        ]);

        return Inertia::render('Console/Clients/Edit', [
            'client' => $this->present($client),
            ...$this->formOptions(),
        ]);
    }

    public function update(ClientRequest $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        $tagsBefore = $client->tags()->pluck('name')->all();

        DB::transaction(function () use ($request, $client) {
            $client->update($request->clientAttributes());
            $this->syncContacts($client, $request->contactRows());
            $client->syncTagsByName($request->tagNames());
        });

        $this->logTagChange($client, $tagsBefore, $request->tagNames());

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
     * @param  list<string>  $before
     * @param  list<string>  $after
     */
    protected function logTagChange(Client $client, array $before, array $after): void
    {
        $normalise = function (array $tags) {
            $tags = array_map('strtolower', $tags);
            sort($tags);

            return $tags;
        };

        if ($normalise($before) === $normalise($after)) {
            return;
        }

        $description = $after === []
            ? 'All tags removed'
            : 'Tags set to '.implode(', ', $after);

        activity()->performedOn($client)->event('tags')->log($description);
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
            'currencies' => Settings::enabledCurrencies(),
            'categories' => ClientTypes::CATEGORIES,
            'owners' => User::query()->active()->orderBy('name')->get(['id', 'name']),
            'allTags' => Tag::orderBy('name')->pluck('name'),
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
            'category' => $client->category,
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
            'tags' => $client->tags->map(fn (Tag $tag) => [
                'name' => $tag->name,
                'color' => $tag->color,
            ])->all(),
            'projects' => $client->relationLoaded('projects')
                ? $client->projects->map(fn ($project) => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'service_line' => $project->service_line,
                    'status' => $project->status,
                ])->all()
                : [],
            'proformas' => $client->relationLoaded('proformas')
                ? $client->proformas->map(fn ($proforma) => [
                    'id' => $proforma->id,
                    'number' => $proforma->number,
                    'status' => $proforma->status,
                    'currency' => $proforma->currency,
                    'total' => $proforma->total,
                    'issue_date' => $proforma->issue_date?->toDateString(),
                ])->all()
                : [],
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
