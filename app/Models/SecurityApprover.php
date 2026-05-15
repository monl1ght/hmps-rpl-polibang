<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SecurityApprover extends Model
{
    protected $fillable = [
        'name',
        'position',
        'whatsapp_number',
        'normalized_whatsapp_number',
        'role',
        'is_primary',
        'is_active',
        'activated_at',
        'deactivated_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime',
    ];

    public function scopePrimaryActive(Builder $query): Builder
    {
        return $query
            ->where('is_primary', true)
            ->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_active')->orderByDesc('is_primary')->latest('id');
    }
}
