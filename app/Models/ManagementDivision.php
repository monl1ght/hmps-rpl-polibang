<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManagementDivision extends Model
{
    protected $fillable = [
        'name',
        'short_name',
        'description',
        'sort_order',
    ];

    protected $attributes = [
        'sort_order' => 0,
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ManagementMember::class, 'management_division_id')
            ->ordered();
    }
}