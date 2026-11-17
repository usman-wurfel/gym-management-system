<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements CanResetPasswordContract
{
    use HasFactory, Notifiable, HasRoles, HasApiTokens, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'is_admin','active','user_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function shifts(){
        return $this->hasMany('App\Models\Shift','user_id');
    }

    public function parent(){
        return $this->belongsTo('App\Models\User','user_id');
    }

    /**
     * Get the subscription history for this user
     */
    public function subscriptionHistory()
    {
        return $this->hasMany('App\Models\SubscribtionHistory');
    }

    /**
     * Get the current active subscription
     */
    public function activeSubscription()
    {
        return $this->hasOne('App\Models\SubscribtionHistory')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->latest();
    }

    /**
     * Get the members for this business owner
     */
    public function members()
    {
        return $this->hasMany('App\Models\Member');
    }
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
