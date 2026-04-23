<?php

namespace App\Console\Commands;

use App\Models\AdminInvitation;
use Illuminate\Console\Command;

class PruneAdminInvitations extends Command
{
    protected $signature = 'admin-invitations:prune {--days= : Hari retensi invitation expired/cancelled}';

    protected $description = 'Hapus admin invitation yang expired atau cancelled dan sudah melewati masa retensi.';

    public function handle(): int
    {
        $retentionDays = (int) ($this->option('days') ?: config('app.admin_invitation_cleanup_days', 7));
        $retentionDays = max($retentionDays, 1);
        $cutoff = now()->subDays($retentionDays);

        $expiredCount = AdminInvitation::query()
            ->whereNull('used_at')
            ->whereNull('cancelled_at')
            ->where('expires_at', '<', $cutoff)
            ->delete();

        $cancelledCount = AdminInvitation::query()
            ->whereNotNull('cancelled_at')
            ->where('cancelled_at', '<', $cutoff)
            ->delete();

        $totalDeleted = $expiredCount + $cancelledCount;

        $this->info(sprintf(
            'Cleanup selesai. %d invitation dihapus (expired: %d, cancelled: %d) dengan retensi %d hari.',
            $totalDeleted,
            $expiredCount,
            $cancelledCount,
            $retentionDays,
        ));

        return self::SUCCESS;
    }
}
