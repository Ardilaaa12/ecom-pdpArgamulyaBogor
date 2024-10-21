<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LikeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'like_id',
        'product_id',
    ];

    public function like() {
        return $this->belongsTo(Like::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
