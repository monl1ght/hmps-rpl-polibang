<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NavbarSetting extends Model
{
    protected $fillable = [
        'logo_path',
        'logo_alt',
        'brand_title',
        'brand_subtitle',
        'cta_label',
        'cta_contact_name',
        'cta_whatsapp_number',
        'cta_message',
        'cta_is_active',
        'is_active',
    ];

    protected $casts = [
        'cta_is_active' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'logo_path' => '/Images/logo/hmps-rpl-logo.png',
        'logo_alt' => 'Logo HMPS RPL',
        'brand_title' => 'HMPS RPL',
        'brand_subtitle' => 'Rekayasa Perangkat Lunak',
        'cta_label' => 'Hubungi Kami',
        'cta_is_active' => true,
        'is_active' => true,
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
