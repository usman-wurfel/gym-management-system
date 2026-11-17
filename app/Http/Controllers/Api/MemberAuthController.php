<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberWorkout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MemberAuthController extends Controller
{
    /**
     * Member login using code and password
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find member by code
        $member = Member::where('code', $request->code)->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid member code'
            ], 401);
        }

        // Check if member has a password set
        if (!$member->password) {
            return response()->json([
                'success' => false,
                'message' => 'No password set for this member. Please contact admin.'
            ], 401);
        }

        // Verify password
        if (!Hash::check($request->password, $member->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password'
            ], 401);
        }

        // Check if member is active
        if ($member->status != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Member account is inactive'
            ], 401);
        }

        // Create token
        $token = $member->createToken('member-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'member' => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'code' => $member->code,
                    'phone' => $member->phone,
                    'address' => $member->address,
                    'city' => $member->city,
                    'status' => $member->status,
                    'package' => $member->package ? [
                        'id' => $member->package->id,
                        'name' => $member->package->name,
                        'price' => $member->package->price,
                        'duration' => $member->package->duration,
                    ] : null,
                    'start_date' => $member->start_date,
                    'created_at' => $member->created_at,
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ], 200);
    }

    /**
     * Member logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ], 200);
    }

    /**
     * Get authenticated member profile
     */
    public function profile(Request $request)
    {
        $member = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'member' => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'code' => $member->code,
                    'phone' => $member->phone,
                    'address' => $member->address,
                    'city' => $member->city,
                    'status' => $member->status,
                    'package' => $member->package ? [
                        'id' => $member->package->id,
                        'name' => $member->package->name,
                        'price' => $member->package->price,
                        'duration' => $member->package->duration,
                    ] : null,
                    'start_date' => $member->start_date,
                    'created_at' => $member->created_at,
                ]
            ]
        ], 200);
    }

    /**
     * Update member password
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $member = $request->user();

        // Check if member has a password set
        if (!$member->password) {
            return response()->json([
                'success' => false,
                'message' => 'No current password set for this member'
            ], 400);
        }

        // Verify current password
        if (!Hash::check($request->current_password, $member->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 400);
        }

        // Update password
        $member->password = Hash::make($request->password);
        $member->save();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully'
        ], 200);
    }

    /**
     * Get all workouts for authenticated member
     */
    public function getAllWorkouts(Request $request)
    {
        $member = $request->user();

        $workouts = MemberWorkout::where('member_id', $member->id)
            ->with(['workoutType.workouts'])
            ->orderBy('assigned_date', 'desc')
            ->get();

        $workoutsData = $workouts->map(function ($workout) {
            return [
                'id' => $workout->id,
                'workout_type' => [
                    'id' => $workout->workoutType->id,
                    'name' => $workout->workoutType->name,
                    'image' => $workout->workoutType->image_url,
                    'workouts' => $workout->workoutType->workouts->map(function ($workoutItem) {
                        return [
                            'id' => $workoutItem->id,
                            'name' => $workoutItem->name,
                            'description' => $workoutItem->description,
                            'description_preview' => $workoutItem->description_preview,
                            'short_description' => $workoutItem->short_description,
                            'image' => $workoutItem->image_url,
                            'video' => $workoutItem->video_url,
                        ];
                    })
                ],
                'assigned_date' => $workout->assigned_date,
                'notes' => $workout->notes,
                'created_at' => $workout->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'workouts' => $workoutsData,
                'total_count' => $workouts->count()
            ]
        ], 200);
    }

    /**
     * Get current day workouts for authenticated member
     */
    public function getCurrentDayWorkouts(Request $request)
    {
        $member = $request->user();
        $today = Carbon::today();

        $workouts = MemberWorkout::where('member_id', $member->id)
            ->whereDate('assigned_date', $today)
            ->with(['workoutType.workouts'])
            ->orderBy('created_at', 'desc')
            ->get();

        $workoutsData = $workouts->map(function ($workout) {
            return [
                'id' => $workout->id,
                'workout_type' => [
                    'id' => $workout->workoutType->id,
                    'name' => $workout->workoutType->name,
                    'image' => $workout->workoutType->image_url,
                    'workouts' => $workout->workoutType->workouts->map(function ($workoutItem) {
                        return [
                            'id' => $workoutItem->id,
                            'name' => $workoutItem->name,
                            'description' => $workoutItem->description,
                            'description_preview' => $workoutItem->description_preview,
                            'short_description' => $workoutItem->short_description,
                            'image' => $workoutItem->image_url,
                            'video' => $workoutItem->video_url,
                        ];
                    })
                ],
                'assigned_date' => $workout->assigned_date,
                'notes' => $workout->notes,
                'created_at' => $workout->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'workouts' => $workoutsData,
                'total_count' => $workouts->count(),
                'date' => $today->format('Y-m-d'),
                'date_formatted' => $today->format('F j, Y')
            ]
        ], 200);
    }

    /**
     * Get workouts for a specific date
     */
    public function getWorkoutsByDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $member = $request->user();
        $date = Carbon::parse($request->date)->startOfDay();

        $workouts = MemberWorkout::where('member_id', $member->id)
            ->whereDate('assigned_date', $date)
            ->with(['workoutType.workouts'])
            ->orderBy('created_at', 'desc')
            ->get();

        $workoutsData = $workouts->map(function ($workout) {
            return [
                'id' => $workout->id,
                'workout_type' => [
                    'id' => $workout->workoutType->id,
                    'name' => $workout->workoutType->name,
                    'image' => $workout->workoutType->image_url,
                    'workouts' => $workout->workoutType->workouts->map(function ($workoutItem) {
                        return [
                            'id' => $workoutItem->id,
                            'name' => $workoutItem->name,
                            'description' => $workoutItem->description,
                            'description_preview' => $workoutItem->description_preview,
                            'short_description' => $workoutItem->short_description,
                            'image' => $workoutItem->image_url,
                            'video' => $workoutItem->video_url,
                        ];
                    })
                ],
                'assigned_date' => $workout->assigned_date,
                'notes' => $workout->notes,
                'created_at' => $workout->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'workouts' => $workoutsData,
                'total_count' => $workouts->count(),
                'date' => $date->format('Y-m-d'),
                'date_formatted' => $date->format('F j, Y')
            ]
        ], 200);
    }
}