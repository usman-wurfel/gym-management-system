<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\MemberWorkout;
use App\Models\Member;
use App\Models\WorkoutType;
use Carbon\Carbon;

class MemberWorkoutController extends Controller
{
    /**
     * Constructor with middleware and permission checks
     */
    function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || $user->user_id != null) {
                $this->middleware('permission:member-workout-list', ['only' => ['index', 'show', 'getMemberWorkouts']]);
                $this->middleware('permission:member-workout-create', ['only' => ['store', 'assignWorkout']]);
                $this->middleware('permission:member-workout-edit', ['only' => ['update']]);
                $this->middleware('permission:member-workout-delete', ['only' => ['destroy', 'deleteSelected']]);
            }
            return $next($request);
        });
    }

    /**
     * Get all member workout assignments for the authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $id = $user->user_id ?? $user->id;

            // Get query parameters
            $search = $request->input('search', '');
            $perPage = $request->input('per_page', 10);
            $sortBy = $request->input('sort_by', 'assigned_date');
            $sortOrder = $request->input('sort_order', 'desc');
            $memberId = $request->input('member_id', '');
            $workoutTypeId = $request->input('workout_type_id', '');
            $dateFrom = $request->input('date_from', '');
            $dateTo = $request->input('date_to', '');

            // Build query - only get member workouts for this business
            $query = MemberWorkout::with(['member', 'workoutType'])
                ->where('user_id', $id);

            // Apply search
            if (!empty($search)) {
                $query->whereHas('member', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('workoutType', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhere('notes', 'like', "%{$search}%");
            }

            // Apply member filter
            if (!empty($memberId)) {
                $query->where('member_id', $memberId);
            }

            // Apply workout type filter
            if (!empty($workoutTypeId)) {
                $query->where('workout_type_id', $workoutTypeId);
            }

            // Apply date range filter
            if (!empty($dateFrom)) {
                $query->whereDate('assigned_date', '>=', $dateFrom);
            }
            if (!empty($dateTo)) {
                $query->whereDate('assigned_date', '<=', $dateTo);
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Get paginated results
            $memberWorkouts = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $memberWorkouts,
                'message' => 'Member workout assignments retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving member workout assignments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created member workout assignment
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $id = $user->user_id ?? $user->id;

            // Validate the request
            $validator = Validator::make($request->all(), [
                'member_id' => 'required|exists:members,id',
                'workout_type_id' => 'required|exists:workout_types,id',
                'assigned_date' => 'required|date',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify member belongs to the same business
            $member = Member::where('id', $request->member_id)
                ->where('user_id', $id)
                ->first();

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member not found or does not belong to your business'
                ], 404);
            }

            // Verify workout type belongs to the same business
            $workoutType = WorkoutType::where('id', $request->workout_type_id)
                ->where('user_id', $id)
                ->first();

            if (!$workoutType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Workout type not found or does not belong to your business'
                ], 404);
            }

            $data = $request->only(['member_id', 'workout_type_id', 'assigned_date', 'notes']);
            $data['user_id'] = $id;

            $memberWorkout = MemberWorkout::create($data);
            $memberWorkout->load(['member', 'workoutType']);

            return response()->json([
                'success' => true,
                'data' => $memberWorkout,
                'message' => 'Workout assigned to member successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error assigning workout to member: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign workout to member (alias for store)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignWorkout(Request $request)
    {
        return $this->store($request);
    }

    /**
     * Display the specified member workout assignment
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            $memberWorkout = MemberWorkout::with(['member', 'workoutType'])
                ->where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$memberWorkout) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member workout assignment not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $memberWorkout,
                'message' => 'Member workout assignment retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving member workout assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified member workout assignment
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            $memberWorkout = MemberWorkout::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$memberWorkout) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member workout assignment not found'
                ], 404);
            }

            // Validate the request
            $validator = Validator::make($request->all(), [
                'member_id' => 'required|exists:members,id',
                'workout_type_id' => 'required|exists:workout_types,id',
                'assigned_date' => 'required|date',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify member belongs to the same business
            $member = Member::where('id', $request->member_id)
                ->where('user_id', $userId)
                ->first();

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member not found or does not belong to your business'
                ], 404);
            }

            // Verify workout type belongs to the same business
            $workoutType = WorkoutType::where('id', $request->workout_type_id)
                ->where('user_id', $userId)
                ->first();

            if (!$workoutType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Workout type not found or does not belong to your business'
                ], 404);
            }

            $data = $request->only(['member_id', 'workout_type_id', 'assigned_date', 'notes']);
            $memberWorkout->update($data);
            $memberWorkout->load(['member', 'workoutType']);

            return response()->json([
                'success' => true,
                'data' => $memberWorkout,
                'message' => 'Member workout assignment updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating member workout assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified member workout assignment
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            $memberWorkout = MemberWorkout::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$memberWorkout) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member workout assignment not found'
                ], 404);
            }

            $memberWorkout->delete();

            return response()->json([
                'success' => true,
                'message' => 'Member workout assignment deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting member workout assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete multiple member workout assignments
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSelected(Request $request)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:member_workouts,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $ids = $request->input('ids');
            $memberWorkouts = MemberWorkout::whereIn('id', $ids)
                ->where('user_id', $userId)
                ->get();

            if ($memberWorkouts->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No member workout assignments found to delete'
                ], 404);
            }

            foreach ($memberWorkouts as $memberWorkout) {
                $memberWorkout->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Selected member workout assignments deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting member workout assignments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get workout assignments for a specific member
     *
     * @param Request $request
     * @param int $memberId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMemberWorkouts(Request $request, $memberId)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            // Verify member belongs to the same business
            $member = Member::where('id', $memberId)
                ->where('user_id', $userId)
                ->first();

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member not found or does not belong to your business'
                ], 404);
            }

            // Get query parameters
            $perPage = $request->input('per_page', 10);
            $sortBy = $request->input('sort_by', 'assigned_date');
            $sortOrder = $request->input('sort_order', 'desc');
            $workoutTypeId = $request->input('workout_type_id', '');
            $dateFrom = $request->input('date_from', '');
            $dateTo = $request->input('date_to', '');

            // Build query
            $query = MemberWorkout::with('workoutType')
                ->where('member_id', $memberId)
                ->where('user_id', $userId);

            // Apply workout type filter
            if (!empty($workoutTypeId)) {
                $query->where('workout_type_id', $workoutTypeId);
            }

            // Apply date range filter
            if (!empty($dateFrom)) {
                $query->whereDate('assigned_date', '>=', $dateFrom);
            }
            if (!empty($dateTo)) {
                $query->whereDate('assigned_date', '<=', $dateTo);
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Get paginated results
            $memberWorkouts = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'member' => $member,
                    'workouts' => $memberWorkouts
                ],
                'message' => 'Member workouts retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving member workouts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get workout assignments for today
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTodayWorkouts(Request $request)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;
            $today = Carbon::today();

            $memberWorkouts = MemberWorkout::with(['member', 'workoutType'])
                ->where('user_id', $userId)
                ->whereDate('assigned_date', $today)
                ->orderBy('assigned_date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'workouts' => $memberWorkouts,
                    'total_count' => $memberWorkouts->count(),
                    'date' => $today->format('Y-m-d'),
                    'date_formatted' => $today->format('F j, Y')
                ],
                'message' => 'Today\'s workout assignments retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving today\'s workout assignments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get workout assignments for a specific date
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWorkoutsByDate(Request $request)
    {
        try {
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

            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;
            $date = Carbon::parse($request->date)->startOfDay();

            $memberWorkouts = MemberWorkout::with(['member', 'workoutType'])
                ->where('user_id', $userId)
                ->whereDate('assigned_date', $date)
                ->orderBy('assigned_date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'workouts' => $memberWorkouts,
                    'total_count' => $memberWorkouts->count(),
                    'date' => $date->format('Y-m-d'),
                    'date_formatted' => $date->format('F j, Y')
                ],
                'message' => 'Workout assignments for the specified date retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving workout assignments for the specified date: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get members and workout types for assignment
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAssignmentData()
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            $members = Member::where('user_id', $userId)
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->get();

            $workoutTypes = WorkoutType::where('user_id', $userId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'members' => $members,
                    'workout_types' => $workoutTypes
                ],
                'message' => 'Assignment data retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving assignment data: ' . $e->getMessage()
            ], 500);
        }
    }
}

