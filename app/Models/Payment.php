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

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function rekening() {
        return $this->hasOne(Rekening::class);
    }
}
