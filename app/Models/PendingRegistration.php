<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingRegistration extends Model
{
    protected $table = 'pending_registrations';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'role',
        'notelp',
        'preferred_locale',
        'company_payload',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'company_payload' => 'array',
        ];
    }
}
