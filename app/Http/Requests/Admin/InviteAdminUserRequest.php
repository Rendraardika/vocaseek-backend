<?php

namespace App\Http\Requests\Admin;

use App\Models\AdminInvitation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'super_admin';
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'email' => [
                'bail',
                'required',
                'email:rfc,dns',
                Rule::unique('users', 'email'),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $hasPendingInvitation = AdminInvitation::query()
                        ->where('email', $value)
                        ->whereNull('used_at')
                        ->whereNull('cancelled_at')
                        ->where('expires_at', '>', now())
                        ->exists();

                    if ($hasPendingInvitation) {
                        $fail('Email sudah memiliki undangan admin yang masih aktif.');
                    }
                },
            ],
            'notelp' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(['staff_admin'])],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Email sudah terdaftar sebagai admin internal.',
        ];
    }
}
