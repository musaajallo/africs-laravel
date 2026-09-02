<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Console\ProjectRequest;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Models\Project;
use App\Support\ProjectMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Project::class);

        $projects = Project::query()
            ->with(['tags:id,name'])
            ->search($request->string('search')->trim()->value())
            ->when(
                in_array($request->query('status'), ProjectMeta::statusKeys(), true),
                fn ($q) => $q->where('status', $request->query('status')),
            )
            ->when(
                in_array($request->query('service_line'), ProjectMeta::serviceLineKeys(), true),
                fn ($q) => $q->where('service_line', $request->query('service_line')),
            )
            ->when($request->integer('client'), fn ($q, $id) => $q->where('client_id', $id))
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 25), 100));

        return ProjectResource::collection($projects);
    }

    public function show(Project $project): ProjectResource
    {
        $this->authorize('view', $project);

        return new ProjectResource($project->load(['tags:id,name', 'members:id,name']));
    }

    public function store(ProjectRequest $request): JsonResponse
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

        return (new ProjectResource($project->load(['tags:id,name', 'members:id,name'])))
            ->response()
            ->setStatusCode(201);
    }

    public function update(ProjectRequest $request, Project $project): ProjectResource
    {
        $this->authorize('update', $project);

        DB::transaction(function () use ($request, $project) {
            $project->update($request->projectAttributes());
            $project->members()->sync($request->memberRoles());
            $project->syncTagsByName($request->tagNames());
        });

        return new ProjectResource($project->load(['tags:id,name', 'members:id,name']));
    }

    public function destroy(Project $project): Response
    {
        $this->authorize('delete', $project);

        $project->delete();

        return response()->noContent();
    }
}
