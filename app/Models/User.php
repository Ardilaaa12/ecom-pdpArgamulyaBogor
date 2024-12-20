<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    use HasApiTokens;

    protected $fillable = [
        'username',
        'email',
        'password',
        'fullname',
        'address',
        'phone_number',
        'image',
        'role',
        'verification_code',
        'is_verified',
    ];

    public function getImageAttribute($value)
    {
        return asset($value); // Mengembalikan URL lengkap
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function order() {
        return $this->hasMany(Order::class);
    }

    public function review() {
        return $this->hasMany(Review::class);
    }

    public function cartItem() {
        return $this->hasMany(CartItem::class);
    }

    public function likeItem() {
        return $this->hasMany(likeItem::class);
    }

    public function cart() {
        return $this->hasMany(Cart::class);
    }

    public function like() {
        return $this->hasMany(Like::class);
    }
}
