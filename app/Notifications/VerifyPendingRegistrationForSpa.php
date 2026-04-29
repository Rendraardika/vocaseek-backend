<?php

namespace App\Notifications;

use App\Models\PendingRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class VerifyPendingRegistrationForSpa extends Notification implements ShouldQueue
{
    use Queueable;

    private readonly int $pendingRegistrationId;

    private readonly string $name;

    private readonly string $email;

    public function __construct(PendingRegistration $pendingRegistration)
    {
        $this->pendingRegistrationId = $pendingRegistration->getKey();
        $this->name = $pendingRegistration->nama;
        $this->email = $pendingRegistration->email;
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
                'name' => $this->name,
                'email' => $this->email,
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
                'id' => $this->pendingRegistrationId,
                'hash' => sha1($this->email),
            ],
            absolute: false,
        );

        return rtrim((string) config('app.url'), '/').'/'.Str::of($path)->ltrim('/');
    }
}
