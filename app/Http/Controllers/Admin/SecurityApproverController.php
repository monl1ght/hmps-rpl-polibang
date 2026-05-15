<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityApprover;
use App\Models\SecurityApproverChangeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SecurityApproverController extends Controller
{
    private const DEFAULT_APPROVER_NAME = 'Ketua HMPS';
    private const DEFAULT_APPROVER_POSITION = 'Ketua HMPS';
    private const DEFAULT_APPROVER_PHONE = '087879175646';

    public function index(Request $request): Response
    {
        $activeApprover = $this->activeApprover();

        $latestRequest = SecurityApproverChangeRequest::query()
            ->with('requestedBy:id,name,username,email')
            ->latest('id')
            ->first();

        if ($latestRequest?->isPending() && $latestRequest->isExpired()) {
            $latestRequest->update([
                'status' => SecurityApproverChangeRequest::STATUS_EXPIRED,
            ]);

            $latestRequest->refresh();
        }

        return Inertia::render('admin/pages/security/ApproverSettings', [
            'activeApprover' => $this->mapApprover($activeApprover),
            'latestRequest' => $latestRequest ? $this->mapRequest($latestRequest) : null,
        ]);
    }

    public function storeChangeRequest(Request $request): RedirectResponse
    {
        $activeApprover = $this->activeApprover();

        $pendingRequest = SecurityApproverChangeRequest::query()
            ->where('status', SecurityApproverChangeRequest::STATUS_PENDING)
            ->latest('id')
            ->first();

        if ($pendingRequest?->isExpired()) {
            $pendingRequest->update([
                'status' => SecurityApproverChangeRequest::STATUS_EXPIRED,
            ]);

            $pendingRequest = null;
        }

        if ($pendingRequest) {
            return back()->with(
                'error',
                'Masih ada permintaan perubahan nomor Ketua HMPS yang menunggu persetujuan.'
            );
        }

        $validated = $request->validate([
            'new_name' => ['required', 'string', 'max:160'],
            'new_position' => ['required', 'string', 'max:160'],
            'new_whatsapp_number' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'request_reason' => ['nullable', 'string', 'max:1500'],
        ], [
            'new_name.required' => 'Nama Ketua HMPS baru wajib diisi.',
            'new_position.required' => 'Jabatan wajib diisi.',
            'new_whatsapp_number.required' => 'Nomor WhatsApp baru wajib diisi.',
            'new_whatsapp_number.regex' => 'Format nomor WhatsApp tidak valid.',
        ]);

        $newNormalizedNumber = $this->normalizeWhatsappNumber($validated['new_whatsapp_number']);

        if (! $newNormalizedNumber) {
            throw ValidationException::withMessages([
                'new_whatsapp_number' => 'Nomor WhatsApp baru tidak valid.',
            ]);
        }

        if ($newNormalizedNumber === $activeApprover->normalized_whatsapp_number) {
            throw ValidationException::withMessages([
                'new_whatsapp_number' => 'Nomor baru tidak boleh sama dengan nomor Ketua HMPS yang sedang aktif.',
            ]);
        }

        SecurityApproverChangeRequest::create([
            'requested_by_id' => $request->user()?->id,
            'current_approver_id' => $activeApprover->id,
            'token' => Str::random(64),

            'old_name' => $activeApprover->name,
            'old_position' => $activeApprover->position,
            'old_whatsapp_number' => $activeApprover->whatsapp_number,
            'old_normalized_whatsapp_number' => $activeApprover->normalized_whatsapp_number,

            'new_name' => $validated['new_name'],
            'new_position' => $validated['new_position'],
            'new_whatsapp_number' => $validated['new_whatsapp_number'],
            'new_normalized_whatsapp_number' => $newNormalizedNumber,

            'status' => SecurityApproverChangeRequest::STATUS_PENDING,
            'request_reason' => $validated['request_reason'] ?? null,

            'requested_at' => now(),
            'expires_at' => now()->addDay(),

            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        return back()->with(
            'success',
            'Permintaan perubahan nomor Ketua HMPS berhasil dibuat. Kirim link approval ke nomor Ketua HMPS yang sedang aktif.'
        );
    }

    private function activeApprover(): SecurityApprover
    {
        $approver = SecurityApprover::query()
            ->primaryActive()
            ->latest('id')
            ->first();

        if ($approver) {
            return $approver;
        }

        return SecurityApprover::create([
            'name' => self::DEFAULT_APPROVER_NAME,
            'position' => self::DEFAULT_APPROVER_POSITION,
            'whatsapp_number' => self::DEFAULT_APPROVER_PHONE,
            'normalized_whatsapp_number' => $this->normalizeWhatsappNumber(self::DEFAULT_APPROVER_PHONE),
            'role' => 'primary_approver',
            'is_primary' => true,
            'is_active' => true,
            'activated_at' => now(),
        ]);
    }

    private function mapApprover(SecurityApprover $approver): array
    {
        return [
            'id' => $approver->id,
            'name' => $approver->name,
            'position' => $approver->position,
            'whatsapp_number' => $approver->whatsapp_number,
            'normalized_whatsapp_number' => $approver->normalized_whatsapp_number,
            'is_primary' => (bool) $approver->is_primary,
            'is_active' => (bool) $approver->is_active,
            'activated_at' => $approver->activated_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function mapRequest(SecurityApproverChangeRequest $request): array
    {
        return [
            'id' => $request->id,
            'token' => $request->token,
            'status' => $request->status,
            'status_label' => $request->statusLabel(),

            'old_name' => $request->old_name,
            'old_position' => $request->old_position,
            'old_whatsapp_number' => $request->old_whatsapp_number,

            'new_name' => $request->new_name,
            'new_position' => $request->new_position,
            'new_whatsapp_number' => $request->new_whatsapp_number,

            'request_reason' => $request->request_reason,
            'approval_note' => $request->approval_note,
            'reject_reason' => $request->reject_reason,
            'processed_by_name' => $request->processed_by_name,

            'requested_by' => [
                'name' => $request->requestedBy?->name,
                'username' => $request->requestedBy?->username,
                'email' => $request->requestedBy?->email,
            ],

            'requested_at' => $request->requested_at?->format('Y-m-d H:i:s'),
            'approved_at' => $request->approved_at?->format('Y-m-d H:i:s'),
            'rejected_at' => $request->rejected_at?->format('Y-m-d H:i:s'),
            'completed_at' => $request->completed_at?->format('Y-m-d H:i:s'),
            'expires_at' => $request->expires_at?->format('Y-m-d H:i:s'),

            'whatsapp_url' => $request->isPending()
                ? $this->buildWhatsappUrl($request)
                : null,
        ];
    }

    private function buildWhatsappUrl(SecurityApproverChangeRequest $request): string
    {
        $approvalUrl = URL::temporarySignedRoute(
            'admin.security.approver-approval.show',
            now()->addDay(),
            ['token' => $request->token]
        );

        $message = "Assalamu'alaikum Ketua HMPS RPL.\n\n"
            . "Ada permintaan perubahan nomor Ketua HMPS/approver keamanan website.\n\n"
            . "Data Lama:\n"
            . "Nama: {$request->old_name}\n"
            . "Jabatan: {$request->old_position}\n"
            . "Nomor: {$request->old_whatsapp_number}\n\n"
            . "Data Baru:\n"
            . "Nama: {$request->new_name}\n"
            . "Jabatan: {$request->new_position}\n"
            . "Nomor: {$request->new_whatsapp_number}\n\n"
            . "Alasan: " . ($request->request_reason ?: '-') . "\n\n"
            . "Silakan setujui atau tolak melalui link berikut:\n"
            . $approvalUrl . "\n\n"
            . "Catatan: Perubahan ini akan mengubah nomor approver utama sistem.";

        return 'https://wa.me/'
            . $request->old_normalized_whatsapp_number
            . '?text='
            . rawurlencode($message);
    }

    private function normalizeWhatsappNumber(?string $number): string
    {
        $clean = preg_replace('/[^0-9]/', '', (string) $number) ?: '';

        if (str_starts_with($clean, '0')) {
            return '62' . substr($clean, 1);
        }

        if (str_starts_with($clean, '8')) {
            return '62' . $clean;
        }

        return $clean;
    }
}
