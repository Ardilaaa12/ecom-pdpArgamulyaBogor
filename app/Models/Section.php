<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
