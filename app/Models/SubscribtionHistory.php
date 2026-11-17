<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscribtionHistory extends Model
{
    use HasFactory;
    
    protected $fillable = ['user_id', 'package_id', 'start_date', 'end_date', 'price'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price' => 'decimal:2'
    ];

    /**
     * Get the user (business owner) that owns the subscription
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription package
     */
    public function package()
    {
        return $this->belongsTo(SubscriptionPackage::class);
    }

    /**
     * Get the formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Check if subscription is active
     */
    public function getIsActiveAttribute()
    {
        return now()->between($this->start_date, $this->end_date);
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute()
    {
        if ($this->is_active) {
            return 'Active';
        } elseif (now()->lt($this->start_date)) {
            return 'Pending';
        } else {
            return 'Expired';
        }
    }
}
