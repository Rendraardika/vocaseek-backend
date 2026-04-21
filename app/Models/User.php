<?php

namespace App\Models;

use App\Notifications\VerifyEmailForSpa;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected static ?bool $hasPreferredLocaleColumn = null;

    protected $table = 'users';
    
    // Karena kamu pakai user_id, pastikan ini konsisten di semua tabel relasi
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'nama',
        'email',
        'email_verified_at',
        'password',
        'role',
        'notelp',
        'foto',
        'preferred_locale',
        'google_id', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting password agar otomatis di-hash
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
        ];
    }

    // --- RELASI ---

    /**
     * Relasi ke profil Intern
     */
    public function internProfile()
    {
        // Parameter: (Model, Foreign Key di tabel tujuan, Local Key di tabel users)
        return $this->hasOne(InternProfile::class, 'user_id', 'user_id');
    }

    /**
     * Relasi ke profil Company (Penting untuk Dashboard Mitra)
     */
    public function companyProfile()
    {
        return $this->hasOne(CompanyProfile::class, 'user_id', 'user_id');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'user_id', 'user_id');
    }

    public function sendPasswordResetNotification($token): void
    {
        $resetUrl = rtrim(config('app.frontend_url'), '/')
            .'/reset-password?token='.$token.'&email='.urlencode($this->getEmailForPasswordReset());

        $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password Vocaseek</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
    <p>Halo {$this->nama},</p>
    <p>Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda.</p>
    <p>
        Klik tautan berikut untuk membuat kata sandi baru:
        <br>
        <a href="{$resetUrl}">{$resetUrl}</a>
    </p>
    <p>Tautan ini berlaku selama {$this->passwordResetExpiryMinutes()} menit.</p>
    <p>Jika Anda tidak merasa meminta reset password, abaikan email ini.</p>
    <p>Terima kasih,<br>Vocaseek</p>
</body>
</html>
HTML;

        Mail::html($html, function ($message) {
            $message->to($this->email)
                ->subject('Reset Password Vocaseek');
        });
    }

    private function passwordResetExpiryMinutes(): int
    {
        return (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);
    }

    public static function supportsPreferredLocale(): bool
    {
        if (self::$hasPreferredLocaleColumn === null) {
            self::$hasPreferredLocaleColumn = Schema::hasColumn('users', 'preferred_locale');
        }

        return self::$hasPreferredLocaleColumn;
    }

    public function getResolvedLocale(): string
    {
        if (self::supportsPreferredLocale() && filled($this->preferred_locale)) {
            return $this->preferred_locale;
        }

        return app()->getLocale();
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailForSpa());
    }
}
