<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use SebastianBergmann\CodeUnit\FunctionUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'no_ref_order',
        'order_date',
        'total_amount',
        'status',
        'notes',
        'check_by',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function orderDetail() {
        return $this->hasMany(OrderDetail::class);
    }

    public function payment() {
        return $this->hasOne(Payment::class);
    }

    public function shipping() {
        return $this->hasOne(Shipping::class);
    }
}
