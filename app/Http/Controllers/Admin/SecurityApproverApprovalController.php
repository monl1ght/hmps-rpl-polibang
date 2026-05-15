<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityApprover;
use App\Models\SecurityApproverChangeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SecurityApproverApprovalController extends Controller
{
    public function show(string $token): Response
    {
        $changeRequest = $this->findRequest($token);

        return Inertia::render('admin/pages/security/ApproverApproval', [
            'changeRequest' => $this->mapRequest($changeRequest),
        ]);
    }

    public function approve(Request $request, string $token): RedirectResponse
    {
        $changeRequest = $this->findRequest($token);

        if (! $changeRequest->isPending()) {
            return back()->with('error', 'Permintaan ini sudah tidak dapat diproses.');
        }

        if ($changeRequest->isExpired()) {
            $changeRequest->update([
                'status' => SecurityApproverChangeRequest::STATUS_EXPIRED,
            ]);

            return back()->with('error', 'Permintaan sudah kedaluwarsa.');
        }

        $validated = $request->validate([
            'processed_by_name' => ['nullable', 'string', 'max:160'],
            'approval_note' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($changeRequest, $validated) {
            SecurityApprover::query()
                ->where('is_primary', true)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'deactivated_at' => now(),
                ]);

            SecurityApprover::create([
                'name' => $changeRequest->new_name,
                'position' => $changeRequest->new_position,
                'whatsapp_number' => $changeRequest->new_whatsapp_number,
                'normalized_whatsapp_number' => $changeRequest->new_normalized_whatsapp_number,
                'role' => 'primary_approver',
                'is_primary' => true,
                'is_active' => true,
                'activated_at' => now(),
            ]);

            $changeRequest->update([
                'status' => SecurityApproverChangeRequest::STATUS_APPROVED,
                'approved_at' => now(),
                'completed_at' => now(),
                'processed_by_name' => $validated['processed_by_name'] ?: 'Ketua HMPS',
                'approval_note' => $validated['approval_note'] ?? null,
            ]);
        });

        return back()->with(
            'success',
            'Perubahan nomor Ketua HMPS berhasil disetujui dan nomor baru sudah aktif.'
        );
    }

    public function reject(Request $request, string $token): RedirectResponse
    {
        $changeRequest = $this->findRequest($token);

        if (! $changeRequest->isPending()) {
            return back()->with('error', 'Permintaan ini sudah tidak dapat diproses.');
        }

        if ($changeRequest->isExpired()) {
            $changeRequest->update([
                'status' => SecurityApproverChangeRequest::STATUS_EXPIRED,
            ]);

            return back()->with('error', 'Permintaan sudah kedaluwarsa.');
        }

        $validated = $request->validate([
            'processed_by_name' => ['nullable', 'string', 'max:160'],
            'reject_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $changeRequest->update([
            'status' => SecurityApproverChangeRequest::STATUS_REJECTED,
            'rejected_at' => now(),
            'processed_by_name' => $validated['processed_by_name'] ?: 'Ketua HMPS',
            'reject_reason' => $validated['reject_reason'] ?? null,
        ]);

        return back()->with(
            'success',
            'Permintaan perubahan nomor Ketua HMPS berhasil ditolak.'
        );
    }

    private function findRequest(string $token): SecurityApproverChangeRequest
    {
        $changeRequest = SecurityApproverChangeRequest::query()
            ->with('requestedBy:id,name,username,email')
            ->where('token', $token)
            ->firstOrFail();

        if ($changeRequest->isPending() && $changeRequest->isExpired()) {
            $changeRequest->update([
                'status' => SecurityApproverChangeRequest::STATUS_EXPIRED,
            ]);

            $changeRequest->refresh();
        }

        return $changeRequest;
    }

    private function mapRequest(SecurityApproverChangeRequest $request): array
    {
        return [
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
        ];
    }
}
