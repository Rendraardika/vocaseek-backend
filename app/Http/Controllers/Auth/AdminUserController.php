<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InviteAdminUserRequest;
use App\Http\Requests\Admin\UpdateManagedAdminUserStatusRequest;
use App\Models\AdminInvitation;
use App\Models\User;
use App\Services\Admin\AdminInvitationService;
use Illuminate\Http\JsonResponse;

class AdminUserController extends Controller
{
    public function __construct(
        private readonly AdminInvitationService $invitationService,
    ) {
    }

    /**
     * 1. LIST ADMIN INTERNAL (Halaman Utama User Management - Gambar 1)
     */
    public function index(): JsonResponse
    {
        $adminUsers = User::whereIn('role', ['super_admin', 'staff_admin'])
            ->with(['latestAdminInvitation', 'inviter'])
            ->latest()
            ->get();

        $orphanInvitations = AdminInvitation::query()
            ->with(['inviter'])
            ->whereNull('user_id')
            ->latest('id')
            ->get()
            ->unique('email')
            ->values();

        $rows = $adminUsers
            ->map(fn (User $user) => $this->transformAdminUser($user))
            ->merge(
                $orphanInvitations->map(
                    fn (AdminInvitation $invitation) => $this->transformPendingInvitation($invitation)
                )
            )
            ->sortByDesc(fn (array $item) => $item['sort_at_iso'] ?? '')
            ->values();

        return response()->json([
            'status' => 'success',
            'stats' => [
                'total_admin' => $rows->count(),
                'super_admin' => $adminUsers->where('role', 'super_admin')->count(),
                'staff_admin' => $adminUsers->where('role', 'staff_admin')->count() + $orphanInvitations->count(),
            ],
            'data' => $rows,
            'pagination' => [
                'total' => $rows->count(),
                'per_page' => $rows->count(),
                'current_page' => 1,
                'last_page' => 1,
            ]
        ]);
    }

    public function invite(InviteAdminUserRequest $request): JsonResponse
    {
        [$admin, $invitation, $activationUrl] = $this->invitationService->invite(
            $request->validated(),
            $request->user(),
        );

        $responseRow = $admin
            ? $this->transformAdminUser($admin->loadMissing(['latestAdminInvitation', 'inviter']))
            : $this->transformPendingInvitation($invitation->fresh(['inviter']));

        return response()->json([
            'status' => 'success',
            'message' => 'Undangan berhasil dikirim ke email admin.',
            'data' => [
                ...$responseRow,
                'invitation' => [
                    'id' => $invitation->id,
                    'state' => $invitation->resolveState(),
                    'expires_at' => optional($invitation->expires_at)->toIso8601String(),
                    'activation_link' => $this->invitationService->isDebugActivationLinkEnabled()
                        ? $activationUrl
                        : null,
                ],
            ]
        ], 201);
    }

