<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class VerifyEmailForSpa extends VerifyEmail
{
    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'api.verification.verify',
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
        );
    }

    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verifikasi Email Vocaseek')
            ->greeting('Halo '.$notifiable->nama.',')
            ->line('Terima kasih telah mendaftar di Vocaseek.')
            ->line('Sebelum login, mohon verifikasi alamat email Anda terlebih dahulu.')
            ->action('Verifikasi Email', $verificationUrl)
            ->line('Jika Anda tidak merasa membuat akun ini, abaikan email ini.');
    }
}
