<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lowongan extends Model
{
    use HasFactory;

    protected $table = 'lowongans';

    protected $fillable = [
        'company_profile_id',
        'judul_posisi',
        'judul_pekerjaan',
        'kategori_pekerjaan',
        'tipe_pekerjaan',
        'deskripsi_pekerjaan',
        'persyaratan',
        'lokasi',
        'tipe_magang',
        'pengaturan_kerja',
        'gaji_per_bulan',
        'gaji_min',
        'gaji_max',
        'tanggal_penutupan_lamaran',
        'tanggal_mulai_kerja',
        'tgl_tutup_lamaran',
        'tgl_mulai_kerja',
        'status'
    ];

    protected $appends = [
        'judul_posisi',
        'tipe_magang',
        'gaji_per_bulan',
        'tanggal_penutupan_lamaran',
        'tanggal_mulai_kerja',
    ];

    protected $casts = [
        'tanggal_penutupan_lamaran' => 'date:Y-m-d',
        'tanggal_mulai_kerja' => 'date:Y-m-d',
        'tgl_tutup_lamaran' => 'date:Y-m-d',
        'tgl_mulai_kerja' => 'date:Y-m-d',
    ];

    public function companyProfile()
    {
        return $this->belongsTo(CompanyProfile::class, 'company_profile_id', 'id');
    }

    public function getJudulPosisiAttribute($value)
    {
        return $value ?? $this->attributes['judul_pekerjaan'] ?? null;
    }

    public function getTipeMagangAttribute($value)
    {
        return $value ?? $this->attributes['pengaturan_kerja'] ?? null;
    }

    public function getGajiPerBulanAttribute($value)
    {
        if ($value) {
            return $value;
        }

        $min = $this->attributes['gaji_min'] ?? null;
        $max = $this->attributes['gaji_max'] ?? null;

        if ($min && $max) {
            return "{$min} - {$max}";
        }

        return $min ?: $max;
    }

    public function getTanggalPenutupanLamaranAttribute($value)
    {
        return $value ?? $this->attributes['tgl_tutup_lamaran'] ?? null;
    }

    public function getTanggalMulaiKerjaAttribute($value)
    {
        return $value ?? $this->attributes['tgl_mulai_kerja'] ?? null;
    }
}
