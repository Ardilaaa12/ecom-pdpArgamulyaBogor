<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rekening extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_method',
        'payment_master_image',
    ];

    public function getPaymentMasterImageAttribute($value)
    {
        return asset($value); // Mengembalikan URL lengkap
    }
    
    public function payments()
    {
        return $this->hasMany(Payment::class, 'payment_master_id'); // Relasi ke tabel payment
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($image) => url('/storage/rekening' . $image),
        );
    }
}
