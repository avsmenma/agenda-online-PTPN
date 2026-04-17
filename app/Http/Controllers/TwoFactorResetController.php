<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ApproveTwoFactorResetRequest;
use App\Http\Requests\RejectTwoFactorResetRequest;
use App\Mail\TwoFactorResetStatusMail;
use App\Models\TwoFactorResetRequest;
use App\Models\User;
use App\Services\FonnteWhatsAppService;
use App\Traits\LogsProgrammerActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

final class TwoFactorResetController extends Controller
{
    use LogsProgrammerActivity;

    public function index()
    {
        $requests = TwoFactorResetRequest::query()
            ->with(['requester', 'programmer'])
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderByDesc('id')
            ->paginate(30);

        return view('programmer.2fa_reset.index', [
            'requests' => $requests,
        ]);
    }

    public function approve(ApproveTwoFactorResetRequest $request, int $id, FonnteWhatsAppService $whatsAppService): RedirectResponse
    {
        $programmer = $request->user();

        $handled = DB::transaction(function () use ($id, $programmer) {
            $resetRequest = TwoFactorResetRequest::query()->lockForUpdate()->findOrFail($id);
            if ($resetRequest->status !== 'pending') {
                return null;
            }

            $targetUser = User::query()->lockForUpdate()->findOrFail($resetRequest->requester_id);

            $targetUser->forceFill([
                'two_factor_enabled' => false,
                'two_factor_secret' => null,
                'two_factor_confirmed_at' => null,
                'two_factor_recovery_codes' => null,
            ])->save();

            $resetRequest->forceFill([
                'status' => 'approved',
                'programmer_id' => $programmer->id,
                'handled_at' => now(),
            ])->save();

            return [$resetRequest, $targetUser];
        });

        if (!$handled) {
            return redirect()->route('programmer.2fa-reset-requests.index')->with('error', 'Request sudah tidak berstatus pending.');
        }

        /** @var TwoFactorResetRequest $resetRequest */
        /** @var User $targetUser */
        [$resetRequest, $targetUser] = $handled;

        $this->notifyUser($targetUser, $resetRequest, 'approved', $whatsAppService);

        $this->logActivity(
            'reset_2fa',
            ['type' => 'User', 'id' => $targetUser->id, 'description' => "{$targetUser->name} (@{$targetUser->username})"],
            "Approve reset 2FA request #{$resetRequest->id} untuk user {$targetUser->name} (@{$targetUser->username})"
        );

        return redirect()->route('programmer.2fa-reset-requests.index')->with('success', 'Reset 2FA disetujui dan 2FA user sudah direset.');
    }

    public function reject(RejectTwoFactorResetRequest $request, int $id, FonnteWhatsAppService $whatsAppService): RedirectResponse
    {
        $programmer = $request->user();

        $handled = DB::transaction(function () use ($id, $programmer, $request) {
            $resetRequest = TwoFactorResetRequest::query()->lockForUpdate()->findOrFail($id);
            if ($resetRequest->status !== 'pending') {
                return null;
            }

            $resetRequest->forceFill([
                'status' => 'rejected',
                'programmer_id' => $programmer->id,
                'handled_at' => now(),
                'notes' => (string) $request->input('notes'),
            ])->save();

            $targetUser = User::findOrFail($resetRequest->requester_id);

            return [$resetRequest, $targetUser];
        });

        if (!$handled) {
            return redirect()->route('programmer.2fa-reset-requests.index')->with('error', 'Request sudah tidak berstatus pending.');
        }

        /** @var TwoFactorResetRequest $resetRequest */
        /** @var User $targetUser */
        [$resetRequest, $targetUser] = $handled;

        $this->notifyUser($targetUser, $resetRequest, 'rejected', $whatsAppService);

        $this->logActivity(
            'reject_2fa_reset',
            ['type' => 'User', 'id' => $targetUser->id, 'description' => "{$targetUser->name} (@{$targetUser->username})"],
            "Reject reset 2FA request #{$resetRequest->id} untuk user {$targetUser->name} (@{$targetUser->username})"
        );

        return redirect()->route('programmer.2fa-reset-requests.index')->with('success', 'Request reset 2FA ditolak.');
    }

    private function notifyUser(User $user, TwoFactorResetRequest $resetRequest, string $status, FonnteWhatsAppService $whatsAppService): void
    {
        $message = $status === 'approved'
            ? "Pengajuan reset 2FA Anda telah disetujui. 2FA pada akun Anda sudah dinonaktifkan. Silakan aktifkan kembali 2FA setelah login."
            : "Pengajuan reset 2FA Anda ditolak. Alasan: " . ($resetRequest->notes ?? '-');

        $userPhone = trim((string) ($user->phone_number ?? ''));
        $userEmail = trim((string) ($user->email ?? ''));

        if ($userPhone !== '' && config('fonnte.enabled', true)) {
            $result = $whatsAppService->sendMessage($userPhone, $message);
            if (($result['success'] ?? false) === true) {
                return;
            }
        }

        $fallbackEnabled = filter_var(config('notifications.fallback_email_enabled', true), FILTER_VALIDATE_BOOL);
        if ($fallbackEnabled && $userEmail !== '') {
            Mail::to($userEmail)->send(new TwoFactorResetStatusMail($user, $resetRequest, $status));
        }
    }
}

