<?php

namespace App\Models;

use App\Notifications\ResetPasswordForSpa;
use App\Notifications\VerifyEmailForSpa;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected static ?bool $hasPreferredLocaleColumn = null;
    protected static ?bool $hasEmailVerifiedAtColumn = null;

    protected $table = 'users';


    protected $primaryKey = 'user_id';

    protected $fillable = [
        'nama',
        'email',
        'email_verified_at',
        'password',
        'role',
        'status',
        'invited_by',
        'notelp',
        'foto',
        'preferred_locale',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
        ];
    }



    public function internProfile()
    {

        return $this->hasOne(InternProfile::class, 'user_id', 'user_id');
    }


    public function companyProfile()
    {
        return $this->hasOne(CompanyProfile::class, 'user_id', 'user_id');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'user_id', 'user_id');
    }

    public function adminInvitations()
    {
        return $this->hasMany(AdminInvitation::class, 'user_id', 'user_id');
    }

    public function latestAdminInvitation()
    {
        return $this->hasOne(AdminInvitation::class, 'user_id', 'user_id')->latestOfMany();
    }

    public function inviter()
    {
        return $this->belongsTo(self::class, 'invited_by', 'user_id');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordForSpa($token));
    }

    public static function supportsPreferredLocale(): bool
    {
        if (self::$hasPreferredLocaleColumn === null) {
            self::$hasPreferredLocaleColumn = Schema::hasColumn('users', 'preferred_locale');
        }

        return self::$hasPreferredLocaleColumn;
    }

    public static function supportsEmailVerificationColumn(): bool
    {
        if (self::$hasEmailVerifiedAtColumn === null) {
            self::$hasEmailVerifiedAtColumn = Schema::hasColumn('users', 'email_verified_at');
        }

        return self::$hasEmailVerifiedAtColumn;
    }

    public function hasVerifiedEmail(): bool
    {
        if (! self::supportsEmailVerificationColumn()) {
            return true;
        }

        return ! is_null($this->email_verified_at);
    }

    public function markEmailAsVerified(): bool
    {
        if (! self::supportsEmailVerificationColumn()) {
            return false;
        }

        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
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
        if (! self::supportsEmailVerificationColumn()) {
            return;
        }

        $this->notify(new VerifyEmailForSpa());
    }
}
