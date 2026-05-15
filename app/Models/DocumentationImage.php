<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentationImage extends Model
{
    protected $fillable = [
        'documentation_id',
        'image',
        'caption',
        'sort_order',
    ];

    protected $attributes = [
        'sort_order' => 0,
    ];

    protected $casts = [
        'documentation_id' => 'integer',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'image_url',
    ];

    public function documentation(): BelongsTo
    {
        return $this->belongsTo(Documentation::class);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->resolveImageUrl($this->image));
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
}