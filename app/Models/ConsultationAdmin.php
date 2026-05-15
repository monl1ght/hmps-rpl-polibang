<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ConsultationAdmin extends Model
{
    protected $fillable = [
        'label',
        'name',
        'day',
        'start_time',
        'end_time',
        'phone_display',
        'phone_wa',
        'role',
        'emoji',
        'badge',
        'skills',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'skills' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'day' => 'Senin',
        'is_active' => true,
        'sort_order' => 0,
        'start_time' => '08:00:00',
        'end_time' => '20:00:00',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getSkillsTextAttribute(): string
    {
        return implode("\n", $this->skills ?? []);
    }
}
