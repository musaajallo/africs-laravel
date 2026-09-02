<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Http\Requests\Console\ProjectRequest;
use App\Models\Client;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use App\Support\ActivityPresenter;
use App\Support\ProjectMeta;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Project::class);

        $filters = [
            'search' => $request->string('search')->trim()->value(),
            'status' => $request->string('status')->trim()->value(),
            'service_line' => $request->string('service_line')->trim()->value(),
            'client' => $request->integer('client') ?: null,
        ];

        $projects = Project::query()
            ->with(['client:id,name', 'owner:id,name'])
            ->search($filters['search'])
            ->when(
                in_array($filters['status'], ProjectMeta::statusKeys(), true),
                fn ($q) => $q->where('status', $filters['status']),
                fn ($q) => $q->whereIn('status', ProjectMeta::OPEN_STATUSES),
            )
            ->when(
                in_array($filters['service_line'], ProjectMeta::serviceLineKeys(), true),
                fn ($q) => $q->where('service_line', $filters['service_line']),
            )
            ->when($filters['client'], fn ($q) => $q->where('client_id', $filters['client']))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Project $project) => [
                'id' => $project->id,
                'name' => $project->name,
                'client' => $project->client?->name,
                'client_id' => $project->client_id,
                'service_line' => $project->service_line,
                'status' => $project->status,
                'owner' => $project->owner?->name,
                'ends_on' => $project->ends_on?->toDateString(),
            ]);

        return Inertia::render('Console/Projects/Index', [
            'projects' => $projects,
            'filters' => $filters,
            'serviceLines' => ProjectMeta::SERVICE_LINES,
            'statuses' => ProjectMeta::STATUSES,
            'clients' => Client::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Project::class);

        return Inertia::render('Console/Projects/Create', [
            ...$this->formOptions(),
            'presetClientId' => $request->integer('client') ?: null,
        ]);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $this->authorize('create', Project::class);

        $project = DB::transaction(function () use ($request) {
            $project = new Project($request->projectAttributes());
            $project->created_by = $request->user()->id;
            $project->save();

            $project->members()->sync($request->memberRoles());
            $project->syncTagsByName($request->tagNames());

            return $project;
        });

        return redirect()
            ->route('console.projects.show', $project)
            ->with('success', 'Project created.');
    }

    public function show(Project $project): Response
    {
        $this->authorize('view', $project);

        $project->load(['client:id,name', 'owner:id,name', 'createdBy:id,name', 'members:id,name', 'tags:id,name,color']);

        return Inertia::render('Console/Projects/Show', [
            'project' => $this->present($project),
            'activity' => Activity::forSubject($project)
                ->with('causer:id,name')
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn (Activity $a) => ActivityPresenter::present($a)),
        ]);
    }

    public function edit(Project $project): Response
    {
        $this->authorize('update', $project);

        $project->load(['members:id,name', 'tags:id,name']);

        return Inertia::render('Console/Projects/Edit', [
            'project' => $this->present($project),
            ...$this->formOptions(),
        ]);
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        DB::transaction(function () use ($request, $project) {
            $project->update($request->projectAttributes());
            $project->members()->sync($request->memberRoles());
            $project->syncTagsByName($request->tagNames());
        });

        return redirect()
            ->route('console.projects.show', $project)
            ->with('success', 'Project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()
            ->route('console.projects.index')
            ->with('success', 'Project archived.');
    }

    public function restore(int $project): RedirectResponse
    {
        $project = Project::onlyTrashed()->findOrFail($project);
        $this->authorize('restore', $project);

        $project->restore();

        return redirect()
            ->route('console.projects.show', $project)
            ->with('success', 'Project restored.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        $users = User::query()->active()->orderBy('name')->get(['id', 'name']);

        return [
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'users' => $users,
            'serviceLines' => ProjectMeta::SERVICE_LINES,
            'statuses' => ProjectMeta::STATUSES,
            'currencies' => Settings::enabledCurrencies(),
            'baseCurrency' => Settings::baseCurrency(),
            'allTags' => Tag::orderBy('name')->pluck('name'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function present(Project $project): array
    {
        return [
            'id' => $project->id,
            'name' => $project->name,
            'client_id' => $project->client_id,
            'client' => $project->client?->name,
            'service_line' => $project->service_line,
            'service_line_label' => ProjectMeta::SERVICE_LINES[$project->service_line] ?? $project->service_line,
            'status' => $project->status,
            'status_label' => ProjectMeta::STATUSES[$project->status] ?? $project->status,
            'description' => $project->description,
            'starts_on' => $project->starts_on?->toDateString(),
            'ends_on' => $project->ends_on?->toDateString(),
            'budget_amount' => $project->budget_amount,
            'budget_currency' => $project->budget_currency,
            'owner_id' => $project->owner_id,
            'owner' => $project->owner?->name,
            'created_by' => $project->createdBy?->name,
            'created_at' => $project->created_at?->toDateString(),
            'archived' => $project->trashed(),
            'members' => $project->members->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'role' => $u->pivot->role,
            ])->all(),
            'tags' => $project->tags->map(fn (Tag $t) => ['name' => $t->name, 'color' => $t->color])->all(),
        ];
    }
}
