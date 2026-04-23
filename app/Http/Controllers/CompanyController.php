<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lowongan;
use App\Models\JobApplication;
use App\Models\CompanyProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    private function assetFromPublicDisk(?string $path): ?string
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

    private function normalizeJobPayload(Request $request): array
    {
        $validated = $request->validate([
            'judul_posisi' => 'required|string|max:255',
            'deskripsi_pekerjaan' => 'required|string',
            'persyaratan' => 'required|string',
            'lokasi' => 'required|string',
            'tipe_magang' => 'required|in:remote,onsite,hybrid',
            'gaji_per_bulan' => 'nullable|string',
            'tanggal_penutupan_lamaran' => 'nullable|date',
            'tanggal_mulai_kerja' => 'nullable|date',
            'tgl_tutup_lamaran' => 'nullable|date',
            'tgl_mulai_kerja' => 'nullable|date',
            'status' => 'required|in:ACTIVE,OPEN,CLOSED,DRAFT',
        ]);

        $validated['tanggal_penutupan_lamaran'] = $validated['tanggal_penutupan_lamaran']
            ?? $validated['tgl_tutup_lamaran']
            ?? null;
        $validated['tanggal_mulai_kerja'] = $validated['tanggal_mulai_kerja']
            ?? $validated['tgl_mulai_kerja']
            ?? null;

        unset($validated['tgl_tutup_lamaran'], $validated['tgl_mulai_kerja']);

        if (($validated['status'] ?? null) === 'OPEN') {
            $validated['status'] = 'ACTIVE';
        }

        return $validated;
    }

    /**
     * Helper: Ambil profil company yang sedang login
     */
    private function getMyCompany()
    {
        return CompanyProfile::where('user_id', Auth::id())->first();
    }

    // ==========================================
    // 1. FITUR PROFIL (Sesuai UI Settings)
    // ==========================================

    public function getCompanyProfile()
    {
        $profile = $this->getMyCompany();
        if (!$profile) return response()->json(['message' => 'Profil tidak ditemukan'], 404);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id'                  => $profile->id,
                'nama_perusahaan'     => $profile->nama_perusahaan,
                'industri'            => $profile->industri,
                'ukuran_perusahaan'   => $profile->ukuran_perusahaan,
                'website_url'         => $profile->website_url,
                'deskripsi'           => $profile->deskripsi,
                'notelp'              => $profile->notelp,
                'alamat_kantor_pusat' => $profile->alamat_kantor_pusat,
                'nib'                 => $profile->nib,
                'status_mitra'        => $profile->status_mitra,
                'logo_url'            => $this->assetFromPublicDisk($profile->logo_perusahaan),
                'banner_url'          => $this->assetFromPublicDisk($profile->banner_perusahaan),
                'linkedin_url'        => $profile->linkedin_url,
                'instagram_url'       => $profile->instagram_url,
                'twitter_url'         => $profile->twitter_url,
                'loa_url'             => $this->assetFromPublicDisk($profile->loa_pdf),
                'akta_url'            => $this->assetFromPublicDisk($profile->akta_pdf),
                'created_at'          => $profile->created_at->format('d M Y')
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $company = $this->getMyCompany();
        if (!$company) return response()->json(['message' => 'Unauthorized'], 403);
        
        $validated = $request->validate([
            'nama_perusahaan'     => 'required|string|max:255',
            'industri'            => 'nullable|string',
            'ukuran_perusahaan'   => 'nullable|string',
            'website_url'         => 'nullable|url',
            'deskripsi'           => 'nullable|string',
            'notelp'              => 'nullable|string',
            'alamat_kantor_pusat' => 'nullable|string',
            'linkedin_url'        => 'nullable|url',
            'instagram_url'       => 'nullable|url',
            'twitter_url'         => 'nullable|url',
            'logo'                => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'banner'              => 'nullable|image|mimes:jpg,jpeg,png|max:3072',
        ]);

        if ($request->hasFile('logo')) {
            if ($company->logo_perusahaan) Storage::disk('public')->delete($company->logo_perusahaan);
            $validated['logo_perusahaan'] = $request->file('logo')->store('company/logos', 'public');
        }

        if ($request->hasFile('banner')) {
            if ($company->banner_perusahaan) Storage::disk('public')->delete($company->banner_perusahaan);
            $validated['banner_perusahaan'] = $request->file('banner')->store('company/banners', 'public');
        }

        $company->update($validated);

        return response()->json(['status' => 'success', 'message' => 'Profil berhasil diperbarui!', 'data' => $company]);
    }

    // ==========================================
    // 2. FITUR DASHBOARD & STATS
    // ==========================================

    public function getDashboardData()
    {
        $company = $this->getMyCompany();
        if (!$company) return response()->json(['message' => 'Unauthorized'], 403);

        $jobIds = Lowongan::where('company_profile_id', $company->id)->pluck('id');

        $stats = [
            'total_applicants' => JobApplication::whereIn('job_id', $jobIds)->count(),
            'active_jobs'      => Lowongan::where('company_profile_id', $company->id)->where('status', 'ACTIVE')->count(),
            'shortlisted'      => JobApplication::whereIn('job_id', $jobIds)->where('status', 'SHORTLISTED')->count(),
        ];

        $recentApplicants = JobApplication::with(['user', 'lowongan'])
            ->whereIn('job_id', $jobIds)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($app) => [
                'application_id' => $app->application_id,
                'name'           => $app->user->nama ?? 'N/A',
                'position'       => $app->lowongan->judul_posisi ?? 'N/A',
                'date'           => $app->created_at->format('d M Y'),
                'status'         => $app->status
            ]);

        return response()->json(['status' => 'success', 'stats' => $stats, 'recent_applicants' => $recentApplicants]);
    }

    // ==========================================
    // 3. FITUR MANAJEMEN PELAMAR
    // ==========================================

    public function getApplicantsByJob($jobId)
    {
        $company = $this->getMyCompany();
        $job = Lowongan::where('id', $jobId)->where('company_profile_id', $company->id)->firstOrFail();
        
        $applicants = JobApplication::with(['user.internProfile'])
            ->where('job_id', $jobId)
            ->latest()
            ->get();

        return response()->json(['status' => 'success', 'job' => $job->judul_posisi, 'applicants' => $applicants]);
    }

    public function updateApplicationStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:PENDING,REVIEW,INTERVIEW,SHORTLISTED,ACCEPTED,REJECTED']);
        $app = JobApplication::findOrFail($id);
        
        $app->update(['status' => $request->status]);
        
        return response()->json(['status' => 'success', 'message' => 'Status pelamar diperbarui!']);
    }

    // ==========================================
    // 4. FITUR LOWONGAN (CRUD)
    // ==========================================

    public function getJobPostings()
    {
        $company = $this->getMyCompany();
        $jobs = Lowongan::where('company_profile_id', $company->id)->latest()->get();
        
        return response()->json(['status' => 'success', 'jobs' => $jobs]);
    }

    public function storeJob(Request $request)
    {
        $company = $this->getMyCompany();
        
        // Proteksi: Cek status mitra
        if (!$company || $company->status_mitra !== 'active') {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun Anda belum aktif atau belum diverifikasi oleh Admin.'
            ], 403);
        }
        
        $validated = $this->normalizeJobPayload($request);

        $job = Lowongan::create(array_merge($validated, ['company_profile_id' => $company->id]));

        return response()->json([
            'status' => 'success', 
            'message' => 'Lowongan berhasil dipublikasikan!',
            'data' => $job
        ], 201);
    }

    public function updateJob(Request $request, $id)
    {
        $company = $this->getMyCompany();
        $job = Lowongan::where('id', $id)->where('company_profile_id', $company->id)->firstOrFail();

        $job->update($this->normalizeJobPayload($request));
        
        return response()->json(['status' => 'success', 'message' => 'Lowongan berhasil diupdate!']);
    }

    public function destroyJob($id)
    {
        $company = $this->getMyCompany();
        $job = Lowongan::where('id', $id)->where('company_profile_id', $company->id)->first();

        if (!$job) return response()->json(['message' => 'Lowongan tidak ditemukan'], 404);

        $job->delete();
        return response()->json(['status' => 'success', 'message' => 'Lowongan berhasil dihapus']);
    }

    
    public function getPublicStats()
    {
        $activeJobsQuery = Lowongan::query()
            ->where('status', 'ACTIVE')
            ->whereHas('companyProfile', function ($query) {
                $query->where('status_mitra', 'active');
            });

        return response()->json(['status' => 'success', 'data' => [
            'live_jobs'  => (clone $activeJobsQuery)->count(),
            'companies'  => CompanyProfile::where('status_mitra', 'active')->count(),
            'candidates' => User::where('role', 'intern')->count(),
            'new_jobs'   => (clone $activeJobsQuery)
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
        ]]);
    }

    public function getPublicJobs()
    {
        
        $jobs = Lowongan::with('companyProfile')
            ->where('status', 'ACTIVE')
            ->whereHas('companyProfile', function ($query) {
                $query->where('status_mitra', 'active');
            })
            ->latest()
            ->get();

        return response()->json(['status' => 'success', 'data' => $jobs]);
    }

    public function getPublicPartners(Request $request)
    {
        $query = CompanyProfile::query()
            ->where('status_mitra', 'active')
            ->withCount([
                'lowongans as active_jobs_count' => fn ($q) => $q->whereIn('status', ['ACTIVE', 'OPEN']),
            ])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->string('search')->value();

            $query->where(function ($q) use ($search) {
                $q->where('nama_perusahaan', 'like', "%{$search}%")
                    ->orWhere('industri', 'like', "%{$search}%")
                    ->orWhere('alamat_kantor_pusat', 'like', "%{$search}%");
            });
        }

        $partners = $query->paginate((int) $request->integer('per_page', 12));

        return response()->json([
            'status' => 'success',
            'summary' => [
                'total_partners' => $partners->total(),
                'current_page' => $partners->currentPage(),
                'per_page' => $partners->perPage(),
            ],
            'data' => $partners->getCollection()->map(fn ($company) => [
                'id' => $company->id,
                'company_id' => $company->id,
                'nama_perusahaan' => $company->nama_perusahaan,
                'name' => $company->nama_perusahaan,
                'company_name' => $company->nama_perusahaan,
                'industri' => $company->industri,
                'industry' => $company->industri,
                'ukuran_perusahaan' => $company->ukuran_perusahaan,
                'company_size' => $company->ukuran_perusahaan,
                'deskripsi' => $company->deskripsi,
                'description' => $company->deskripsi,
                'notelp' => $company->notelp,
                'alamat_kantor_pusat' => $company->alamat_kantor_pusat,
                'location' => $company->alamat_kantor_pusat,
                'website_url' => $company->website_url,
                'website' => $company->website_url,
                'logo_url' => $this->assetFromPublicDisk($company->logo_perusahaan),
                'logo' => $this->assetFromPublicDisk($company->logo_perusahaan),
                'banner_url' => $this->assetFromPublicDisk($company->banner_perusahaan),
                'banner' => $this->assetFromPublicDisk($company->banner_perusahaan),
                'linkedin_url' => $company->linkedin_url,
                'instagram_url' => $company->instagram_url,
                'twitter_url' => $company->twitter_url,
                'active_jobs_count' => $company->active_jobs_count ?? 0,
                'open_positions' => $company->active_jobs_count ?? 0,
                'created_at' => optional($company->created_at)->format('d M Y') ?? 'N/A',
                'display' => [
                    'title' => $company->nama_perusahaan,
                    'subtitle' => $company->industri ?: ($company->alamat_kantor_pusat ?: 'Partner Vocaseek'),
                    'image' => $this->assetFromPublicDisk($company->logo_perusahaan),
                    'meta' => [
                        'location' => $company->alamat_kantor_pusat,
                        'website' => $company->website_url,
                        'active_jobs_count' => $company->active_jobs_count ?? 0,
                    ],
                ],
            ]),
            'pagination' => [
                'total' => $partners->total(),
                'current_page' => $partners->currentPage(),
                'last_page' => $partners->lastPage(),
                'per_page' => $partners->perPage(),
            ],
        ]);
    }
}
