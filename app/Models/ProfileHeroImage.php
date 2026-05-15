<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileHeroImage extends Model
{
    protected $fillable = [
        'title',
        'image',
        'alt_text',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn() => $this->resolveImageUrl($this->image));
    }

    private function resolveImageUrl(?string $value): string
    {
        if (blank($value)) {
            return 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&h=600&fit=crop&auto=format';
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
