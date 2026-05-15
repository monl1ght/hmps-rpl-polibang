<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCatalog extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'badge',
        'icon',
        'summary',
        'features',
        'cta',
        'whatsapp_text',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'features' => '[]',
        'is_active' => true,
        'sort_order' => 0,
    ];

    public function packages(): HasMany
    {
        return $this->hasMany(ServicePackage::class, 'service_catalog_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
