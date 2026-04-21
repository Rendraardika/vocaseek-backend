<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $table = 'job_listings'; // Wajib karena nama tabel kita bukan 'jobs'

    protected $fillable = [
        'user_id', 
        'category_id', 
        'judul_pekerjaan', 
        'lokasi', 
        'perusahaan', 
        'tipe', 
        'gaji'
    ];

    // Relasi ke User (Company)
    public function company()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Relasi ke Kategori
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}