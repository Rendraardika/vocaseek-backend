<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompanyProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminVerificationController extends Controller
{
    /**
     * 1. LIST PENGAJUAN (POV Staff & Super Admin)
     */
    public function index()
    {
        // Sesuaikan dengan kolom 'status_mitra' yang kita pakai sebelumnya
        $stats = [
            'total_pending'  => CompanyProfile::where('status_mitra', 'pending')->count(),
            'total_reviewed' => CompanyProfile::where('status_mitra', 'reviewed')->count(),
            'total_active'   => CompanyProfile::where('status_mitra', 'active')->count(),
        ];

        // Daftar Pengajuan yang belum Active/Rejected
        $submissions = CompanyProfile::with('user')
            ->whereIn('status_mitra', ['pending', 'reviewed'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'stats'  => $stats,
            'data'   => $submissions->getCollection()->map(fn($item) => [
                'id'                => $item->id,
                'id_perusahaan'     => 'CMP-' . str_pad($item->id, 3, '0', STR_PAD_LEFT),
                'nama_perusahaan'   => $item->nama_perusahaan,
                'industri'          => $item->industri ?? 'N/A', // Pakai kolom industri yang baru kita buat
                'tanggal_pengajuan' => $item->created_at->format('d M Y'),
                'status_mitra'      => $item->status_mitra, 
            ])
        ]);
    }

    /**
     * 2. UBAH STATUS REVIEW (Biasanya dilakukan Staff)
     */
    public function updateReviewStatus(Request $request, $id)
    {
        $company = $this->resolveCompanyProfile($id);
        $normalizedStatus = $this->normalizeVerificationInput(
            $request->input('status', $request->input('action'))
        );

        if (!$normalizedStatus) {
            return response()->json([
                'message' => 'The selected status is invalid.',
                'allowed_status' => ['pending', 'reviewed', 'approve', 'reject', 'active', 'rejected'],
            ], 422);
        }

        if (in_array($normalizedStatus, ['active', 'rejected'], true) && Auth::user()->role !== 'super_admin') {
            return response()->json([
                'message' => __('messages.verification.super_admin_only'),
            ], 403);
        }

        $company->update(['status_mitra' => $normalizedStatus]);

        if ($company->user && in_array($normalizedStatus, ['active', 'rejected'], true)) {
            $company->user->update(['status' => $normalizedStatus]);
        }

        return response()->json([
            'status' => 'success',
            'message' => __('messages.verification.marked_as', ['status' => $normalizedStatus]),
            'data' => [
                'company_id' => $company->id,
                'status_mitra' => $company->status_mitra,
            ],
        ]);
    }

    /**
     * 3. DETAIL DOKUMEN LEGALITAS
     */
    public function show($id)
    {
        $company = $this->resolveCompanyProfile($id, true);

        return response()->json([
            'status' => 'success',
            'data' => [
                'perusahaan' => [
                    'id' => $company->id,
                    'user_id' => $company->user_id,
                    'id_perusahaan' => 'CMP-' . str_pad($company->id, 3, '0', STR_PAD_LEFT),
                    'nama_perusahaan' => $company->nama_perusahaan,
                    'industri' => $company->industri,
                    'notelp' => $company->notelp,
                    'alamat_kantor_pusat' => $company->alamat_kantor_pusat,
                    'nib' => $company->nib,
                    'status_mitra' => $company->status_mitra,
                    'email' => $company->user?->email,
                    'loa_pdf' => $company->loa_pdf,
                    'akta_pdf' => $company->akta_pdf,
                    'loa_url' => $this->buildDocumentUrl($company->loa_pdf),
                    'akta_url' => $this->buildDocumentUrl($company->akta_pdf),
                    'created_at' => optional($company->created_at)->format('d M Y'),
                ],
                'dokumen' => [
                    [
                        'id' => 1,
                        'nama' => 'Nomor Induk Berusaha (NIB)',
                        'jenis' => 'text',
                        'value' => $company->nib,
                        'file' => null,
                        'path' => null,
                        'preview_url' => null,
                    ],
                    [
                        'id' => 2,
                        'nama' => 'Letter of Acceptance (LoA)',
                        'jenis' => 'file',
                        'value' => basename((string) $company->loa_pdf),
                        'file' => $this->buildDocumentUrl($company->loa_pdf),
                        'path' => $company->loa_pdf,
                        'preview_url' => $this->buildDocumentUrl($company->loa_pdf),
                    ],
                    [
                        'id' => 3,
                        'nama' => 'Akta Pendirian Perusahaan',
                        'jenis' => 'file',
                        'value' => basename((string) $company->akta_pdf),
                        'file' => $this->buildDocumentUrl($company->akta_pdf),
                        'path' => $company->akta_pdf,
                        'preview_url' => $this->buildDocumentUrl($company->akta_pdf),
                    ],
                ],
            ]
        ]);
    }

    /**
     * 4. SETUJUI ATAU TOLAK FINAL (HANYA SUPER ADMIN)
     */
    public function finalVerification(Request $request, $id)
    {
        // Proteksi tambahan di level Code
        if (Auth::user()->role !== 'super_admin') {
            return response()->json(['message' => __('messages.verification.super_admin_only')], 403);
        }

        $company = $this->resolveCompanyProfile($id, true);
        $normalizedStatus = $this->normalizeVerificationInput(
            $request->input('action', $request->input('status'))
        );

        if (!in_array($normalizedStatus, ['active', 'rejected'], true)) {
            return response()->json([
                'message' => 'The selected action is invalid.',
                'allowed_action' => ['approve', 'reject'],
            ], 422);
        }

        if ($normalizedStatus === 'active') {
            DB::transaction(function () use ($company) {
                $company->update(['status_mitra' => 'active']);

                if ($company->user) {
                    $company->user->update(['status' => 'active']);
                }
            });

            return response()->json(['status' => 'success', 'message' => __('messages.verification.company_approved')]);
        }

        // Jika Reject
        DB::transaction(function () use ($company) {
            $company->update(['status_mitra' => 'rejected']);

            if ($company->user) {
                $company->user->update(['status' => 'rejected']);
            }
        });

        return response()->json(['status' => 'success', 'message' => __('messages.verification.company_rejected')]);
    }

    private function resolveCompanyProfile($identifier, bool $withUser = false): CompanyProfile
    {
        $query = CompanyProfile::query();

        if ($withUser) {
            $query->with('user');
        }

        return $query
            ->where('id', $identifier)
            ->orWhere('user_id', $identifier)
            ->firstOrFail();
    }

    private function buildDocumentUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $normalizedPath = ltrim($path, '/');

        if (!Storage::disk('public')->exists($normalizedPath)) {
            return null;
        }

        return asset('storage/' . $normalizedPath);
    }

    private function normalizeVerificationInput(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        return match (strtolower(trim($value))) {
            'pending' => 'pending',
            'reviewed', 'review' => 'reviewed',
            'approve', 'approved', 'active' => 'active',
            'reject', 'rejected' => 'rejected',
            default => null,
        };
    }
}
