<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ManagementHeroSetting extends Model
{
    protected $fillable = [
        'badge',
        'title',
        'title_highlight',
        'description',
        'primary_button_label',
        'primary_button_url',
        'secondary_button_label',
        'secondary_button_url',
        'tertiary_button_label',
        'tertiary_button_url',
        'image_one',
        'image_one_alt',
        'image_two',
        'image_two_alt',
        'image_three',
        'image_three_alt',
        'image_four',
        'image_four_alt',
        'floating_badge_top_icon',
        'floating_badge_top_title',
        'floating_badge_top_subtitle',
        'floating_badge_bottom_icon',
        'floating_badge_bottom_title',
        'floating_badge_bottom_subtitle',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    protected $appends = [
        'image_one_url',
        'image_two_url',
        'image_three_url',
        'image_four_url',
    ];

    protected function imageOneUrl(): Attribute
    {
        return Attribute::get(fn() => $this->resolveImageUrl($this->image_one));
    }

    protected function imageTwoUrl(): Attribute
    {
        return Attribute::get(fn() => $this->resolveImageUrl($this->image_two));
    }

    protected function imageThreeUrl(): Attribute
    {
        return Attribute::get(fn() => $this->resolveImageUrl($this->image_three));
    }

    protected function imageFourUrl(): Attribute
    {
        return Attribute::get(fn() => $this->resolveImageUrl($this->image_four));
    }

    private function resolveImageUrl(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://', 'data:image', '/'])) {
            return $value;
        }

        if (Str::startsWith($value, ['storage/', 'images/', 'uploads/'])) {
            return asset($value);
        }

        return Storage::url($value);
    }
}
