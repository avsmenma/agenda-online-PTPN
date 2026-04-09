<?php

namespace App\Mail;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeadlineReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Dokumen $document,
        public string $notificationMessage
    ) {
    }

    public function envelope(): Envelope
    {
        $nomorAgenda = $this->document->nomor_agenda ?? null;
        $subject = $nomorAgenda ? "Pengingat Deadline Dokumen (#{$nomorAgenda})" : 'Pengingat Deadline Dokumen';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.deadline_reminder',
            with: [
                'user' => $this->user,
                'document' => $this->document,
                'message' => $this->notificationMessage,
            ],
        );
    }
}

