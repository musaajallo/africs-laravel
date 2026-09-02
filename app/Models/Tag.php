<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'color'])]
class Tag extends Model
{
    use HasFactory;

    public function taggables(): HasMany
    {
        return $this->hasMany(Taggable::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Tag $tag) {
            $tag->slug = Str::slug($tag->name);
        });
    }

    /**
     * Resolve tag names to Tag ids, creating any that don't exist yet.
     *
     * @param  iterable<string>  $names
     * @return list<int>
     */
    public static function idsForNames(iterable $names): array
    {
        return collect($names)
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique(fn ($name) => Str::lower($name))
            ->map(fn ($name) => static::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            )->id)
            ->values()
            ->all();
    }
}
