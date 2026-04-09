<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreTwoFactorResetRequest;
use App\Models\TwoFactorResetRequest;
use Illuminate\Http\RedirectResponse;

final class TwoFactorResetRequestController extends Controller
{
    public function store(StoreTwoFactorResetRequest $request): RedirectResponse
    {
        $user = $request->user();

        TwoFactorResetRequest::create([
            'requester_id' => $user->id,
            'reason' => $request->string('reason')->toString(),
            'status' => 'pending',
        ]);

        return redirect()->route('profile.account')->with('success', 'Pengajuan reset 2FA berhasil dikirim dan menunggu persetujuan programmer.');
    }
}