    public function updateStatus(UpdateManagedAdminUserStatusRequest $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $targetStatus = $request->validated('status');

        if ($user->role === 'super_admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Status super admin tidak dapat diubah dari halaman ini.',
            ], 422);
        }

        if (
            $targetStatus === 'active'
            && (
                empty($user->password)
                || $user->status === 'pending_invitation'
                || optional($user->latestAdminInvitation)->resolveState() === 'pending'
            )
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Admin belum menyelesaikan aktivasi akun. Status aktif hanya dapat diberikan setelah password dibuat melalui tautan undangan.',
            ], 422);
        }

        $user->update(['status' => $targetStatus]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status admin berhasil diperbarui.',
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $admin = User::findOrFail($id);

        if (auth()->id() == $admin->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak dapat menghapus akun sendiri.',
            ], 403);
        }

        AdminInvitation::query()->where('user_id', $admin->user_id)->delete();
        $admin->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Admin berhasil dihapus dari sistem.',
        ]);
    }

    private function transformAdminUser(User $user): array
    {
        $invitation = $user->latestAdminInvitation;
        $displayStatus = $this->resolveDisplayStatus($user, $invitation);

        return [
            'id' => $user->user_id,
            'user_id' => $user->user_id,
            'nama' => $user->nama,
            'name' => $user->nama,
            'full_name' => $user->nama,
            'email' => $user->email,
            'email_address' => $user->email,
            'foto' => $user->foto,
            'notelp' => $user->notelp,
            'identity' => [
                'nama' => $user->nama,
                'email' => $user->email,
                'foto' => $user->foto,
            ],
            'role' => $user->role,
            'role_label' => strtoupper(str_replace('_', ' ', $user->role)),
            'status' => $displayStatus,
            'status_label' => $this->mapStatusLabel($displayStatus),
            'joined_at' => optional($user->created_at)->format('d M Y') ?? 'N/A',
            'joined_at_iso' => optional($user->created_at)->toIso8601String(),
            'sort_at_iso' => optional($user->created_at)->toIso8601String(),
            'invited_by' => $user->inviter?->nama,
            'invitation' => $invitation ? [
                'id' => $invitation->id,
                'state' => $invitation->resolveState(),
                'expires_at' => optional($invitation->expires_at)->toIso8601String(),
                'used_at' => optional($invitation->used_at)->toIso8601String(),
                'cancelled_at' => optional($invitation->cancelled_at)->toIso8601String(),
            ] : null,
            'actions' => [
                'can_resend' => $user->role === 'staff_admin' && in_array($displayStatus, ['pending', 'expired', 'cancelled'], true),
                'can_cancel' => $user->role === 'staff_admin' && $displayStatus === 'pending',
                'can_edit' => $displayStatus === 'active',
                'can_delete' => auth()->id() !== $user->user_id,
            ],
        ];
    }

    private function transformPendingInvitation(AdminInvitation $invitation): array
    {
        $displayStatus = match ($invitation->resolveState()) {
            'cancelled' => 'cancelled',
            'expired' => 'expired',
            'used' => 'active',
            default => 'pending',
        };

        return [
            'id' => sprintf('invitation-%s', $invitation->id),
            'user_id' => null,
            'nama' => $invitation->name,
            'name' => $invitation->name,
            'full_name' => $invitation->name,
            'email' => $invitation->email,
            'email_address' => $invitation->email,
            'foto' => null,
            'notelp' => $invitation->phone,
            'identity' => [
                'nama' => $invitation->name,
                'email' => $invitation->email,
                'foto' => null,
            ],
            'role' => $invitation->role,
            'role_label' => strtoupper(str_replace('_', ' ', $invitation->role)),
            'status' => $displayStatus,
            'status_label' => $this->mapStatusLabel($displayStatus),
            'joined_at' => optional($invitation->created_at)->format('d M Y') ?? 'N/A',
            'joined_at_iso' => optional($invitation->created_at)->toIso8601String(),
            'sort_at_iso' => optional($invitation->created_at)->toIso8601String(),
            'invited_by' => $invitation->inviter?->nama,
            'invitation' => [
                'id' => $invitation->id,
                'state' => $invitation->resolveState(),
                'expires_at' => optional($invitation->expires_at)->toIso8601String(),
                'used_at' => optional($invitation->used_at)->toIso8601String(),
                'cancelled_at' => optional($invitation->cancelled_at)->toIso8601String(),
            ],
            'actions' => [
                'can_resend' => in_array($displayStatus, ['pending', 'expired', 'cancelled'], true),
                'can_cancel' => $displayStatus === 'pending',
                'can_edit' => false,
                'can_delete' => false,
            ],
        ];
    }

    private function resolveDisplayStatus(User $user, ?AdminInvitation $invitation): string
    {
        if ($user->status === 'active' || $user->role === 'super_admin') {
            return 'active';
        }

        if (!$invitation) {
            return $user->status === 'disabled' ? 'disabled' : 'pending';
        }

        return match ($invitation->resolveState()) {
            'cancelled' => 'cancelled',
            'expired' => 'expired',
            'used' => 'active',
            default => $user->status === 'disabled' ? 'disabled' : 'pending',
        };
    }

    private function mapStatusLabel(string $status): string
    {
        return match ($status) {
            'active' => 'Aktif',
            'pending' => 'Pending',
            'expired' => 'Kedaluwarsa',
            'cancelled' => 'Dibatalkan',
            'disabled' => 'Nonaktif',
            default => ucfirst($status),
        };
    }
}
