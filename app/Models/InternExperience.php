<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternExperience extends Model
{
    // Mass Assignment agar bisa simpan data lewat InternExperience::create()
    protected $fillable = [
        'user_id',
        'title',
        'type',
        'company',
        'start_date',
        'end_date',
        'period',
        'document_path',
    ];

    /**
     * Relasi ke model User
     * Karena satu pengalaman dimiliki oleh satu User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
