<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;

    protected $table = 'job_applications';

    // TAMBAHKAN BARIS INI:
    protected $primaryKey = 'application_id'; 

    protected $fillable = [
        'user_id',
        'job_id',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function lowongan()
    {
        return $this->belongsTo(Lowongan::class, 'job_id', 'id');
    }
}