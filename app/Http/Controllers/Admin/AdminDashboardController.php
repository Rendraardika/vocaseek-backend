<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\JobApplication;
use App\Models\Lowongan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    public function getOverview()
    {
        $totalTalents = User::where('role', 'intern')->count();
        $totalPartners = CompanyProfile::count();
        $totalOpenings = Lowongan::whereIn('status', ['OPEN', 'ACTIVE'])->count();
        $scheduledMeetings = 8;

        $lastMonth = now()->subMonth();
        $lastMonthTalents = User::where('role', 'intern')
            ->whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->count();

        $talentGrowth = $lastMonthTalents > 0
            ? round((($totalTalents - $lastMonthTalents) / $lastMonthTalents) * 100)
            : 0;

        $recentActivity = JobApplication::with(['user', 'lowongan.companyProfile'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($app) => [
                'application_id' => $app->application_id,
                'user_id' => $app->user_id,
                'name' => $app->user->nama ?? 'Unknown',
                'role_category' => $app->lowongan->judul_posisi ?? $app->lowongan->judul_pekerjaan ?? 'N/A',
                'organization' => $app->lowongan->companyProfile->nama_perusahaan ?? 'N/A',
                'company_name' => $app->lowongan->companyProfile->nama_perusahaan ?? 'N/A',
                'nama_perusahaan' => $app->lowongan->companyProfile->nama_perusahaan ?? 'N/A',
                'status' => $this->formatStatus($app->status),
            ]);

        return response()->json([
            'status' => 'success',
            'user_info' => [
                'name' => Auth::user()->nama,
                'role_display' => Auth::user()->role === 'super_admin' ? 'Master Admin' : 'Staff Operasional',
                'is_super' => Auth::user()->role === 'super_admin',
            ],
            'dashboard_data' => [
                'total_talents' => [
                    'value' => number_format($totalTalents),
                    'growth' => ($talentGrowth >= 0 ? '+' : '')."{$talentGrowth}% vs last month",
                ],
                'partners' => [
                    'value' => number_format($totalPartners),
                    'growth' => '+5% vs last month',
                ],
                'openings' => [
                    'value' => number_format($totalOpenings),
                    'growth' => '-2% from yesterday',
                ],
                'meetings' => [
                    'value' => str_pad($scheduledMeetings, 2, '0', STR_PAD_LEFT),
                    'label' => 'Scheduled today',
                ],
            ],
            'recent_activity' => $recentActivity,
        ]);
    }

    private function formatStatus($status)
    {
        return match ($status) {
            'APPLIED', 'REVIEWED' => 'REVIEWING',
            'OFFER', 'SHORTLISTED' => 'ACCEPTED',
            'REJECTED' => 'DECLINED',
            default => 'REVIEWING',
        };
    }
}
