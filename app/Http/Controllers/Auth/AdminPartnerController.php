<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\Lowongan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdminPartnerController extends Controller
{
    private function normalizePartnerPayload(Request $request): void
    {
        $request->merge([
            'nama_perusahaan' => $request->input('nama_perusahaan')
                ?? $request->input('company_name')
                ?? $request->input('companyName'),
            'industri' => $request->input('industri')
                ?? $request->input('industry'),
            'website' => $request->input('website')
                ?? $request->input('website_url')
                ?? $request->input('websiteUrl'),
            'deskripsi' => $request->input('deskripsi')
                ?? $request->input('description'),
            'nama_pic' => $request->input('nama_pic')
                ?? $request->input('pic_name')
                ?? $request->input('picName'),
            'jabatan_pic' => $request->input('jabatan_pic')
                ?? $request->input('pic_position')
                ?? $request->input('picPosition')
                ?? $request->input('jabatan'),
            'email' => $request->input('email')
                ?? $request->input('email_pic')
                ?? $request->input('pic_email')
                ?? $request->input('picEmail'),
            'notelp' => $request->input('notelp')
                ?? $request->input('phone')
                ?? $request->input('phone_number')
                ?? $request->input('phoneNumber')
                ?? $request->input('whatsapp'),
            'alamat_lengkap' => $request->input('alamat_lengkap')
                ?? $request->input('address')
                ?? $request->input('alamat'),
            'kota' => $request->input('kota')
                ?? $request->input('city'),
            'provinsi' => $request->input('provinsi')
                ?? $request->input('province'),
            'kode_pos' => $request->input('kode_pos')
                ?? $request->input('postal_code')
                ?? $request->input('postalCode'),
        ]);
    }

    
    public function index(Request $request)
    {
        $query = CompanyProfile::with(['user']);

        // Search bar
        if ($request->has('search')) {
            $query->where('nama_perusahaan', 'like', '%' . $request->search . '%');
        }

        $partners = $query->latest()->paginate(10);

        return response()->json([
            'status' => 'success',
            'stats' => [
                'total_partner' => CompanyProfile::count(),
                'kolaborasi_aktif' => CompanyProfile::where('status_mitra', 'active')->count(),
                'butuh_review' => CompanyProfile::whereIn('status_mitra', ['pending', 'reviewed'])->count(),
            ],
            'data' => $partners->map(fn($item) => [
                'id' => $item->id,
                'id_perusahaan' => 'CMP-' . str_pad($item->id, 3, '0', STR_PAD_LEFT),
                'nama_perusahaan' => [
                    'nama' => $item->nama_perusahaan,
                    'lokasi' => $item->alamat_kantor_pusat ?? 'N/A',
                ],
                'tipe_bisnis' => $item->industri ?? 'N/A',
                'tanggal_pengajuan' => optional($item->created_at)->format('d M Y') ?? 'N/A',
                'status_verifikasi' => $this->formatStatus($item->status_mitra),
            ]),
            'pagination' => [
                'total' => $partners->total(),
                'current_page' => $partners->currentPage(),
            ]
        ]);
    }

    
    public function show($id)
    {
        $partner = CompanyProfile::with(['user', 'lowongans'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'perusahaan' => $partner,
                'pic' => [
                    'nama' => $partner->nama_pic ?? 'N/A',
                    'jabatan' => $partner->jabatan_pic ?? 'N/A',
                    'email' => $partner->user->email,
                    'phone' => $partner->user->notelp
                ],
        
                'aktivitas' => [
                    ['tgl' => now()->format('d M Y'), 'pesan' => 'Membuka lowongan baru'],
                    ['tgl' => $partner->updated_at->format('d M Y'), 'pesan' => 'Dokumen MOU diverifikasi'],
                ],
                
                'dokumen' => [
                    ['nama' => 'MOU_Vokaseek.pdf', 'status' => 'Terverifikasi'],
                    ['nama' => 'SIUP_License.jpg', 'status' => 'Terverifikasi'],
                ]
            ]
        ]);
    }

    
    public function store(Request $request)
    {
        $this->normalizePartnerPayload($request);

        $validated = $request->validate([
            'nama_perusahaan' => 'required|string',
            'industri' => 'required',
            'website' => 'nullable|url',
            'deskripsi' => 'nullable|max:500',
            'nama_pic' => 'required',
            'jabatan_pic' => 'required',
            'email' => 'required|email|unique:users,email',
            'notelp' => 'required',
            'alamat_lengkap' => 'required',
            'kota' => 'required',
            'provinsi' => 'required',
            'kode_pos' => 'required',
            'document' => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:5120',
            'supporting_document' => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:5120',
        ]);

        $partner = DB::transaction(function () use ($validated, $request) {
            
            $user = User::create([
                'nama' => $validated['nama_pic'],
                'email' => $validated['email'],
                'password' => Str::random(12),
                'role' => 'company',
                'notelp' => $validated['notelp']
            ]);

            $document = $request->file('document', $request->file('supporting_document'));
            $documentPath = $document?->store('company/documents', 'public');
            
            return CompanyProfile::create([
                'user_id' => $user->user_id,
                'nama_perusahaan' => $validated['nama_perusahaan'],
                'industri' => $validated['industri'],
                'website_url' => $validated['website'] ?? null,
                'deskripsi' => $validated['deskripsi'] ?? null,
                'notelp' => $validated['notelp'],
                'loa_pdf' => $documentPath,
                'alamat_kantor_pusat' => $validated['alamat_lengkap'] . ', ' . $validated['kota'] . ', ' . $validated['provinsi'] . ' ' . $validated['kode_pos'],
                'status_mitra' => 'active',
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Perusahaan Berhasil Ditambahkan!',
            'data' => $partner
        ], 201);
    }

    
    public function destroy($id)
    {
        $partner = CompanyProfile::with(['user', 'lowongans'])->findOrFail($id);

        DB::transaction(function () use ($partner) {
            $user = $partner->user;

            Lowongan::where('company_profile_id', $partner->id)->delete();

            $this->deletePartnerFiles($partner);

            $partner->delete();

            if ($user) {
                $user->delete();
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Mitra berhasil dihapus.',
        ]);
    }

    private function deletePartnerFiles(CompanyProfile $partner): void
    {
        $paths = [
            $partner->loa_pdf,
            $partner->akta_pdf,
            $partner->logo_perusahaan,
            $partner->banner_perusahaan,
        ];

        foreach (array_filter($paths) as $path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function formatStatus(?string $statusMitra): string
    {
        return match ($statusMitra) {
            'active' => 'Active',
            'reviewed' => 'Reviewed',
            'rejected' => 'Rejected',
            default => 'Pending',
        };
    }
}
