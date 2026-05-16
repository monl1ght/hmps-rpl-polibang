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
    public const FEATURED_SIZE_LARGE = 'large';
    public const FEATURED_SIZE_MEDIUM = 'medium';
    public const FEATURED_SIZE_SMALL = 'small';

    public const FEATURED_SIZES = [
        self::FEATURED_SIZE_LARGE,
        self::FEATURED_SIZE_MEDIUM,
        self::FEATURED_SIZE_SMALL,
    ];

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
        'featured_size',
        'is_published',
        'sort_order',
    ];

    protected $attributes = [
        'category' => 'kegiatan',
        'is_featured' => false,
        'featured_size' => self::FEATURED_SIZE_MEDIUM,
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
                $documentation->slug = static::generateUniqueSlug(
                    $documentation->title,
                    $documentation->id
                );
            }

            if (blank($documentation->year)) {
                $documentation->year = now()->year;
            }

            if (! in_array($documentation->featured_size, self::FEATURED_SIZES, true)) {
                $documentation->featured_size = self::FEATURED_SIZE_MEDIUM;
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

    protected function featuredSize(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): string => in_array($value, self::FEATURED_SIZES, true)
                ? $value
                : self::FEATURED_SIZE_MEDIUM,
            set: fn (?string $value): string => in_array($value, self::FEATURED_SIZES, true)
                ? $value
                : self::FEATURED_SIZE_MEDIUM,
        );
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
        $baseSlug = Str::slug($title) ?: 'dokumentasi';
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
