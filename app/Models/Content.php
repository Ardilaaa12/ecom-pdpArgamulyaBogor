<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'title',
        'description',
        'media',
        'status',
        'type',
    ];

    public function getMediaAttribute($value)
    {
        return asset($value); // Mengembalikan URL lengkap
    }

    public function section() {
        return $this->belongsTo(Section::class);
    }
}
