<?php

namespace App\Notifications;

use App\Models\PendingRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class VerifyPendingRegistrationForSpa extends Notification
{
    use Queueable;

    public function __construct(
        private readonly PendingRegistration $pendingRegistration
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl();

        return (new MailMessage)
            ->subject('Verifikasi Email Vocaseek')
            ->view('emails.verify-email', [
                'name' => $this->pendingRegistration->nama,
                'email' => $this->pendingRegistration->email,
                'verificationUrl' => $verificationUrl,
                'expiresInMinutes' => (int) config('auth.verification.expire', 60),
                'introText' => 'Terima kasih telah mendaftar di Vocaseek. Akun Anda akan dibuat setelah alamat email ini diverifikasi.',
            ]);
    }

    private function verificationUrl(): string
    {
        $path = URL::temporarySignedRoute(
            'api.pending-registration.verify',
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $this->pendingRegistration->getKey(),
                'hash' => sha1($this->pendingRegistration->email),
            ],
            absolute: false,
        );

        return rtrim((string) config('app.url'), '/').'/'.Str::of($path)->ltrim('/');
    }
}
