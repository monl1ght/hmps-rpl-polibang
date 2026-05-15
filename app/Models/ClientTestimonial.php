<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ClientTestimonial extends Model
{
    public const SOURCE_SERVICE = 'layanan_jasa';
    public const SOURCE_CONSULTATION = 'konsultasi';

    protected $fillable = [
        'name',
        'role',
        'business_name',
        'service_type',
        'source_page',
        'rating',
        'message',
        'emoji',
        'is_approved',
        'sort_order',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'role' => 'Pengguna Layanan',
        'source_page' => self::SOURCE_SERVICE,
        'rating' => 5,
        'emoji' => '💬',
        'is_approved' => false,
        'sort_order' => 0,
    ];

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('is_approved', false);
    }

    public function scopeConsultation(Builder $query): Builder
    {
        return $query->where('source_page', self::SOURCE_CONSULTATION);
    }

    public function scopeService(Builder $query): Builder
    {
        return $query->where('source_page', self::SOURCE_SERVICE);
    }

    public function scopeSource(Builder $query, string $sourcePage): Builder
    {
        return $query->where('source_page', $this->normalizeSourcePage($sourcePage));
    }

    public function scopeRating(Builder $query, int $rating): Builder
    {
        return $query->where('rating', $this->normalizeRating($rating));
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function setNameAttribute(mixed $value): void
    {
        $this->attributes['name'] = $this->cleanString($value);
    }

    public function setRoleAttribute(mixed $value): void
    {
        $role = $this->cleanString($value);

        $this->attributes['role'] = $role !== '' ? $role : 'Pengguna Layanan';
    }

    public function setBusinessNameAttribute(mixed $value): void
    {
        $businessName = $this->cleanString($value);

        $this->attributes['business_name'] = $businessName !== '' ? $businessName : null;
    }

    public function setServiceTypeAttribute(mixed $value): void
    {
        $this->attributes['service_type'] = $this->cleanString($value);
    }

    public function setSourcePageAttribute(mixed $value): void
    {
        $this->attributes['source_page'] = $this->normalizeSourcePage($value);
    }

    public function setRatingAttribute(mixed $value): void
    {
        $this->attributes['rating'] = $this->normalizeRating($value);
    }

    public function setMessageAttribute(mixed $value): void
    {
        $this->attributes['message'] = $this->cleanString($value);
    }

    public function setEmojiAttribute(mixed $value): void
    {
        $emoji = $this->cleanString($value);

        $this->attributes['emoji'] = $emoji !== '' ? $emoji : '💬';
    }

    public function isServiceTestimonial(): bool
    {
        return $this->source_page === self::SOURCE_SERVICE;
    }

    public function isConsultationTestimonial(): bool
    {
        return $this->source_page === self::SOURCE_CONSULTATION;
    }

    public static function sourceOptions(): array
    {
        return [
            self::SOURCE_SERVICE,
            self::SOURCE_CONSULTATION,
        ];
    }

    private function normalizeSourcePage(mixed $sourcePage): string
    {
        $sourcePage = $this->cleanString($sourcePage);

        return in_array($sourcePage, self::sourceOptions(), true)
            ? $sourcePage
            : self::SOURCE_SERVICE;
    }

    private function normalizeRating(mixed $rating): int
    {
        $rating = (int) $rating;

        if ($rating < 1 || $rating > 5) {
            return 5;
        }

        return $rating;
    }

    private function cleanString(mixed $value): string
    {
        return trim((string) $value);
    }
}
