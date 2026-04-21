<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InternCertification;
use App\Models\InternExperience;
use App\Models\TestAnswer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminTalentController extends Controller
{
    public function index(Request $request)
    {
        $totalTalents = User::where('role', 'intern')->count();
        $activeTalents = User::where('role', 'intern')
            ->whereHas('applications', function ($q) {
                $q->whereIn('status', ['ACCEPTED', 'OFFER']);
            })->count();

        $newTalentsMonth = User::where('role', 'intern')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $query = User::where('role', 'intern')->with(['internProfile', 'applications.lowongan.companyProfile']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                    ->orWhereHas('internProfile', function ($sq) use ($search) {
                        $sq->where('universitas', 'like', "%$search%")
                            ->orWhere('jurusan', 'like', "%$search%");
                    });
            });
        }

        $talents = $query->latest()->paginate(10);

        return response()->json([
            'status' => 'success',
            'stats' => [
                'total_talenta' => [
                    'value' => number_format($totalTalents),
                    'growth' => '+12.5%',
                ],
                'talenta_aktif' => [
                    'value' => number_format($activeTalents),
                    'growth' => '+5.2%',
                ],
                'talenta_baru' => [
                    'value' => number_format($newTalentsMonth),
                    'growth' => '-2.1%',
                ],
            ],
            'data' => $talents->getCollection()->map(fn ($user) => $this->transformTalent($user)),
            'pagination' => [
                'total' => $talents->total(),
                'current_page' => $talents->currentPage(),
                'last_page' => $talents->lastPage(),
            ],
        ]);
    }

    public function show($id)
    {
        $user = User::where('role', 'intern')
            ->with(['internProfile', 'applications.lowongan.companyProfile'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $this->transformTalent($user),
        ]);
    }

    public function downloadCv($id)
    {
        $user = User::where('role', 'intern')
            ->with('internProfile')
            ->findOrFail($id);

        $cvPath = $user->internProfile?->cv_pdf;

        if (!$cvPath || !Storage::disk('public')->exists($cvPath)) {
            return response()->json([
                'status' => 'error',
                'message' => 'CV belum tersedia.',
            ], 404);
        }

        return response()->download(
            Storage::disk('public')->path($cvPath),
            'cv-'.$user->user_id.'.pdf'
        );
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return response()->json(['message' => 'Talenta berhasil dihapus dari sistem']);
    }

    private function transformTalent(User $user): array
    {
        $profile = $user->internProfile;
        $latestApplication = $user->applications->sortByDesc('created_at')->first();
        $latestJob = $latestApplication?->lowongan;
        $latestCompany = $latestJob?->companyProfile;
        $registeredAt = $user->created_at
            ?? $profile?->created_at
            ?? $latestApplication?->created_at
            ?? now();
        $answers = TestAnswer::where('user_id', $user->user_id)
            ->orderBy('id')
            ->get(['id', 'question_text', 'user_answer'])
            ->map(fn ($answer, $index) => [
                'id' => $answer->id,
                'nomor' => $index + 1,
                'question' => $answer->question_text,
                'question_text' => $answer->question_text,
                'answer' => $answer->user_answer,
                'user_answer' => $answer->user_answer,
            ])
            ->values();
        $experiences = InternExperience::where('user_id', $user->user_id)
            ->orderByDesc('id')
            ->get()
            ->map(fn ($experience) => $this->transformExperienceItem($experience))
            ->values();
        $certifications = InternCertification::where('user_id', $user->user_id)
            ->orderByDesc('id')
            ->get()
            ->map(fn ($certification) => $this->transformCertificationItem($certification))
            ->values();
        $cvUrl = $this->assetFromPublicDisk($profile?->cv_pdf);
        $educationDocumentUrl = $this->assetFromPublicDisk($profile?->dokumen_pendidikan_pdf);
        $portfolioUrl = $this->assetFromPublicDisk($profile?->portofolio_pdf);
        $recommendationUrl = $this->assetFromPublicDisk($profile?->surat_rekomendasi_pdf);
        $ktpUrl = $this->assetFromPublicDisk($profile?->ktp_pdf);
        $transcriptUrl = $this->assetFromPublicDisk($profile?->transkrip_nilai_pdf);
        $cvDownloadUrl = $cvUrl ? url('/api/admin/talents/'.$user->user_id.'/download-cv') : null;
        $birthPlace = $profile?->tempat_lahir;
        $birthDate = optional($profile?->tanggal_lahir)->format('d M Y');
        $birthDisplay = trim(collect([$birthPlace, $birthDate])->filter()->implode(', '));

        return [
            'id' => $user->user_id,
            'user_id' => $user->user_id,
            'id_talenta' => 'TLA-'.str_pad($user->user_id, 3, '0', STR_PAD_LEFT),
            'nama' => $user->nama,
            'name' => $user->nama,
            'full_name' => $user->nama,
            'email' => $user->email,
            'email_address' => $user->email,
            'foto' => $this->assetFromPublicDisk($profile?->foto),
            'universitas' => $profile?->universitas ?? '-',
            'jurusan' => $profile?->jurusan ?? '-',
            'jenjang' => $profile?->jenjang ?? '-',
            'ipk' => $profile?->ipk,
            'tahun_masuk' => $profile?->tahun_masuk,
            'tahun_lulus' => $profile?->tahun_lulus,
            'tanggal_daftar' => optional($registeredAt)->format('d M Y, H:i') ?? 'N/A',
            'tanggal_daftar_label' => optional($registeredAt)->format('d M Y') ?? 'N/A',
            'registered_at' => optional($registeredAt)->toDateTimeString(),
            'registered_at_label' => optional($registeredAt)->format('d M Y') ?? 'N/A',
            'joined_at' => optional($registeredAt)->toDateTimeString(),
            'joined_at_label' => optional($registeredAt)->format('d M Y') ?? 'N/A',
            'created_at' => optional($registeredAt)->toDateTimeString(),
            'status' => $latestApplication?->status ?? 'PENDING',
            'cv_pdf' => $cvUrl,
            'cv_url' => $cvUrl,
            'cv_download_url' => $cvDownloadUrl,
            'dokumen_pendidikan_pdf' => $educationDocumentUrl,
            'education_document' => $educationDocumentUrl,
            'education_document_url' => $educationDocumentUrl,
            'portofolio_pdf' => $portfolioUrl,
            'portofolio_url' => $portfolioUrl,
            'skor_pretest' => $profile?->skor_pretest ?? 0,
            'test_started_at' => optional($profile?->test_started_at)->toDateTimeString(),
            'test_finished_at' => optional($profile?->test_finished_at)->toDateTimeString(),
            'is_profile_complete' => (bool) ($profile?->is_profile_complete ?? false),
            'pretest_answers_count' => $answers->count(),
            'pretest_answers' => $answers,
            'review_jawaban' => $answers,
            'nama_talenta' => [
                'nama' => $user->nama,
                'email' => $user->email,
                'foto' => $this->assetFromPublicDisk($profile?->foto),
            ],
            'personal' => [
                'name' => $user->nama,
                'role' => $profile?->jenjang ?? 'Talent',
                'biodata' => $profile?->tentang_saya ?? '-',
                'gender' => $profile?->jenis_kelamin ?? '-',
                'birth' => $birthDisplay !== '' ? $birthDisplay : '-',
                'email' => $user->email,
                'phone' => $profile?->notelp ?? $user->notelp,
                'address' => trim(collect([$profile?->detail_alamat, $profile?->kabupaten, $profile?->provinsi])->filter()->implode(', ')) ?: '-',
                'socials' => [
                    'linkedin' => $profile?->linkedin,
                    'instagram' => $profile?->instagram,
                ],
            ],
            'academic' => [
                'education' => [
                    'universitas' => $profile?->universitas ?? '-',
                    'jurusan' => $profile?->jurusan ?? '-',
                    'jenjang' => $profile?->jenjang ?? '-',
                    'ipk' => $profile?->ipk,
                    'tahun_masuk' => $profile?->tahun_masuk,
                    'tahun_lulus' => $profile?->tahun_lulus,
                    'document' => $educationDocumentUrl,
                    'file' => $educationDocumentUrl,
                    'document_url' => $educationDocumentUrl,
                    'file_url' => $educationDocumentUrl,
                    'preview_url' => $educationDocumentUrl,
                    'supporting_document_url' => $educationDocumentUrl,
                ],
                'university' => $profile?->universitas ?? '-',
                'major' => $profile?->jurusan ?? '-',
                'degree' => $profile?->jenjang ?? '-',
                'ipk' => $profile?->ipk,
                'graduation' => $profile?->tahun_lulus ?? '-',
                'entry_year' => $profile?->tahun_masuk ?? '-',
                'document' => $educationDocumentUrl,
                'file' => $educationDocumentUrl,
                'document_url' => $educationDocumentUrl,
                'file_url' => $educationDocumentUrl,
                'preview_url' => $educationDocumentUrl,
                'supporting_document_url' => $educationDocumentUrl,
                'experiences' => $experiences,
                'experience' => $experiences,
                'certifications' => $certifications,
                'pengalaman' => $experiences,
                'sertifikasi' => $certifications,
            ],
            'assessment' => [
                'score' => $profile?->skor_pretest ?? 0,
                'summary' => $answers->isNotEmpty()
                    ? 'Jawaban pre-test tersedia untuk direview oleh admin.'
                    : 'Belum ada hasil assessment untuk ditampilkan.',
                'date' => optional($profile?->test_finished_at)->format('d M Y, H:i') ?? '-',
                'completed_at' => optional($profile?->test_finished_at)->toDateTimeString(),
                'answers_count' => $answers->count(),
                'answers' => $answers,
            ],
            'hasil_online_assessment' => [
                'score' => $profile?->skor_pretest ?? 0,
                'answers' => $answers,
                'has_result' => $answers->isNotEmpty(),
            ],
            'documents' => [
                'cv' => $cvUrl,
                'cv_url' => $cvUrl,
                'cv_download_url' => $cvDownloadUrl,
                'portfolio' => $portfolioUrl,
                'portfolio_url' => $portfolioUrl,
                'ktp' => $ktpUrl,
                'ktp_url' => $ktpUrl,
                'recommendation_letter' => $recommendationUrl,
                'recommendation_letter_url' => $recommendationUrl,
                'transcript' => $transcriptUrl,
                'transcript_url' => $transcriptUrl,
            ],
            'profile' => [
                'foto' => $this->assetFromPublicDisk($profile?->foto),
                'tentang_saya' => $profile?->tentang_saya,
                'jenis_kelamin' => $profile?->jenis_kelamin,
                'tempat_lahir' => $profile?->tempat_lahir,
                'tanggal_lahir' => optional($profile?->tanggal_lahir)->format('Y-m-d'),
                'notelp' => $profile?->notelp ?? $user->notelp,
                'provinsi' => $profile?->provinsi,
                'kabupaten' => $profile?->kabupaten,
                'detail_alamat' => $profile?->detail_alamat,
                'universitas' => $profile?->universitas,
                'jurusan' => $profile?->jurusan,
                'jenjang' => $profile?->jenjang,
                'ipk' => $profile?->ipk,
                'tahun_masuk' => $profile?->tahun_masuk,
                'tahun_lulus' => $profile?->tahun_lulus,
                'linkedin' => $profile?->linkedin,
                'instagram' => $profile?->instagram,
                'cv_pdf' => $cvUrl,
                'cv_download_url' => $cvDownloadUrl,
                'dokumen_pendidikan_pdf' => $educationDocumentUrl,
                'education_document' => $educationDocumentUrl,
                'education_document_url' => $educationDocumentUrl,
                'portofolio_pdf' => $portfolioUrl,
                'surat_rekomendasi_pdf' => $recommendationUrl,
                'ktp_pdf' => $ktpUrl,
                'transkrip_nilai_pdf' => $transcriptUrl,
                'skor_pretest' => $profile?->skor_pretest ?? 0,
                'test_started_at' => optional($profile?->test_started_at)->toDateTimeString(),
                'test_finished_at' => optional($profile?->test_finished_at)->toDateTimeString(),
                'is_profile_complete' => (bool) ($profile?->is_profile_complete ?? false),
            ],
            'latest_application' => $latestApplication ? [
                'application_id' => $latestApplication->application_id,
                'job_id' => $latestApplication->job_id,
                'job_title' => $latestJob?->judul_posisi ?? $latestJob?->judul_pekerjaan,
                'company_name' => $latestCompany?->nama_perusahaan,
                'nama_perusahaan' => $latestCompany?->nama_perusahaan,
                'status' => $latestApplication->status,
                'applied_at' => optional($latestApplication->created_at)->format('d M Y, H:i'),
            ] : null,
            'company_name' => $latestCompany?->nama_perusahaan,
            'nama_perusahaan' => $latestCompany?->nama_perusahaan,
        ];
    }

    private function assetFromPublicDisk(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    private function transformDocumentItem(array $item): array
    {
        $documentUrl = $this->assetFromPublicDisk($item['document_path'] ?? null);

        return array_merge($item, [
            'document' => $documentUrl,
            'file' => $documentUrl,
            'document_url' => $documentUrl,
            'file_url' => $documentUrl,
            'preview_url' => $documentUrl,
            'supporting_document_url' => $documentUrl,
        ]);
    }

    private function transformExperienceItem(InternExperience $experience): array
    {
        return $this->transformDocumentItem([
            'id' => $experience->id,
            'title' => $experience->title,
            'type' => $experience->type,
            'company' => $experience->company,
            'start_date' => $this->formatDateOutput($experience->start_date),
            'end_date' => $this->formatDateOutput($experience->end_date),
            'period' => $experience->period,
            'document_path' => $experience->document_path,
        ]);
    }

    private function transformCertificationItem(InternCertification $certification): array
    {
        return $this->transformDocumentItem([
            'id' => $certification->id,
            'name' => $certification->name,
            'issuer' => $certification->issuer,
            'issue_date' => $this->formatDateOutput($certification->issue_date),
            'certificate_number' => $certification->certificate_number,
            'description' => $certification->description,
            'document_path' => $certification->document_path,
        ]);
    }

    private function formatDateOutput(mixed $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return is_string($value) ? $value : null;
        }
    }
}
