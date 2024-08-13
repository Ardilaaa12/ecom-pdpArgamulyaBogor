<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rekening extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_method',
        'payment_master_image',
    ];

    public function payment() {
        return $this->belongsTo(Payment::class);
    }
}
