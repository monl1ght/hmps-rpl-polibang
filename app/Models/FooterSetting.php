<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FooterSetting extends Model
{
    protected $fillable = [
        'logo_path',
        'logo_alt',
        'brand_title',
        'brand_subtitle',
        'description',
        'identity_label',
        'identity_text',
        'navigation_title',
        'contact_title',
        'contact_cta_label',
        'contact_cta_url',
        'copyright_text',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
