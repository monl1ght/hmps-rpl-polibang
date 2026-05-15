<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminPasswordChangeRequest;
use App\Models\SecurityApprover;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminPasswordChangeController extends Controller
{
    public function index(Request $request): Response
    {
        $approver = $this->activeApprover();

        $latestRequest = AdminPasswordChangeRequest::query()
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->first();

        if ($latestRequest?->isPending() && $latestRequest->isExpired()) {
            $latestRequest->update([
                'status' => AdminPasswordChangeRequest::STATUS_EXPIRED,
            ]);

            $latestRequest->refresh();
        }

        return Inertia::render('admin/pages/auth/ChangePassword', [
            'approvalPhone' => $approver->whatsapp_number,
            'approvalName' => $approver->name,
            'latestRequest' => $latestRequest
                ? $this->mapRequest($latestRequest, $approver)
                : null,
        ]);
    }

    public function storeRequest(Request $request): RedirectResponse
    {
        $user = $request->user();
        $approver = $this->activeApprover();

        AdminPasswordChangeRequest::query()
            ->where('user_id', $user->id)
            ->where('status', AdminPasswordChangeRequest::STATUS_PENDING)
            ->update([
                'status' => AdminPasswordChangeRequest::STATUS_CANCELLED,
            ]);

        AdminPasswordChangeRequest::create([
            'user_id' => $user->id,
            'token' => Str::random(64),
            'approver_phone' => $approver->normalized_whatsapp_number,
            'approver_name' => $approver->name,
            'status' => AdminPasswordChangeRequest::STATUS_PENDING,
            'requested_at' => now(),
            'expires_at' => now()->addDay(),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        return back()->with(
            'success',
            'Permintaan ganti password berhasil dibuat. Silakan kirim link approval ke Ketua HMPS melalui WhatsApp.'
        );
    }

    public function updatePassword(
        Request $request,
        AdminPasswordChangeRequest $passwordChangeRequest
    ): RedirectResponse {
        $user = $request->user();

        if ($passwordChangeRequest->user_id !== $user->id) {
            abort(403);
        }

        if (! $passwordChangeRequest->isApproved()) {
            return back()->with(
                'error',
                'Password belum bisa diganti karena permintaan belum disetujui Ketua HMPS.'
            );
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string', 'max:255'],
            'password' => [
                'required',
                'confirmed',
                Password::min(10)->letters()->mixedCase()->numbers(),
            ],
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak sesuai.',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password lama tidak sesuai.',
            ]);
        }

        if (Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Password baru tidak boleh sama dengan password lama.',
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        $passwordChangeRequest->update([
            'status' => AdminPasswordChangeRequest::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $request->session()->regenerate();

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Password admin berhasil diganti.');
    }

    private function mapRequest(AdminPasswordChangeRequest $request, SecurityApprover $approver): array
    {
        return [
            'id' => $request->id,
            'token' => $request->token,
            'status' => $request->status,
            'status_label' => $request->statusLabel(),
            'approver_phone' => $request->approver_phone,
            'approver_name' => $request->approver_name,
            'requested_at' => $request->requested_at?->format('Y-m-d H:i:s'),
            'approved_at' => $request->approved_at?->format('Y-m-d H:i:s'),
            'rejected_at' => $request->rejected_at?->format('Y-m-d H:i:s'),
            'completed_at' => $request->completed_at?->format('Y-m-d H:i:s'),
            'expires_at' => $request->expires_at?->format('Y-m-d H:i:s'),
            'approval_note' => $request->approval_note,
            'reject_reason' => $request->reject_reason,
            'whatsapp_url' => $request->isPending()
                ? $this->buildWhatsappUrl($request, $approver)
                : null,
        ];
    }

    private function buildWhatsappUrl(
        AdminPasswordChangeRequest $request,
        SecurityApprover $approver
    ): string {
        $approvalUrl = URL::temporarySignedRoute(
            'admin.password.approval.show',
            now()->addDay(),
            ['token' => $request->token]
        );

        $message = "Assalamu'alaikum {$approver->position}.\n\n"
            . "Admin website HMPS RPL mengajukan permintaan untuk mengganti password akun admin.\n\n"
            . "Status: Menunggu Persetujuan\n"
            . "Batas waktu persetujuan: " . $request->expires_at?->format('d M Y H:i') . "\n\n"
            . "Silakan klik link berikut untuk menyetujui atau menolak permintaan:\n"
            . $approvalUrl . "\n\n"
            . "Catatan: Password baru tidak dikirim melalui WhatsApp demi keamanan.";

        return 'https://wa.me/'
            . $approver->normalized_whatsapp_number
            . '?text='
            . rawurlencode($message);
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

        $phone = '087879175646';

        return SecurityApprover::create([
            'name' => 'Ketua HMPS',
            'position' => 'Ketua HMPS',
            'whatsapp_number' => $phone,
            'normalized_whatsapp_number' => $this->normalizeWhatsappNumber($phone),
            'role' => 'primary_approver',
            'is_primary' => true,
            'is_active' => true,
            'activated_at' => now(),
        ]);
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
