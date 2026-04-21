<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternProfile extends Model
{
    use HasFactory;

    protected $table = 'intern_profiles';
    protected $primaryKey = 'intern_id'; // Sesuai database Abang

    protected $fillable = [
        'user_id',
        'foto',
        'tentang_saya',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'notelp',
        'instagram',
        'linkedin',
        'provinsi',
        'kabupaten',
        'detail_alamat',
        'universitas',
        'jurusan',
        'jenjang',
        'ipk',
        'tahun_masuk',
        'tahun_lulus',
        'dokumen_pendidikan_pdf',
        'cv_pdf',
        'portofolio_pdf',
        'surat_rekomendasi_pdf',
        'ktp_pdf',
        'transkrip_nilai_pdf',
        'skor_pretest',
        'test_started_at',
        'test_finished_at',
        'is_profile_complete'
    ];

    protected $casts = [
        'tanggal_lahir' => 'date:Y-m-d',
        'test_started_at' => 'datetime',
        'test_finished_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
