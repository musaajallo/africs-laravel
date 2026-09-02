<?php

namespace App\Models\Concerns;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasTags
{
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')->orderBy('name');
    }

    /**
     * Replace this model's tags with the given names, creating tags as needed.
     *
     * @param  iterable<string>  $names
     */
    public function syncTagsByName(iterable $names): void
    {
        $this->tags()->sync(Tag::idsForNames($names));
    }

    public function scopeTagged($query, ?string $slug)
    {
        return $query->when(
            filled($slug),
            fn ($q) => $q->whereHas('tags', fn ($t) => $t->where('slug', $slug)),
        );
    }
}
