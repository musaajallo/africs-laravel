<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Http\Requests\Console\AssetRequest;
use App\Models\Asset;
use App\Models\User;
use App\Support\ActivityPresenter;
use App\Support\AssetMeta;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class AssetController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Asset::class);

        $filters = [
            'search' => $request->string('search')->trim()->value(),
            'status' => $request->string('status')->trim()->value(),
            'category' => $request->string('category')->trim()->value(),
            'assignee' => $request->integer('assignee') ?: null,
        ];

        $assets = Asset::query()
            ->with('assignee:id,name')
            ->search($filters['search'])
            ->when(
                in_array($filters['status'], AssetMeta::statusKeys(), true),
                fn ($q) => $q->where('status', $filters['status']),
                fn ($q) => $q->whereIn('status', AssetMeta::ACTIVE_STATUSES),
            )
            ->when(
                in_array($filters['category'], AssetMeta::categoryKeys(), true),
                fn ($q) => $q->where('category', $filters['category']),
            )
            ->when($filters['assignee'], fn ($q) => $q->where('assigned_to', $filters['assignee']))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Asset $asset) => [
                'id' => $asset->id,
                'name' => $asset->name,
                'category' => $asset->category,
                'make_model' => trim("{$asset->make} {$asset->model}") ?: null,
                'serial_number' => $asset->serial_number,
                'asset_tag' => $asset->asset_tag,
                'status' => $asset->status,
                'assignee' => $asset->assignee?->name,
                'location' => $asset->location,
            ]);

        return Inertia::render('Console/Assets/Index', [
            'assets' => $assets,
            'filters' => $filters,
            'categories' => AssetMeta::CATEGORIES,
            'statuses' => AssetMeta::STATUSES,
            'people' => User::query()->active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Asset::class);

        return Inertia::render('Console/Assets/Create', $this->formOptions());
    }

    public function store(AssetRequest $request): RedirectResponse
    {
        $this->authorize('create', Asset::class);

        $asset = new Asset($request->assetAttributes());
        $asset->created_by = $request->user()->id;
        $asset->save();

        return redirect()
            ->route('console.assets.show', $asset)
            ->with('success', 'Asset added.');
    }

    public function show(Asset $asset): Response
    {
        $this->authorize('view', $asset);

        $asset->load([
            'assignee:id,name', 'createdBy:id,name',
            'assignments.user:id,name',
        ]);

        return Inertia::render('Console/Assets/Show', [
            'asset' => $this->present($asset),
            'people' => User::query()->active()->orderBy('name')->get(['id', 'name']),
            'statuses' => AssetMeta::STATUSES,
            'activity' => Activity::forSubject($asset)
                ->with('causer:id,name')
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn (Activity $a) => ActivityPresenter::present($a)),
        ]);
    }

    public function edit(Asset $asset): Response
    {
        $this->authorize('update', $asset);

        return Inertia::render('Console/Assets/Edit', [
            'asset' => $this->present($asset),
            ...$this->formOptions(),
        ]);
    }

    public function update(AssetRequest $request, Asset $asset): RedirectResponse
    {
        $this->authorize('update', $asset);

        $asset->update($request->assetAttributes());

        return redirect()
            ->route('console.assets.show', $asset)
            ->with('success', 'Asset updated.');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $this->authorize('delete', $asset);

        $asset->delete();

        return redirect()
            ->route('console.assets.index')
            ->with('success', 'Asset removed.');
    }

    public function restore(int $asset): RedirectResponse
    {
        $asset = Asset::onlyTrashed()->findOrFail($asset);
        $this->authorize('restore', $asset);

        $asset->restore();

        return redirect()
            ->route('console.assets.show', $asset)
            ->with('success', 'Asset restored.');
    }

    public function assign(Request $request, Asset $asset): RedirectResponse
    {
        $this->authorize('manage', $asset);

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'assigned_on' => ['nullable', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $asset->assignTo(
            User::findOrFail($data['user_id']),
            ! empty($data['assigned_on']) ? now()->parse($data['assigned_on']) : null,
            $data['notes'] ?? null,
        );

        return back()->with('success', 'Asset assigned.');
    }

    public function unassign(Asset $asset): RedirectResponse
    {
        $this->authorize('manage', $asset);

        $asset->unassign();

        return back()->with('success', 'Asset returned to stock.');
    }

    public function status(Request $request, Asset $asset): RedirectResponse
    {
        $this->authorize('manage', $asset);

        $data = $request->validate([
            'status' => ['required', Rule::in(AssetMeta::statusKeys())],
        ]);

        // Retiring or losing a held asset closes its assignment.
        if (in_array($data['status'], ['retired', 'lost'], true) && $asset->assigned_to) {
            $asset->openAssignment()->update(['returned_on' => now()->toDateString()]);
            $asset->assigned_to = null;
            $asset->assigned_on = null;
        }

        $asset->update(['status' => $data['status']]);

        return back()->with('success', "Asset marked {$data['status']}.");
    }

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [
            'categories' => AssetMeta::CATEGORIES,
            'statuses' => AssetMeta::STATUSES,
            'conditions' => AssetMeta::CONDITIONS,
            'currencies' => Settings::enabledCurrencies(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function present(Asset $asset): array
    {
        return [
            'id' => $asset->id,
            'name' => $asset->name,
            'category' => $asset->category,
            'make' => $asset->make,
            'model' => $asset->model,
            'serial_number' => $asset->serial_number,
            'asset_tag' => $asset->asset_tag,
            'status' => $asset->status,
            'condition' => $asset->condition,
            'purchased_on' => $asset->purchased_on?->toDateString(),
            'purchase_cost' => $asset->purchase_cost,
            'purchase_currency' => $asset->purchase_currency,
            'supplier' => $asset->supplier,
            'warranty_until' => $asset->warranty_until?->toDateString(),
            'location' => $asset->location,
            'notes' => $asset->notes,
            'assigned_to' => $asset->assigned_to,
            'assignee' => $asset->assignee?->name,
            'assigned_on' => $asset->assigned_on?->toDateString(),
            'created_by' => $asset->createdBy?->name,
            'created_at' => $asset->created_at?->toDateString(),
            'archived' => $asset->trashed(),
            'assignments' => $asset->relationLoaded('assignments')
                ? $asset->assignments->map(fn ($a) => [
                    'user' => $a->user?->name,
                    'assigned_on' => $a->assigned_on?->toDateString(),
                    'returned_on' => $a->returned_on?->toDateString(),
                    'notes' => $a->notes,
                ])->all()
                : [],
        ];
    }
}
