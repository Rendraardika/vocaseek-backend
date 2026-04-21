<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternCertification extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'issuer',
        'issue_date',
        'certificate_number',
        'description',
        'document_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
