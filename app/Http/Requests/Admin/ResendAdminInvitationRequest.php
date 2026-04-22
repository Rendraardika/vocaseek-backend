<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ResendAdminInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'super_admin';
    }

    public function rules(): array
    {
        return [
            'invitation_id' => ['required', 'integer', 'exists:admin_invitations,id'],
        ];
    }
}
