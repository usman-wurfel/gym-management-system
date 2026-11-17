<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberWorkout extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'workout_type_id',
        'assigned_date',
        'notes',
        'user_id'
    ];

    protected $dates = [
        'assigned_date'
    ];

    /**
     * Get the member that owns the workout assignment.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the workout type for the assignment.
     */
    public function workoutType()
    {
        return $this->belongsTo(WorkoutType::class);
    }

    /**
     * Get the user that owns the workout assignment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
