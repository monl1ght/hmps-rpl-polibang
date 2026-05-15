<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileCabinetLogo extends Model
{
    public const DEFAULT_TITLE = 'Logo Kabinet';
    public const DEFAULT_CABINET_NAME = 'Raksa Devarya';
    public const DEFAULT_PERIOD = 'Periode 2025/2026';
    public const DEFAULT_ALT_TEXT = 'Logo Kabinet HMPS RPL';
    public const DEFAULT_FALLBACK_IMAGE = '/Images/logo/hmps-rpl-logo.png';

    protected $fillable = [
        'title',
        'cabinet_name',
        'period',
        'image',
        'alt_text',
        'description',
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
        'title' => self::DEFAULT_TITLE,
        'cabinet_name' => self::DEFAULT_CABINET_NAME,
        'period' => self::DEFAULT_PERIOD,
        'meta' => '{}',
        'is_active' => true,
        'sort_order' => 0,
    ];

    protected $appends = [
        'image_url',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function scopePrimary(Builder $query): Builder
    {
        return $query
            ->active()
            ->ordered();
    }

    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): string => $this->filledOrDefault(
                $value,
                self::DEFAULT_TITLE
            ),
            set: fn (mixed $value): string => $this->filledOrDefault(
                $value,
                self::DEFAULT_TITLE
            ),
        );
    }

    protected function cabinetName(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): string => $this->filledOrDefault(
                $value,
                self::DEFAULT_CABINET_NAME
            ),
            set: fn (mixed $value): string => $this->filledOrDefault(
                $value,
                self::DEFAULT_CABINET_NAME
            ),
        );
    }

    protected function period(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): string => $this->filledOrDefault(
                $value,
                self::DEFAULT_PERIOD
            ),
            set: fn (mixed $value): string => $this->filledOrDefault(
                $value,
                self::DEFAULT_PERIOD
            ),
        );
    }

    protected function altText(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): string => $this->filledOrDefault(
                $value,
                $this->cabinet_name
                    ? "Logo Kabinet {$this->cabinet_name}"
                    : self::DEFAULT_ALT_TEXT
            ),
            set: fn (mixed $value): ?string => $this->nullableString($value),
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value): ?string => $this->nullableString($value),
        );
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value): ?string => $this->nullableString($value),
        );
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): string => $this->resolveImageUrl($this->image));
    }

    public function getMeaningAttribute(): string
    {
        return $this->description ?: (string) data_get($this->meta, 'meaning', '');
    }

    public function getShortMeaningAttribute(): string
    {
        return (string) data_get($this->meta, 'short_meaning', '');
    }

    private function resolveImageUrl(?string $value): string
    {
        if (blank($value)) {
            return asset(self::DEFAULT_FALLBACK_IMAGE);
        }

        $value = trim($value);

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        if (Str::startsWith($value, ['/'])) {
            return $value;
        }

        if (Str::startsWith($value, ['storage/', 'images/', 'Images/', 'uploads/'])) {
            return asset($value);
        }

        return Storage::url($value);
    }

    private function filledOrDefault(mixed $value, string $default): string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : $default;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
