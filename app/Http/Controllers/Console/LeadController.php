<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Http\Requests\Console\LeadTriageRequest;
use App\Models\Client;
use App\Models\Lead;
use App\Models\User;
use App\Support\ActivityPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class LeadController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Lead::class);

        $filters = [
            'search' => $request->string('search')->trim()->value(),
            'status' => $request->string('status')->trim()->value(),
        ];

        $leads = Lead::query()
            ->with('owner:id,name')
            ->search($filters['search'])
            ->when(
                in_array($filters['status'], Lead::STATUSES, true),
                fn ($q) => $q->where('status', $filters['status']),
                fn ($q) => $q->where('status', '!=', 'converted'), // hide converted by default
            )
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Lead $lead) => [
                'id' => $lead->id,
                'name' => $lead->name,
                'company' => $lead->company,
                'email' => $lead->email,
                'status' => $lead->status,
                'owner' => $lead->owner?->name,
                'received' => $lead->created_at->diffForHumans(),
            ]);

        return Inertia::render('Console/Leads/Index', [
            'leads' => $leads,
            'filters' => $filters,
            'statuses' => Lead::STATUSES,
        ]);
    }

    public function show(Request $request, Lead $lead): Response
    {
        $this->authorize('view', $lead);

        $lead->load(['owner:id,name', 'convertedClient:id,name']);

        return Inertia::render('Console/Leads/Show', [
            'lead' => [
                'id' => $lead->id,
                'name' => $lead->name,
                'company' => $lead->company,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'message' => $lead->message,
                'source' => $lead->source,
                'status' => $lead->status,
                'owner_id' => $lead->owner_id,
                'notes' => $lead->notes,
                'received_at' => $lead->created_at->toDayDateTimeString(),
                'converted_client' => $lead->convertedClient
                    ? ['id' => $lead->convertedClient->id, 'name' => $lead->convertedClient->name]
                    : null,
            ],
            'owners' => User::query()->active()->orderBy('name')->get(['id', 'name']),
            'canConvert' => $request->user()->can('convert', $lead),
            'activity' => Activity::forSubject($lead)
                ->with('causer:id,name')
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn (Activity $a) => ActivityPresenter::present($a)),
        ]);
    }

    public function update(LeadTriageRequest $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        // Don't let triage move a converted lead back into the pipeline.
        if ($lead->isConverted()) {
            return back()->with('success', 'This lead has already been converted.');
        }

        $lead->update($request->triageAttributes());

        return back()->with('success', 'Lead updated.');
    }

    public function convert(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('convert', $lead);

        $client = DB::transaction(function () use ($request, $lead) {
            $isOrg = filled($lead->company);

            $client = new Client([
                'name' => $isOrg ? $lead->company : $lead->name,
                'type' => $isOrg ? 'organisation' : 'individual',
                'status' => 'active',
                'email' => $isOrg ? null : $lead->email,
                'phone' => $isOrg ? null : $lead->phone,
                'owner_id' => $lead->owner_id,
            ]);
            $client->created_by = $request->user()->id;
            $client->save();

            if ($isOrg) {
                $client->contacts()->create([
                    'name' => $lead->name,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'is_primary' => true,
                ]);
            }

            $lead->forceFill([
                'converted_client_id' => $client->id,
                'status' => 'converted',
            ])->save();

            activity()->performedOn($lead)->event('converted')
                ->log("Converted to client “{$client->name}”");

            return $client;
        });

        return redirect()
            ->route('console.clients.show', $client)
            ->with('success', "Lead converted to {$client->name}.");
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $this->authorize('delete', $lead);

        $lead->delete();

        return redirect()
            ->route('console.leads.index')
            ->with('success', 'Lead deleted.');
    }
}
