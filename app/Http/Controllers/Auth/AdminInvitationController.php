<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AcceptAdminInvitationRequest;
use App\Http\Requests\Admin\CancelAdminInvitationRequest;
use App\Http\Requests\Admin\ResendAdminInvitationRequest;
use App\Http\Requests\Admin\VerifyAdminInvitationRequest;
use App\Models\AdminInvitation;
use App\Services\Admin\AdminInvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AdminInvitationController extends Controller
{
    public function __construct(
        private readonly AdminInvitationService $invitationService,
    ) {
    }

    public function verify(VerifyAdminInvitationRequest $request): JsonResponse
    {
        try {
            $invitation = $this->invitationService->verifyToken($request->validated('token'));
        } catch (ValidationException $exception) {
            return $this->buildValidationErrorResponse($exception);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'invitation_id' => $invitation->id,
                'name' => $invitation->name,
                'email' => $invitation->email,
                'phone' => $invitation->phone,
                'role' => $invitation->role,
                'expires_at' => optional($invitation->expires_at)->toIso8601String(),
                'invited_by' => $invitation->inviter?->nama,
                'state' => $invitation->resolveState(),
            ],
        ]);
    }

    public function accept(AcceptAdminInvitationRequest $request): JsonResponse
    {
        try {
            $invitation = $this->invitationService->accept(
                $request->validated('token'),
                $request->validated('password'),
            );
        } catch (ValidationException $exception) {
            return $this->buildValidationErrorResponse($exception);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Akun admin berhasil diaktifkan.',
            'data' => [
                'email' => $invitation->email,
                'role' => $invitation->role,
                'login_path' => '/login',
            ],
        ]);
    }

    public function resend(ResendAdminInvitationRequest $request): JsonResponse
    {
        $invitation = AdminInvitation::with(['user', 'inviter'])->findOrFail(
            $request->validated('invitation_id'),
        );

        try {
            [$newInvitation, $activationUrl] = $this->invitationService->resend(
                $invitation,
                $request->user(),
            );
        } catch (ValidationException $exception) {
            return $this->buildValidationErrorResponse($exception);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Undangan berhasil dikirim ulang ke email admin.',
            'data' => [
                'invitation_id' => $newInvitation->id,
                'email' => $newInvitation->email,
                'expires_at' => optional($newInvitation->expires_at)->toIso8601String(),
                'activation_link' => $this->invitationService->isDebugActivationLinkEnabled()
                    ? $activationUrl
                    : null,
            ],
        ]);
    }

    public function cancel(CancelAdminInvitationRequest $request): JsonResponse
    {
        $invitation = AdminInvitation::with(['user', 'inviter'])->findOrFail(
            $request->validated('invitation_id'),
        );

        try {
            $cancelledInvitation = $this->invitationService->cancel($invitation);
        } catch (ValidationException $exception) {
            return $this->buildValidationErrorResponse($exception);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Undangan berhasil dibatalkan.',
            'data' => [
                'invitation_id' => $cancelledInvitation->id,
                'email' => $cancelledInvitation->email,
                'state' => $cancelledInvitation->resolveState(),
            ],
        ]);
    }

    private function buildValidationErrorResponse(ValidationException $exception): JsonResponse
    {
        $errors = $exception->errors();
        $message = collect($errors)->flatten()->first() ?: 'Validasi invitation gagal.';

        $code = match ($message) {
            'Tautan aktivasi sudah kedaluwarsa.' => 'invitation_expired',
            'Tautan aktivasi sudah digunakan sebelumnya.' => 'invitation_used',
            default => 'invitation_invalid',
        };

        $statusCode = match ($code) {
            'invitation_expired' => 410,
            'invitation_used' => 409,
            default => 422,
        };

        return response()->json([
            'status' => 'error',
            'code' => $code,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}
