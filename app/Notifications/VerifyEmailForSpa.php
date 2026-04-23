<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class VerifyEmailForSpa extends VerifyEmail
{
    protected function verificationUrl($notifiable): string
    {
        $path = URL::temporarySignedRoute(
            'api.verification.verify',
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
            absolute: false,
        );

        return rtrim((string) config('app.url'), '/').'/'.Str::of($path)->ltrim('/');
    }

    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verifikasi Email Vocaseek')
            ->view('emails.verify-email', [
                'name' => $notifiable->nama,
                'email' => $notifiable->getEmailForVerification(),
                'verificationUrl' => $verificationUrl,
                'expiresInMinutes' => (int) config('auth.verification.expire', 60),
                'introText' => 'Terima kasih telah mendaftar di Vocaseek. Sebelum login, mohon verifikasi alamat email Anda terlebih dahulu.',
            ]);
    }
}
