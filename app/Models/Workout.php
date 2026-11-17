<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'video',
        'user_id',
        'workout_type_id'
    ];

    /**
     * Get the user that owns the workout.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the workout type for the workout.
     */
    public function workoutType()
    {
        return $this->belongsTo(WorkoutType::class);
    }

    /**
     * Get the image URL.
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('uploads/workouts/' . $this->image);
        }
        return asset('assets/media/placeholder.jpg');
    }

    /**
     * Get the video URL.
     */
    public function getVideoUrlAttribute()
    {
        if ($this->video) {
            return asset('uploads/workouts/' . $this->video);
        }
        return null;
    }

    /**
     * Get the formatted description (strip HTML tags for preview).
     */
    public function getDescriptionPreviewAttribute()
    {
        return strip_tags($this->description);
    }

    /**
     * Get the short description (first 100 characters).
     */
    public function getShortDescriptionAttribute()
    {
        $description = strip_tags($this->description);
        return strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
    }
}
