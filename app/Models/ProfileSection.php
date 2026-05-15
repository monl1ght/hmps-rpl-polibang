<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProfileSection extends Model
{
    protected $fillable = [
        'key',
        'badge',
        'title',
        'title_highlight',
        'description',
        'primary_button_label',
        'primary_button_url',
        'meta',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'meta' => '{}',
        'is_active' => true,
        'sort_order' => 0,
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
