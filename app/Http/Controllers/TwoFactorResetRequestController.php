<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreTwoFactorResetRequest;
use App\Http\Requests\StoreTwoFactorResetRequestFromVerify;
use App\Models\TwoFactorResetRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

final class TwoFactorResetRequestController extends Controller
{
    /**
     * Pengajuan dari halaman profil — pengaju masih bisa login.
     */
    public function store(StoreTwoFactorResetRequest $request): RedirectResponse
    {
        $this->createPendingRequest($request->user(), $request->string('reason')->toString());

        return redirect()->route('profile.account')->with('success', 'Pengajuan reset 2FA berhasil dikirim dan menunggu persetujuan programmer.');
    }

    /**
     * Pengajuan dari halaman verifikasi 2FA — pengaju TERKUNCI dan tak bisa login.
     *
     * Tanpa jalur ini akun yang kehilangan authenticator sekaligus recovery code
     * terkunci permanen: tombol di halaman profil butuh login, sedangkan
     * programmer menolak mereset tanpa ada request berstatus pending.
     *
     * Endpoint ini HANYA membuat permintaan. Penonaktifan 2FA tetap wewenang
     * programmer lewat TwoFactorResetController::approve().
     */
    public function storeFromVerify(StoreTwoFactorResetRequestFromVerify $request): RedirectResponse
    {
        $user = $request->pendingUser();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Sesi verifikasi sudah berakhir. Silakan masukkan username dan password Anda lagi.');
        }

        $this->createPendingRequest($user, $request->string('reason')->toString());

        return redirect()->route('2fa.verify')
            ->with('info', 'Pengajuan reset 2FA sudah dikirim. Programmer akan meninjau permintaan Anda, dan Anda akan dihubungi setelah disetujui.');
    }

    private function createPendingRequest(User $user, string $reason): void
    {
        TwoFactorResetRequest::create([
            'requester_id' => $user->id,
            'reason' => $reason,
            'status' => 'pending',
        ]);
    }
}
