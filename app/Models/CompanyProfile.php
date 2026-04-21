<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    use HasFactory;

    protected $table = 'company_profile'; // Sesuai nama tabel di HeidiSQL
    protected $primaryKey = 'id'; // Sesuai screenshot Abang

    protected $fillable = [
        'user_id',
        'nama_perusahaan',
        'industri',
        'ukuran_perusahaan',
        'website_url',
        'deskripsi',
        'notelp',
        'alamat_kantor_pusat',
        'nib',
        'loa_pdf',
        'akta_pdf',
        'logo_perusahaan',
        'banner_perusahaan',
        'status_mitra',
        'linkedin_url',
        'instagram_url',
        'twitter_url',
    ];

    // Relasi balik ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Relasi ke Lowongan
    public function lowongan()
    {
        return $this->hasMany(Lowongan::class, 'company_profile_id', 'id');
    }

    public function lowongans()
    {
        return $this->lowongan();
    }
}
