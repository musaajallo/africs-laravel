<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Console\AssetRequest;
use App\Http\Resources\Api\V1\AssetResource;
use App\Models\Asset;
use App\Support\AssetMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AssetController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Asset::class);

        $assets = Asset::query()
            ->search($request->string('search')->trim()->value())
            ->when(
                in_array($request->query('status'), AssetMeta::statusKeys(), true),
                fn ($q) => $q->where('status', $request->query('status')),
            )
            ->when(
                in_array($request->query('category'), AssetMeta::categoryKeys(), true),
                fn ($q) => $q->where('category', $request->query('category')),
            )
            ->when($request->integer('assignee'), fn ($q, $id) => $q->where('assigned_to', $id))
            ->orderBy('name')
            ->paginate(min((int) $request->integer('per_page', 25), 100));

        return AssetResource::collection($assets);
    }

    public function show(Asset $asset): AssetResource
    {
        $this->authorize('view', $asset);

        return new AssetResource($asset);
    }

    public function store(AssetRequest $request): JsonResponse
    {
        $this->authorize('create', Asset::class);

        $asset = new Asset($request->assetAttributes());
        $asset->created_by = $request->user()->id;
        $asset->save();

        return (new AssetResource($asset))->response()->setStatusCode(201);
    }

    public function update(AssetRequest $request, Asset $asset): AssetResource
    {
        $this->authorize('update', $asset);

        $asset->update($request->assetAttributes());

        return new AssetResource($asset);
    }

    public function destroy(Asset $asset): Response
    {
        $this->authorize('delete', $asset);

        $asset->delete();

        return response()->noContent();
    }
}
