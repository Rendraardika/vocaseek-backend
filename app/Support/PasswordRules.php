<?php

namespace App\Support;

final class PasswordRules
{
    public static function strong(): array
    {
        return [
            'string',
            'min:8',
            'regex:/^[A-Z](?=.*[^A-Za-z0-9])[^\s]{7,}$/',
        ];
    }

    public static function message(): string
    {
        return 'Password minimal 8 karakter, huruf pertama harus kapital, wajib mengandung karakter unik, dan tidak boleh memakai spasi.';
    }
}
