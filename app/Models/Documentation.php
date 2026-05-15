<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Documentation extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'category',
        'year',
        'event_date',
        'location',
        'cover',
        'description',
        'is_featured',
        'is_published',
        'sort_order',
    ];

    protected $attributes = [
        'category' => 'kegiatan',
        'is_featured' => false,
        'is_published' => true,
        'sort_order' => 0,
    ];

    protected $casts = [
        'year' => 'integer',
        'event_date' => 'date',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'cover_url',
    ];

    protected static function booted(): void
    {
        static::saving(function (Documentation $documentation) {
            if (blank($documentation->slug)) {
                $documentation->slug = static::generateUniqueSlug($documentation->title, $documentation->id);
            }

            if (blank($documentation->year)) {
                $documentation->year = now()->year;
            }
        });
    }

    public function images(): HasMany
    {
        return $this->hasMany(DocumentationImage::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByDesc('is_featured')
            ->orderByDesc('event_date')
            ->orderBy('sort_order')
            ->orderByDesc('id');
    }

    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn () => $this->resolveImageUrl($this->cover));
    }

    private function resolveImageUrl(?string $value): string
    {
        if (blank($value)) {
            return 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=1200&h=800&fit=crop&auto=format';
        }

        if (Str::startsWith($value, ['http://', 'https://', '/'])) {
            return $value;
        }

        if (Str::startsWith($value, ['storage/', 'images/', 'uploads/'])) {
            return asset($value);
        }

        return Storage::url($value);
    }

    private static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}