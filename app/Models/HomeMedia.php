<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomeMedia extends Model
{
    protected $fillable = [
        'group',
        'title',
        'subtitle',
        'category',
        'image',
        'alt_text',
        'layout',
        'is_active',
        'sort_order',
    ];

    protected $attributes = [
        'layout' => 'normal',
        'is_active' => true,
        'sort_order' => 0,
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'image_url',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->resolveImageUrl($this->image));
    }

    private function resolveImageUrl(?string $value): string
    {
        if (blank($value)) {
            return 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=900&h=700&fit=crop&auto=format';
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