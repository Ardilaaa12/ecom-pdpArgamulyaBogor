<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'navbar_id',
        'title',
        'description',
        'media',
        'status',
        'type',
    ];

    public function content() {
        return $this->hasMany(Content::class);
    }
}
