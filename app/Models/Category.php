<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['nama_kategori', 'icon'];

    public function jobs()
    {
        return $this->hasMany(Job::class, 'category_id', 'id');
    }
}