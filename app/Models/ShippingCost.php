<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'city',
        'cost',
    ];

    public function shipping() {
        return $this->hasMany(Shipping::class);
    }
}