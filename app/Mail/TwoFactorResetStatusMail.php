<?php

namespace App\Mail;

use App\Models\TwoFactorResetRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorResetStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public TwoFactorResetRequest $resetRequest,
        public string $status
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = $this->status === 'approved'
            ? 'Pengajuan Reset 2FA Disetujui'
            : 'Pengajuan Reset 2FA Ditolak';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.two_factor_reset_status',
            with: [
                'user' => $this->user,
                'resetRequest' => $this->resetRequest,
                'status' => $this->status,
            ],
        );
    }
}

