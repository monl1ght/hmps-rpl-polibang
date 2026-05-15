<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminPasswordChangeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminPasswordApprovalController extends Controller
{
    public function show(string $token): Response
    {
        $changeRequest = $this->findRequest($token);

        return Inertia::render('admin/pages/auth/PasswordApproval', [
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
                'status' => AdminPasswordChangeRequest::STATUS_EXPIRED,
            ]);

            return back()->with('error', 'Permintaan sudah kedaluwarsa.');
        }

        $validated = $request->validate([
            'approver_name' => ['nullable', 'string', 'max:160'],
            'approval_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $changeRequest->update([
            'status' => AdminPasswordChangeRequest::STATUS_APPROVED,
            'approved_at' => now(),
            'approver_name' => $validated['approver_name'] ?: 'Ketua HMPS',
            'approval_note' => $validated['approval_note'] ?? null,
        ]);

        return back()->with(
            'success',
            'Permintaan ganti password berhasil disetujui.'
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
                'status' => AdminPasswordChangeRequest::STATUS_EXPIRED,
            ]);

            return back()->with('error', 'Permintaan sudah kedaluwarsa.');
        }

        $validated = $request->validate([
            'approver_name' => ['nullable', 'string', 'max:160'],
            'reject_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $changeRequest->update([
            'status' => AdminPasswordChangeRequest::STATUS_REJECTED,
            'rejected_at' => now(),
            'approver_name' => $validated['approver_name'] ?: 'Ketua HMPS',
            'reject_reason' => $validated['reject_reason'] ?? null,
        ]);

        return back()->with(
            'success',
            'Permintaan ganti password berhasil ditolak.'
        );
    }

    private function findRequest(string $token): AdminPasswordChangeRequest
    {
        $changeRequest = AdminPasswordChangeRequest::query()
            ->with('user:id,name,username,email')
            ->where('token', $token)
            ->firstOrFail();

        if ($changeRequest->isPending() && $changeRequest->isExpired()) {
            $changeRequest->update([
                'status' => AdminPasswordChangeRequest::STATUS_EXPIRED,
            ]);

            $changeRequest->refresh();
        }

        return $changeRequest;
    }

    private function mapRequest(AdminPasswordChangeRequest $request): array
    {
        return [
            'token' => $request->token,
            'status' => $request->status,
            'status_label' => $request->statusLabel(),
            'requested_by' => [
                'name' => $request->user?->name,
                'username' => $request->user?->username,
                'email' => $request->user?->email,
            ],
            'approver_name' => $request->approver_name,
            'requested_at' => $request->requested_at?->format('Y-m-d H:i:s'),
            'approved_at' => $request->approved_at?->format('Y-m-d H:i:s'),
            'rejected_at' => $request->rejected_at?->format('Y-m-d H:i:s'),
            'completed_at' => $request->completed_at?->format('Y-m-d H:i:s'),
            'expires_at' => $request->expires_at?->format('Y-m-d H:i:s'),
            'approval_note' => $request->approval_note,
            'reject_reason' => $request->reject_reason,
        ];
    }
}
