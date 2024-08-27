<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Navbar extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'route',
        'status',
        'type',
    ];

    public function section() {
        return $this->belongsTo(Section::class);
    }
}
