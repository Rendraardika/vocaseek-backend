<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lowongan extends Model
{
    use HasFactory;

    protected $table = 'lowongan'; // Karena nama tabel kita 'lowongan'

    protected $fillable = [
        'company_profile_id',
        'judul_posisi',
        'deskripsi_pekerjaan',
        'persyaratan',
        'lokasi',
        'tipe_magang',
        'gaji_per_bulan',
        'tanggal_penutupan_lamaran',
        'tanggal_mulai_kerja',
        'status'
    ];

    protected $casts = [
        'tanggal_penutupan_lamaran' => 'date:Y-m-d',
        'tanggal_mulai_kerja' => 'date:Y-m-d',
    ];

    public function companyProfile()
    {
        return $this->belongsTo(CompanyProfile::class, 'company_profile_id', 'id');
    }
}
