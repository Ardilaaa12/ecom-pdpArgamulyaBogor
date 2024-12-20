<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'image',
        'description',
        'review_date',
        'amount_like',
        'rate'
    ];

    public function getImageAttribute($value)
    {
        return asset($value); // Mengembalikan URL lengkap
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
