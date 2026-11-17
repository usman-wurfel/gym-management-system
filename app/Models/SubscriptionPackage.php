<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description', 
        'price',
        'duration',
        'features',
        'image',
        'active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration' => 'integer',
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the active status as a string
     */
    public function getStatusTextAttribute()
    {
        return $this->active ? 'Active' : 'Inactive';
    }

    /**
     * Get the formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get the duration text
     */
    public function getDurationTextAttribute()
    {
        return $this->duration . ' days';
    }

    /**
     * Scope to get only active packages
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to get only inactive packages
     */
    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }
} 