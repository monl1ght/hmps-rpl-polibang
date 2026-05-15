<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ClientTestimonial extends Model
{
    protected $fillable = [
        'name',
        'role',
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
        'source_page' => 'layanan_jasa',
        'rating' => 5,
        'emoji' => '💬',
        'is_approved' => false,
        'sort_order' => 0,
    ];

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    public function scopeConsultation(Builder $query): Builder
    {
        return $query->where('source_page', 'konsultasi');
    }

    public function scopeService(Builder $query): Builder
    {
        return $query->where('source_page', 'layanan_jasa');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function scopeRating(Builder $query, int $rating): Builder
    {
        return $query->where('rating', $rating);
    }
}