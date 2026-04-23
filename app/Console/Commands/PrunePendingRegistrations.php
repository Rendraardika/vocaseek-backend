<?php

namespace App\Console\Commands;

use App\Models\PendingRegistration;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class PrunePendingRegistrations extends Command
{
    protected $signature = 'pending-registrations:prune {--hours=}';

    protected $description = 'Hapus pending registration yang sudah kedaluwarsa beserta file dokumen sementaranya.';

    public function handle(): int
    {
        $hours = (int) ($this->option('hours') ?: ceil(((int) config('auth.verification.expire', 60)) / 60));
        $hours = max(1, $hours);

        $expiredRegistrations = PendingRegistration::query()
            ->where('created_at', '<=', Carbon::now()->subHours($hours))
            ->get();

        foreach ($expiredRegistrations as $pendingRegistration) {
            $payload = $pendingRegistration->company_payload ?? [];
            $directoriesToCleanup = [];

            foreach ([$payload['loa_pdf'] ?? null, $payload['akta_pdf'] ?? null] as $path) {
                if ($path) {
                    Storage::disk('public')->delete($path);
                    $directoriesToCleanup[] = dirname($path);
                }
            }

            foreach (array_unique($directoriesToCleanup) as $directory) {
                if ($directory && $directory !== '.') {
                    $remainingFiles = Storage::disk('public')->allFiles($directory);

                    if ($remainingFiles === []) {
                        Storage::disk('public')->deleteDirectory($directory);
                    }
                }
            }

            $pendingRegistration->delete();
        }

        $this->info("Pruned {$expiredRegistrations->count()} pending registration(s).");

        return self::SUCCESS;
    }
}
