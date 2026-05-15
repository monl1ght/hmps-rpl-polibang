<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityApproverChangeRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'requested_by_id',
        'current_approver_id',
        'token',
        'old_name',
        'old_position',
        'old_whatsapp_number',
        'old_normalized_whatsapp_number',
        'new_name',
        'new_position',
        'new_whatsapp_number',
        'new_normalized_whatsapp_number',
        'status',
        'request_reason',
        'approval_note',
        'reject_reason',
        'processed_by_name',
        'requested_at',
        'approved_at',
        'rejected_at',
        'completed_at',
        'expires_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    public function currentApprover(): BelongsTo
    {
        return $this->belongsTo(SecurityApprover::class, 'current_approver_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && now()->greaterThan($this->expires_at);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu Persetujuan',
            self::STATUS_APPROVED => 'Disetujui dan Aktif',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_EXPIRED => 'Kedaluwarsa',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => 'Tidak Diketahui',
        };
    }
}
