<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_master_id',
        'payment_date',
        'payment_amount',
        'payment_image',
        'account_name',
    ];

    public function getPaymentImageAttribute($value)
    {
        return asset($value); // Menggunakan asset untuk menghasilkan URL, tapi ini hanya berlaku jika URL base sudah sesuai
    }
        

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function rekening()
    {
        return $this->belongsTo(Rekening::class, 'payment_master_id'); // Payment mengacu pada Rekening
    }
}
