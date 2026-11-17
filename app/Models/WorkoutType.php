<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'user_id'
    ];

    /**
     * Get the user that owns the workout type.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the workouts for the workout type.
     */
    public function workouts()
    {
        return $this->hasMany(Workout::class);
    }

    /**
     * Get the image URL.
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('uploads/workout-types/' . $this->image);
        }
        return asset('assets/media/placeholder.jpg');
    }
}
