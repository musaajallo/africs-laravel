<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Http\Requests\Console\TagRequest;
use App\Models\Tag;
use App\Support\TagColors;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Tag::class);

        $tags = Tag::query()
            ->withCount('taggables as usage_count')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'color'])
            ->map(fn (Tag $tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'color' => $tag->color,
                'usage_count' => $tag->usage_count,
            ]);

        return Inertia::render('Console/Tags/Index', [
            'tags' => $tags,
            'colors' => TagColors::KEYS,
        ]);
    }

    public function store(TagRequest $request): RedirectResponse
    {
        $this->authorize('create', Tag::class);

        Tag::create($request->tagAttributes());

        return redirect()->route('console.tags.index')->with('success', 'Tag created.');
    }

    public function update(TagRequest $request, Tag $tag): RedirectResponse
    {
        $this->authorize('update', $tag);

        $tag->update($request->tagAttributes());

        return redirect()->route('console.tags.index')->with('success', 'Tag updated.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $this->authorize('delete', $tag);

        $tag->delete();

        return redirect()->route('console.tags.index')->with('success', 'Tag deleted.');
    }
}
