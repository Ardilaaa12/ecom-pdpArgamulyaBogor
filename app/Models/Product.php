<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name_product',
        'age',
        'weight',
        'description',
        'price',
        'stock',
        'health_status',
        'photo_product',
    ];

    public function getPhotoProductAttribute($value)
    {
        return asset($value); // Mengembalikan URL lengkap
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function orderDetail() {
        return $this->hasMany(OrderDetail::class);
    }

    public function review() {
        return $this->hasMany(Review::class);
    }

    public function likeItem() {
        return $this->hasMany(LikeItem::class);
    }

    public function cartItem() {
        return $this->hasMany(CartItem::class);
    }
}
