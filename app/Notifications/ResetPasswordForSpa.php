<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordForSpa extends ResetPassword implements ShouldQueue
{
    use Queueable;

    public function __construct(string $token)
    {
        parent::__construct($token);
    }

    public function toMail($notifiable): MailMessage
    {
        $resetUrl = rtrim((string) config('app.public_frontend_url', config('app.frontend_url')), '/')
            .'/reset-password?'.http_build_query([
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);

        return (new MailMessage)
            ->subject('Reset Password Vocaseek')
            ->view('emails.reset-password', [
                'name' => $notifiable->nama ?? 'Pengguna Vocaseek',
                'resetLink' => $resetUrl,
                'expiresInMinutes' => (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
            ]);
    }
}
