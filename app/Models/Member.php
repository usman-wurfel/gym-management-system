<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Member extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    
    protected $fillable = ['name', 'email', 'phone', 'address', 'city', 'package_id', 'start_date', 'status','code','user_id','password'];
    
    protected $hidden = ['password', 'remember_token'];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function businessOwner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
