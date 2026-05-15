<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProgramWork extends Model
{
    protected $fillable = [
        'program_work_category_id',
        'title',
        'slug',
        'schedule_text',
        'division_name',
        'person_in_charge',
        'target',
        'budget',
        'image',
        'excerpt',
        'description',
        'goals',
        'benefits',
        'funding_sources',
        'is_featured',
        'is_published',
        'sort_order',
    ];

    protected $attributes = [
        'budget' => 0,
        'is_featured' => false,
        'is_published' => true,
        'sort_order' => 0,
    ];

    protected $casts = [
        'budget' => 'integer',
        'goals' => 'array',
        'benefits' => 'array',
        'funding_sources' => 'array',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProgramWorkCategory::class, 'program_work_category_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('title');
    }

    protected function budgetText(): Attribute
    {
        return Attribute::get(
            fn () => 'Rp ' . number_format((int) $this->budget, 0, ',', '.')
        );
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->resolveImageUrl($this->image));
    }

    private function resolveImageUrl(?string $value): string
    {
        if (blank($value)) {
            return 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?w=1200&h=800&fit=crop&auto=format';
        }

        if (Str::startsWith($value, ['http://', 'https://', '/'])) {
            return $value;
        }

        if (Str::startsWith($value, ['storage/', 'images/', 'uploads/'])) {
            return asset($value);
        }

        return Storage::url($value);
    }
}