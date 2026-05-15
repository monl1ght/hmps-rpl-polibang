<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramWorkCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'title',
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

    public function programs(): HasMany
    {
        return $this->hasMany(ProgramWork::class, 'program_work_category_id');
    }
}