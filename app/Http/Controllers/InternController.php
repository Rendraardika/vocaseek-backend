<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\InternProfile;
use App\Models\JobApplication;
use App\Models\TestAnswer;
use App\Models\InternExperience;
use App\Models\InternCertification;
use App\Models\Lowongan; // Pastikan Abang buat model untuk tabel lowongan
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InternController extends Controller
{
    private function normalizeApplicationStatus(?string $status): string
    {
        return match ($status) {
            'HIRED', 'ACCEPTED', 'OFFER', 'SHORTLISTED' => 'HIRED',
            'REJECTED', 'DECLINED' => 'REJECTED',
            default => 'PENDING',
        };
    }

    private function internStatusLabel(?string $status): string
    {
        return match ($this->normalizeApplicationStatus($status)) {
            'HIRED' => 'Diterima',
            'REJECTED' => 'Ditolak',
            default => 'Pending',
        };
    }

    public function getTestQuestions()
    {
        $user = Auth::user();
        $profile = InternProfile::where('user_id', $user->user_id)->first();

        if (!$profile || (int) $profile->is_profile_complete === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lengkapi profil dulu sebelum mengakses pre-test.',
            ], 403);
        }

        if ($profile->test_finished_at) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pre-test hanya dapat dikerjakan satu kali.',
                'data' => [
                    'already_completed' => true,
                    'test_started_at' => $profile->test_started_at,
                    'test_finished_at' => $profile->test_finished_at,
                ],
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'questions' => $this->pretestQuestions(),
                'duration_minutes' => $this->pretestDurationMinutes(),
                'total_questions' => count($this->pretestQuestions()),
                'already_started' => (bool) $profile->test_started_at,
                'test_started_at' => $profile->test_started_at,
                'expires_at' => $this->expiresAt($profile),
            ],
        ]);
    }

    /**
     * Ambil Data Profil Lengkap
     */
    public function getProfile()
    {
        $user = Auth::user();
        $profile = InternProfile::where('user_id', $user->user_id)->first();
        
        if (!$profile) return response()->json(['message' => 'Profil tidak ditemukan'], 404);

        $experiences = InternExperience::where('user_id', $user->user_id)
            ->get()
            ->map(fn ($experience) => $this->transformExperienceItem($experience))
            ->values();
        $certifications = InternCertification::where('user_id', $user->user_id)
            ->get()
            ->map(fn ($certification) => $this->transformCertificationItem($certification))
            ->values();
        $photoUrl = $this->documentUrl($profile->foto);
        $cvUrl = $this->documentUrl($profile->cv_pdf);
        $educationDocumentUrl = $this->documentUrl($profile->dokumen_pendidikan_pdf);
        $portfolioUrl = $this->documentUrl($profile->portofolio_pdf);
        $recommendationUrl = $this->documentUrl($profile->surat_rekomendasi_pdf);
        $ktpUrl = $this->documentUrl($profile->ktp_pdf);
        $transcriptUrl = $this->documentUrl($profile->transkrip_nilai_pdf);
        $birthDisplay = trim(collect([
            $profile->tempat_lahir,
            optional($profile->tanggal_lahir)->format('d M Y'),
        ])->filter()->implode(', '));
        $addressDisplay = trim(collect([
            $profile->detail_alamat,
            $profile->kabupaten,
            $profile->provinsi,
        ])->filter()->implode(', '));

        return response()->json([
            'status' => 'success',
            'data' => [
                'nama' => $user->nama, 
                'email' => $user->email,
                'universitas' => $profile->universitas,
                'jurusan' => $profile->jurusan,
                'jenjang' => $profile->jenjang,
                'ipk' => $profile->ipk,
                'tahun_masuk' => $profile->tahun_masuk,
                'tahun_lulus' => $profile->tahun_lulus,
                'provinsi' => $profile->provinsi,
                'kabupaten' => $profile->kabupaten,
                'foto' => $photoUrl,
                'photo' => $photoUrl,
                'cv' => $cvUrl,
                'cv_pdf' => $cvUrl,
                'cv_url' => $cvUrl,
                'dokumen_pendidikan_pdf' => $educationDocumentUrl,
                'education_document' => $educationDocumentUrl,
                'education_document_url' => $educationDocumentUrl,
                'portofolio_pdf' => $portfolioUrl,
                'portofolio_url' => $portfolioUrl,
                'portfolio' => $portfolioUrl,
                'portfolio_url' => $portfolioUrl,
                'surat_rekomendasi_pdf' => $recommendationUrl,
                'recommendation_letter' => $recommendationUrl,
                'recommendation_letter_url' => $recommendationUrl,
                'ktp_pdf' => $ktpUrl,
                'ktp' => $ktpUrl,
                'ktp_url' => $ktpUrl,
                'transkrip_nilai_pdf' => $transcriptUrl,
                'transcript' => $transcriptUrl,
                'transcript_url' => $transcriptUrl,
                'tentang_saya' => $profile->tentang_saya,
                'tempat_lahir' => $profile->tempat_lahir,
                'tanggal_lahir' => optional($profile->tanggal_lahir)->format('Y-m-d'),
                'jenis_kelamin' => $profile->jenis_kelamin,
                'detail_alamat' => $profile->detail_alamat,
                'linkedin' => $profile->linkedin,
                'instagram' => $profile->instagram,
                'notelp' => $profile->notelp ?? $user->notelp,
                'is_complete' => (int) $profile->is_profile_complete,
                'is_profile_complete' => (bool) $profile->is_profile_complete,
                'pengalaman' => $experiences,
                'experiences' => $experiences,
                'experience' => $experiences,
                'sertifikasi' => $certifications,
                'certifications' => $certifications,
                'personal' => [
                    'name' => $user->nama,
                    'photo' => $photoUrl,
                    'foto' => $photoUrl,
                    'role' => $profile->jenjang ?? 'Talent',
                    'biodata' => $profile->tentang_saya ?? '-',
                    'gender' => $profile->jenis_kelamin ?? '-',
                    'birth' => $birthDisplay !== '' ? $birthDisplay : '-',
                    'email' => $user->email,
                    'phone' => $profile->notelp ?? $user->notelp,
                    'address' => $addressDisplay !== '' ? $addressDisplay : '-',
                    'socials' => [
                        'linkedin' => $profile->linkedin,
                        'instagram' => $profile->instagram,
                    ],
                ],
                'academic' => [
                    'education' => [
                        'universitas' => $profile->universitas,
                        'jurusan' => $profile->jurusan,
                        'jenjang' => $profile->jenjang,
                        'ipk' => $profile->ipk,
                        'tahun_masuk' => $profile->tahun_masuk,
                        'tahun_lulus' => $profile->tahun_lulus,
                        'document' => $educationDocumentUrl,
                        'file' => $educationDocumentUrl,
                        'document_url' => $educationDocumentUrl,
                        'file_url' => $educationDocumentUrl,
                        'preview_url' => $educationDocumentUrl,
                        'supporting_document_url' => $educationDocumentUrl,
                    ],
                    'university' => $profile->universitas,
                    'major' => $profile->jurusan,
                    'degree' => $profile->jenjang,
                    'ipk' => $profile->ipk,
                    'graduation' => $profile->tahun_lulus,
                    'entry_year' => $profile->tahun_masuk,
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
                'documents' => [
                    'cv' => $cvUrl,
                    'cv_url' => $cvUrl,
                    'portfolio' => $portfolioUrl,
                    'portfolio_url' => $portfolioUrl,
                    'ktp' => $ktpUrl,
                    'ktp_url' => $ktpUrl,
                    'recommendation_letter' => $recommendationUrl,
                    'recommendation_letter_url' => $recommendationUrl,
                    'transcript' => $transcriptUrl,
                    'transcript_url' => $transcriptUrl,
                    'education_document' => $educationDocumentUrl,
                    'education_document_url' => $educationDocumentUrl,
                ],
                'profile' => [
                    'foto' => $photoUrl,
                    'tentang_saya' => $profile->tentang_saya,
                    'tempat_lahir' => $profile->tempat_lahir,
                    'tanggal_lahir' => optional($profile->tanggal_lahir)->format('Y-m-d'),
                    'jenis_kelamin' => $profile->jenis_kelamin,
                    'notelp' => $profile->notelp ?? $user->notelp,
                    'provinsi' => $profile->provinsi,
                    'kabupaten' => $profile->kabupaten,
                    'detail_alamat' => $profile->detail_alamat,
                    'universitas' => $profile->universitas,
                    'jurusan' => $profile->jurusan,
                    'jenjang' => $profile->jenjang,
                    'ipk' => $profile->ipk,
                    'tahun_masuk' => $profile->tahun_masuk,
                    'tahun_lulus' => $profile->tahun_lulus,
                    'linkedin' => $profile->linkedin,
                    'instagram' => $profile->instagram,
                    'cv_pdf' => $cvUrl,
                    'dokumen_pendidikan_pdf' => $educationDocumentUrl,
                    'education_document' => $educationDocumentUrl,
                    'education_document_url' => $educationDocumentUrl,
                    'portofolio_pdf' => $portfolioUrl,
                    'surat_rekomendasi_pdf' => $recommendationUrl,
                    'ktp_pdf' => $ktpUrl,
                    'transkrip_nilai_pdf' => $transcriptUrl,
                    'is_profile_complete' => (bool) $profile->is_profile_complete,
                ],
            ]
        ]);
    }

    /**
     * Update Profile Utama
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $profile = InternProfile::where('user_id', $user->user_id)->first();
        $pengalaman = $this->normalizeArrayInput($request->input('pengalaman', $request->input('experiences')));
        $sertifikasi = $this->normalizeArrayInput($request->input('sertifikasi', $request->input('certifications')));
        $pengalamanFiles = $this->mergeNestedFileInputs(
            $request->file('pengalaman'),
            $request->file('experiences'),
            $request->file('pengalaman_files'),
            $request->file('experience_files'),
            $request->file('experienceDocuments'),
            $request->file('pengalamanDocuments')
        );
        $sertifikasiFiles = $this->mergeNestedFileInputs(
            $request->file('sertifikasi'),
            $request->file('certifications'),
            $request->file('sertifikasi_files'),
            $request->file('certification_files'),
            $request->file('license_files'),
            $request->file('sertifikat_files')
        );

        $request->validate([
            'foto'           => 'nullable|image|max:2048',
            'cv_pdf'         => 'nullable|mimes:pdf|max:5120',
            'dokumen_pendidikan_pdf' => 'nullable|mimes:pdf|max:5120',
            'education_document' => 'nullable|mimes:pdf|max:5120',
            'portofolio_pdf' => 'nullable|mimes:pdf|max:5120',
            'surat_rekomendasi_pdf' => 'nullable|mimes:pdf|max:5120',
            'ktp_pdf' => 'nullable|mimes:pdf|max:5120',
            'transkrip_nilai_pdf' => 'nullable|mimes:pdf|max:5120',
            'ipk'            => 'nullable|numeric|between:0,4.00',
        ]);

        DB::transaction(function () use (
            $request,
            $profile,
            $user,
            $pengalaman,
            $sertifikasi,
            $pengalamanFiles,
            $sertifikasiFiles
        ) {
            // Handle foto
            if ($request->hasFile('foto')) {
                if ($profile->foto) Storage::disk('public')->delete($profile->foto);
                $profile->foto = $request->file('foto')->store('profiles/photos', 'public');
            } elseif ($request->has('foto_delete') || $request->filled('foto') && is_null($request->input('foto'))) {
                if ($profile->foto) Storage::disk('public')->delete($profile->foto);
                $profile->foto = null;
            }

            // Handle cv_pdf
            if ($request->hasFile('cv_pdf')) {
                if ($profile->cv_pdf) Storage::disk('public')->delete($profile->cv_pdf);
                $profile->cv_pdf = $request->file('cv_pdf')->store('profiles/documents', 'public');
            } elseif ($request->has('cv_pdf_delete') || $request->filled('cv_pdf') && is_null($request->input('cv_pdf'))) {
                if ($profile->cv_pdf) Storage::disk('public')->delete($profile->cv_pdf);
                $profile->cv_pdf = null;
            }

            // Handle dokumen_pendidikan_pdf / education_document
            $educationDocument = $request->file('dokumen_pendidikan_pdf') ?: $request->file('education_document');
            if ($educationDocument) {
                if ($profile->dokumen_pendidikan_pdf) {
                    Storage::disk('public')->delete($profile->dokumen_pendidikan_pdf);
                }
                $profile->dokumen_pendidikan_pdf = $educationDocument->store('profiles/documents', 'public');
            } elseif ($request->has('dokumen_pendidikan_pdf_delete') || $request->has('education_document_delete') || ($request->filled('dokumen_pendidikan_pdf') && is_null($request->input('dokumen_pendidikan_pdf')))) {
                if ($profile->dokumen_pendidikan_pdf) {
                    Storage::disk('public')->delete($profile->dokumen_pendidikan_pdf);
                }
                $profile->dokumen_pendidikan_pdf = null;
            }

            // Handle portofolio_pdf
            if ($request->hasFile('portofolio_pdf')) {
                if ($profile->portofolio_pdf) Storage::disk('public')->delete($profile->portofolio_pdf);
                $profile->portofolio_pdf = $request->file('portofolio_pdf')->store('profiles/documents', 'public');
            } elseif ($request->has('portofolio_pdf_delete') || ($request->filled('portofolio_pdf') && is_null($request->input('portofolio_pdf')))) {
                if ($profile->portofolio_pdf) Storage::disk('public')->delete($profile->portofolio_pdf);
                $profile->portofolio_pdf = null;
            }

            // Handle surat_rekomendasi_pdf
            if ($request->hasFile('surat_rekomendasi_pdf')) {
                if ($profile->surat_rekomendasi_pdf) Storage::disk('public')->delete($profile->surat_rekomendasi_pdf);
                $profile->surat_rekomendasi_pdf = $request->file('surat_rekomendasi_pdf')->store('profiles/documents', 'public');
            } elseif ($request->has('surat_rekomendasi_pdf_delete') || ($request->filled('surat_rekomendasi_pdf') && is_null($request->input('surat_rekomendasi_pdf')))) {
                if ($profile->surat_rekomendasi_pdf) Storage::disk('public')->delete($profile->surat_rekomendasi_pdf);
                $profile->surat_rekomendasi_pdf = null;
            }

            // Handle ktp_pdf
            if ($request->hasFile('ktp_pdf')) {
                if ($profile->ktp_pdf) Storage::disk('public')->delete($profile->ktp_pdf);
                $profile->ktp_pdf = $request->file('ktp_pdf')->store('profiles/documents', 'public');
            } elseif ($request->has('ktp_pdf_delete') || ($request->filled('ktp_pdf') && is_null($request->input('ktp_pdf')))) {
                if ($profile->ktp_pdf) Storage::disk('public')->delete($profile->ktp_pdf);
                $profile->ktp_pdf = null;
            }

            // Handle transkrip_nilai_pdf
            if ($request->hasFile('transkrip_nilai_pdf')) {
                if ($profile->transkrip_nilai_pdf) Storage::disk('public')->delete($profile->transkrip_nilai_pdf);
                $profile->transkrip_nilai_pdf = $request->file('transkrip_nilai_pdf')->store('profiles/documents', 'public');
            } elseif ($request->has('transkrip_nilai_pdf_delete') || ($request->filled('transkrip_nilai_pdf') && is_null($request->input('transkrip_nilai_pdf')))) {
                if ($profile->transkrip_nilai_pdf) Storage::disk('public')->delete($profile->transkrip_nilai_pdf);
                $profile->transkrip_nilai_pdf = null;
            }

            $profileData = $request->only([
                'tentang_saya', 'tempat_lahir', 'jenis_kelamin',
                'provinsi', 'kabupaten', 'detail_alamat', 'universitas', 'jurusan',
                'jenjang', 'ipk', 'tahun_masuk', 'tahun_lulus', 'linkedin', 'instagram', 'notelp'
            ]);
            $profileData['tanggal_lahir'] = $this->normalizeDateValue($request->input('tanggal_lahir'));
            $profile->fill($profileData);

            if ($profile->foto && $profile->cv_pdf && $profile->universitas) {
                $profile->is_profile_complete = 1;
            }

            $profile->save();

            if ($request->exists('pengalaman') || $request->exists('experiences')) {
                $existingExperiences = InternExperience::where('user_id', $user->user_id)
                    ->get()
                    ->keyBy('id');

                $retainedExperienceIds = [];

                foreach ($pengalaman as $index => $item) {
                    if (!is_array($item)) {
                        continue;
                    }

                    $experienceId = isset($item['id']) ? (int) $item['id'] : null;
                    $existingExperience = $experienceId
                        ? $existingExperiences->get($experienceId)
                        : null;

                    $title = trim((string) ($item['title'] ?? $item['jabatan'] ?? ''));
                    $type = trim((string) ($item['type'] ?? $item['jenis'] ?? ''));
                    $company = trim((string) ($item['company'] ?? $item['perusahaan'] ?? ''));
                    $startDate = $this->normalizeDateValue($item['start_date'] ?? $item['mulai'] ?? null);
                    $endDate = $this->normalizeDateValue($item['end_date'] ?? $item['akhir'] ?? null);
                    $period = $this->buildPeriod(
                        $item['period'] ?? $item['periode'] ?? null,
                        $startDate,
                        $endDate
                    );

                    $newDocumentPath = $this->storeNestedDocument(
                        $pengalamanFiles,
                        $index,
                        ['document', 'document_file', 'file', 'supporting_document', 'dokumen', 'upload', 'attachment', 'certificate', 'certificate_file']
                    );

                    $documentPath = $existingExperience?->document_path;

                    if ($newDocumentPath) {
                        if ($existingExperience?->document_path) {
                            Storage::disk('public')->delete($existingExperience->document_path);
                        }
                        $documentPath = $newDocumentPath;
                    }

                    if (
                        $title === '' &&
                        $type === '' &&
                        $company === '' &&
                        !$startDate &&
                        !$endDate &&
                        $period === '' &&
                        !$documentPath
                    ) {
                        continue;
                    }

                    if ($existingExperience) {
                        $existingExperience->update([
                            'title' => $title !== '' ? $title : '-',
                            'type' => $type !== '' ? $type : null,
                            'company' => $company !== '' ? $company : '-',
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'period' => $period !== '' ? $period : '-',
                            'document_path' => $documentPath,
                        ]);

                        $retainedExperienceIds[] = $existingExperience->id;
                    } else {
                        $createdExperience = InternExperience::create([
                            'user_id' => $user->user_id,
                            'title' => $title !== '' ? $title : '-',
                            'type' => $type !== '' ? $type : null,
                            'company' => $company !== '' ? $company : '-',
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'period' => $period !== '' ? $period : '-',
                            'document_path' => $documentPath,
                        ]);

                        $retainedExperienceIds[] = $createdExperience->id;
                    }
                }

                $experiencesToDelete = InternExperience::where('user_id', $user->user_id)
                    ->when(!empty($retainedExperienceIds), function ($query) use ($retainedExperienceIds) {
                        $query->whereNotIn('id', $retainedExperienceIds);
                    })
                    ->get();

                foreach ($experiencesToDelete as $experienceToDelete) {
                    if ($experienceToDelete->document_path) {
                        Storage::disk('public')->delete($experienceToDelete->document_path);
                    }
                    $experienceToDelete->delete();
                }
            }

            if ($request->exists('sertifikasi') || $request->exists('certifications')) {
                $existingCertifications = InternCertification::where('user_id', $user->user_id)
                    ->get()
                    ->keyBy('id');

                $retainedCertificationIds = [];

                foreach ($sertifikasi as $index => $item) {
                    if (!is_array($item) && !is_string($item)) {
                        continue;
                    }

                    $itemArray = is_array($item) ? $item : ['name' => $item];
                    $certificationId = isset($itemArray['id']) ? (int) $itemArray['id'] : null;
                    $existingCertification = $certificationId
                        ? $existingCertifications->get($certificationId)
                        : null;

                    $name = trim((string) ($itemArray['name'] ?? $itemArray['nama'] ?? ''));
                    $issuer = trim((string) ($itemArray['issuer'] ?? $itemArray['penerbit'] ?? ''));
                    $issueDate = $this->normalizeDateValue($itemArray['issue_date'] ?? $itemArray['tanggal'] ?? null);
                    $certificateNumber = trim((string) ($itemArray['certificate_number'] ?? $itemArray['nomor'] ?? ''));
                    $description = trim((string) ($itemArray['description'] ?? $itemArray['deskripsi'] ?? ''));

                    $newDocumentPath = $this->storeNestedDocument(
                        $sertifikasiFiles,
                        $index,
                        ['document', 'document_file', 'file', 'supporting_document', 'dokumen', 'upload', 'attachment', 'certificate_file']
                    );

                    $documentPath = $existingCertification?->document_path;

                    if ($newDocumentPath) {
                        if ($existingCertification?->document_path) {
                            Storage::disk('public')->delete($existingCertification->document_path);
                        }
                        $documentPath = $newDocumentPath;
                    }

                    if (
                        $name === '' &&
                        $issuer === '' &&
                        !$issueDate &&
                        $certificateNumber === '' &&
                        $description === '' &&
                        !$documentPath
                    ) {
                        continue;
                    }

                    if ($existingCertification) {
                        $existingCertification->update([
                            'name' => $name !== '' ? $name : 'Dokumen Pendukung',
                            'issuer' => $issuer !== '' ? $issuer : null,
                            'issue_date' => $issueDate,
                            'certificate_number' => $certificateNumber !== '' ? $certificateNumber : null,
                            'description' => $description !== '' ? $description : null,
                            'document_path' => $documentPath,
                        ]);

                        $retainedCertificationIds[] = $existingCertification->id;
                    } else {
                        $createdCertification = InternCertification::create([
                            'user_id' => $user->user_id,
                            'name' => $name !== '' ? $name : 'Dokumen Pendukung',
                            'issuer' => $issuer !== '' ? $issuer : null,
                            'issue_date' => $issueDate,
                            'certificate_number' => $certificateNumber !== '' ? $certificateNumber : null,
                            'description' => $description !== '' ? $description : null,
                            'document_path' => $documentPath,
                        ]);

                        $retainedCertificationIds[] = $createdCertification->id;
                    }
                }

                $certificationsToDelete = InternCertification::where('user_id', $user->user_id)
                    ->when(!empty($retainedCertificationIds), function ($query) use ($retainedCertificationIds) {
                        $query->whereNotIn('id', $retainedCertificationIds);
                    })
                    ->get();

                foreach ($certificationsToDelete as $certificationToDelete) {
                    if ($certificationToDelete->document_path) {
                        Storage::disk('public')->delete($certificationToDelete->document_path);
                    }
                    $certificationToDelete->delete();
                }
            }
        });

        return response()->json(['status' => 'success', 'message' => 'Profil diperbarui!']);
    }

    /**
     * Memulai Tes
     */
    public function startTest()
    {
        $user = Auth::user();
        $profile = InternProfile::where('user_id', $user->user_id)->first();

        if (!$profile || (int)$profile->is_profile_complete === 0) {
            return response()->json(['status' => 'error', 'message' => 'Lengkapi profil dulu!'], 403);
        }

        if ($profile->test_finished_at) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pre-test hanya dapat dikerjakan satu kali.',
            ], 403);
        }

        if (!$profile->test_started_at) {
            $profile->test_started_at = now();
            $profile->save();
        }

        return response()->json([
            'status' => 'success', 
            'message' => 'Test dimulai!',
            'test_started_at' => $profile->test_started_at,
            'expires_at' => $this->expiresAt($profile),
            'duration_minutes' => $this->pretestDurationMinutes(),
            'total_questions' => count($this->pretestQuestions()),
        ]);
    }

    /**
     * Submit Jawaban Tes
     */
    public function submitPreTest(Request $request)
    {
        $request->validate([
            'answers' => 'required|array',
        ]);

        $user = Auth::user();
        $profile = InternProfile::where('user_id', $user->user_id)->first();
        $questionsById = collect($this->pretestQuestions())->keyBy('id');

        if (!$profile || (int) $profile->is_profile_complete === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lengkapi profil dulu sebelum mengerjakan pre-test.',
            ], 403);
        }

        if ($profile->test_finished_at) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pre-test hanya dapat dikerjakan satu kali.',
            ], 403);
        }

        if (!$profile->test_started_at) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mulai pre-test terlebih dahulu.',
            ], 400);
        }

        if ($this->isTestExpired($profile)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Waktu pre-test sudah habis.',
                'expires_at' => $this->expiresAt($profile),
            ], 422);
        }

        if (count($request->answers) !== $questionsById->count()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jumlah jawaban tidak sesuai dengan jumlah soal.',
                'expected' => $questionsById->count(),
            ], 422);
        }

        $normalizedAnswers = collect($request->answers);
        $questionIds = $normalizedAnswers->pluck('question_id')->filter()->values();

        if ($questionIds->count() !== $questionsById->count() || $questionIds->unique()->count() !== $questionsById->count()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Setiap soal harus dijawab tepat satu kali.',
            ], 422);
        }

        if ($questionIds->sort()->values()->all() !== $questionsById->keys()->sort()->values()->all()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Daftar soal yang dikirim tidak valid.',
            ], 422);
        }

        foreach ($request->answers as $ans) {
            $question = $questionsById->get((int) ($ans['question_id'] ?? 0));
            $selectedOption = $ans['selected_option'] ?? null;

            if (!$question || !in_array($selectedOption, $question['options'], true)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jawaban pre-test tidak valid.',
                ], 422);
            }

            TestAnswer::create([
                'user_id' => $user->user_id,
                'question_text' => $question['question'],
                'user_answer' => $selectedOption,
            ]);
        }

        $profile->update(['test_finished_at' => now()]);

        return response()->json(['status' => 'success', 'message' => 'Tes berhasil dikirim!']);
    }

    /**
     * Melamar Kerja (Final Step)
     */
    public function applyJob(Request $request)
    {
        $request->validate([
            'job_id' => 'required|integer', // Mengacu ke ID di tabel lowongan
        ]);

        $user = Auth::user();
        $profile = InternProfile::where('user_id', $user->user_id)->first();

        // Validasi: Profil Lengkap & Sudah Test
        if (!$profile->is_profile_complete || !$profile->test_finished_at) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Selesaikan profil dan tes dulu sebelum melamar!'
            ], 403);
        }

        // Cek apakah sudah pernah melamar di posisi yang sama
        $exists = JobApplication::where('user_id', $user->user_id)
                                ->where('job_id', $request->job_id)
                                ->exists();
        
        if ($exists) {
            return response()->json(['message' => 'Anda sudah melamar di posisi ini.'], 400);
        }

        // Simpan Lamaran (Sesuai kolom di tabel job_applications Abang)
        JobApplication::create([
            'user_id' => $user->user_id,
            'job_id'  => $request->job_id,
            'status'  => 'PENDING' // Sesuai default ENUM di gambar DB
        ]);

        return response()->json(['status' => 'success', 'message' => 'Lamaran berhasil terkirim!']);
    }

    public function getMyApplications()
    {
        $user = Auth::user();

        $applications = JobApplication::with(['lowongan.companyProfile'])
            ->where('user_id', $user->user_id)
            ->latest()
            ->get();

        $data = $applications->map(function ($application) {
            $job = $application->lowongan;
            $company = $job?->companyProfile;
            $normalizedStatus = $this->normalizeApplicationStatus($application->status);

            return [
                'id' => $application->application_id,
                'application_id' => $application->application_id,
                'job_id' => $application->job_id,
                'status' => $normalizedStatus,
                'status_label' => $this->internStatusLabel($application->status),
                'raw_status' => $application->status,
                'applied_at' => optional($application->created_at)->format('d M Y'),
                'applied_at_iso' => optional($application->created_at)->toDateTimeString(),
                'job' => [
                    'id' => $job?->id,
                    'title' => $job?->judul_posisi ?? $job?->judul_pekerjaan ?? 'N/A',
                    'position' => $job?->judul_posisi ?? $job?->judul_pekerjaan ?? 'N/A',
                    'location' => $job?->lokasi ?? '-',
                    'type' => $job?->tipe_magang ?? $job?->tipe_pekerjaan ?? '-',
                    'salary' => $job?->gaji_per_bulan,
                    'tanggal_penutupan_lamaran' => optional($job?->tanggal_penutupan_lamaran)->format('Y-m-d'),
                    'tanggal_mulai_kerja' => optional($job?->tanggal_mulai_kerja)->format('Y-m-d'),
                    'close_date' => optional($job?->tanggal_penutupan_lamaran)->format('d M Y'),
                    'start_date' => optional($job?->tanggal_mulai_kerja)->format('d M Y'),
                ],
                'company' => [
                    'id' => $company?->id,
                    'name' => $company?->nama_perusahaan ?? 'N/A',
                    'company_name' => $company?->nama_perusahaan ?? 'N/A',
                    'logo_url' => $company?->logo_perusahaan ? asset('storage/' . ltrim($company->logo_perusahaan, '/')) : null,
                    'location' => $company?->alamat_kantor_pusat ?? '-',
                ],
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    private function pretestQuestions(): array
    {
        return config('pretest.questions', []);
    }

    private function pretestDurationMinutes(): int
    {
        return (int) config('pretest.duration_minutes', 20);
    }

    private function expiresAt(InternProfile $profile): ?string
    {
        if (!$profile->test_started_at) {
            return null;
        }

        return Carbon::parse($profile->test_started_at)
            ->addMinutes($this->pretestDurationMinutes())
            ->toDateTimeString();
    }

    private function isTestExpired(InternProfile $profile): bool
    {
        if (!$profile->test_started_at) {
            return false;
        }

        return now()->greaterThan(
            Carbon::parse($profile->test_started_at)->addMinutes($this->pretestDurationMinutes())
        );
    }

    private function normalizeArrayInput(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($value) ? $value : [];
    }

    private function normalizeNestedFiles(mixed $value): array
    {
        if ($value instanceof UploadedFile) {
            return [$value];
        }

        return is_array($value) ? $value : [];
    }

    private function mergeNestedFileInputs(mixed ...$sources): array
    {
        $merged = [];

        foreach ($sources as $source) {
            $normalized = $this->normalizeNestedFiles($source);

            if ($normalized === []) {
                continue;
            }

            $merged = array_replace_recursive($merged, $normalized);
        }

        return $merged;
    }

    private function shouldSyncNestedItems(array $items, array $files): bool
    {
        if ($this->extractUploadedFile($files)) {
            return true;
        }

        foreach ($items as $item) {
            if (is_array($item)) {
                foreach ($item as $value) {
                    if (is_string($value) && trim($value) !== '') {
                        return true;
                    }
                }

                continue;
            }

            if (is_string($item) && trim($item) !== '') {
                return true;
            }
        }

        return false;
    }

    private function storeNestedDocument(array $items, int $index, array $keys): ?string
    {
        $file = $this->extractUploadedFile($items[$index] ?? null, $keys);

        if ($file) {
            return $file->store('profiles/documents', 'public');
        }

        return null;
    }

    private function extractUploadedFile(mixed $value, array $preferredKeys = []): ?UploadedFile
    {
        if ($value instanceof UploadedFile) {
            return $value;
        }

        if (!is_array($value)) {
            return null;
        }

        foreach ($preferredKeys as $key) {
            $candidate = $value[$key] ?? null;

            if ($candidate instanceof UploadedFile) {
                return $candidate;
            }

            if (is_array($candidate)) {
                $found = $this->extractUploadedFile($candidate, $preferredKeys);

                if ($found) {
                    return $found;
                }
            }
        }

        foreach ($value as $candidate) {
            $found = $this->extractUploadedFile($candidate, $preferredKeys);

            if ($found) {
                return $found;
            }
        }

        return null;
    }

    private function transformDocumentItem(array $item): array
    {
        $documentUrl = $this->documentUrl($item['document_path'] ?? null);

        return array_merge($item, [
            'document' => $documentUrl,
            'file' => $documentUrl,
            'document_url' => $documentUrl,
            'file_url' => $documentUrl,
            'preview_url' => $documentUrl,
            'supporting_document_url' => $documentUrl,
            'document_name' => isset($item['document_path']) && $item['document_path']
                ? basename((string) $item['document_path'])
                : null,
            'file_name' => isset($item['document_path']) && $item['document_path']
                ? basename((string) $item['document_path'])
                : null,
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

    private function normalizeDateValue(mixed $value): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function buildPeriod(mixed $period, ?string $startDate, ?string $endDate): string
    {
        $period = is_string($period) ? trim($period) : '';

        if ($period !== '') {
            return $period;
        }

        if ($startDate && $endDate) {
            return $startDate . ' - ' . $endDate;
        }

        if ($startDate) {
            return $startDate;
        }

        if ($endDate) {
            return $endDate;
        }

        return '';
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

    private function documentUrl(?string $path): ?string
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
}
