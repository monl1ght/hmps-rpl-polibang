<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ManagementMember extends Model
{
    public const CATEGORY_CORE = 'core';
    public const CATEGORY_COORDINATOR = 'coordinator';
    public const CATEGORY_MEMBER = 'member';

    protected $fillable = [
        'management_division_id',
        'name',
        'position',
        'task_description',
        'category',
        'photo',
        'sort_order',
    ];

    protected $attributes = [
        'category' => self::CATEGORY_MEMBER,
        'sort_order' => 0,
    ];

    protected $casts = [
        'management_division_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(ManagementDivision::class, 'management_division_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeCore(Builder $query): Builder
    {
        return $query->where('category', self::CATEGORY_CORE);
    }

    public function scopeCoordinators(Builder $query): Builder
    {
        return $query->where('category', self::CATEGORY_COORDINATOR);
    }

    public function scopeMembersOnly(Builder $query): Builder
    {
        return $query->where('category', self::CATEGORY_MEMBER);
    }

    protected function photoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->resolvePhotoUrl($this->photo));
    }

    private function resolvePhotoUrl(?string $value): string
    {
        if (blank($value)) {
            return $this->defaultPhotoUrl();
        }

        if (Str::startsWith($value, ['http://', 'https://', 'data:image', '/'])) {
            return $value;
        }

        if (Str::startsWith($value, ['storage/', 'images/', 'uploads/'])) {
            return asset($value);
        }

        return Storage::url($value);
    }

    private function defaultPhotoUrl(): string
    {
        $initials = collect(preg_split('/\s+/', trim($this->name ?: 'HMPS'), -1, PREG_SPLIT_NO_EMPTY))
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600" fill="none">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#ef4444"/>
      <stop offset="100%" stop-color="#991b1b"/>
    </linearGradient>
  </defs>
  <rect width="600" height="600" fill="url(#bg)"/>
  <circle cx="300" cy="210" r="95" fill="rgba(255,255,255,0.16)"/>
  <text x="50%" y="58%" dominant-baseline="middle" text-anchor="middle" font-size="120" font-family="Arial, sans-serif" font-weight="700" fill="white">{$initials}</text>
</svg>
SVG;

        return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
    }
}