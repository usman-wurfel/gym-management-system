<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Workout;
use App\Models\WorkoutType;
use Illuminate\Support\Str;

class WorkoutController extends Controller
{
    /**
     * Constructor with middleware and permission checks
     */
    function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || $user->user_id != null) {
                $this->middleware('permission:workout-list', ['only' => ['index', 'show']]);
                $this->middleware('permission:workout-create', ['only' => ['store']]);
                $this->middleware('permission:workout-edit', ['only' => ['update']]);
                $this->middleware('permission:workout-delete', ['only' => ['destroy', 'deleteSelected']]);
            }
            return $next($request);
        });
    }

    /**
     * Get all workouts for the authenticated user
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
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $workoutTypeId = $request->input('workout_type_id', '');

            // Build query - only get workouts for this business
            $query = Workout::with('workoutType')->where('user_id', $id);

            // Apply search
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Apply workout type filter
            if (!empty($workoutTypeId)) {
                $query->where('workout_type_id', $workoutTypeId);
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Get paginated results
            $workouts = $query->paginate($perPage);

            // Add URL attributes to each workout
            $workouts->getCollection()->transform(function ($workout) {
                $workout->image_url = $workout->image_url;
                $workout->video_url = $workout->video_url;
                $workout->description_preview = $workout->description_preview;
                $workout->short_description = $workout->short_description;
                return $workout;
            });

            return response()->json([
                'success' => true,
                'data' => $workouts,
                'message' => 'Workouts retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving workouts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created workout
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
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'workout_type_id' => 'required|exists:workout_types,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'video' => 'nullable|file|mimes:mp4,avi,mov,wmv,flv|max:102400' // 100MB max for video
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
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

            $data = $request->only(['name', 'description', 'workout_type_id']);
            $data['user_id'] = $id;

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/workouts'), $imageName);
                $data['image'] = $imageName;
            }

            // Handle video upload
            if ($request->hasFile('video')) {
                $video = $request->file('video');
                $videoName = time() . '_' . Str::random(10) . '.' . $video->getClientOriginalExtension();
                $video->move(public_path('uploads/workouts'), $videoName);
                $data['video'] = $videoName;
            }

            $workout = Workout::create($data);
            $workout->load('workoutType');
            $workout->image_url = $workout->image_url;
            $workout->video_url = $workout->video_url;
            $workout->description_preview = $workout->description_preview;
            $workout->short_description = $workout->short_description;

            return response()->json([
                'success' => true,
                'data' => $workout,
                'message' => 'Workout created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating workout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified workout
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            $workout = Workout::with('workoutType')
                ->where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$workout) {
                return response()->json([
                    'success' => false,
                    'message' => 'Workout not found'
                ], 404);
            }

            $workout->image_url = $workout->image_url;
            $workout->video_url = $workout->video_url;
            $workout->description_preview = $workout->description_preview;
            $workout->short_description = $workout->short_description;

            return response()->json([
                'success' => true,
                'data' => $workout,
                'message' => 'Workout retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving workout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified workout
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

            $workout = Workout::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$workout) {
                return response()->json([
                    'success' => false,
                    'message' => 'Workout not found'
                ], 404);
            }

            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'workout_type_id' => 'required|exists:workout_types,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'video' => 'nullable|file|mimes:mp4,avi,mov,wmv,flv|max:102400'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
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

            $data = $request->only(['name', 'description', 'workout_type_id']);

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($workout->image && file_exists(public_path('uploads/workouts/' . $workout->image))) {
                    unlink(public_path('uploads/workouts/' . $workout->image));
                }

                $image = $request->file('image');
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/workouts'), $imageName);
                $data['image'] = $imageName;
            }

            // Handle video upload
            if ($request->hasFile('video')) {
                // Delete old video if exists
                if ($workout->video && file_exists(public_path('uploads/workouts/' . $workout->video))) {
                    unlink(public_path('uploads/workouts/' . $workout->video));
                }

                $video = $request->file('video');
                $videoName = time() . '_' . Str::random(10) . '.' . $video->getClientOriginalExtension();
                $video->move(public_path('uploads/workouts'), $videoName);
                $data['video'] = $videoName;
            }

            $workout->update($data);
            $workout->load('workoutType');
            $workout->image_url = $workout->image_url;
            $workout->video_url = $workout->video_url;
            $workout->description_preview = $workout->description_preview;
            $workout->short_description = $workout->short_description;

            return response()->json([
                'success' => true,
                'data' => $workout,
                'message' => 'Workout updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating workout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified workout
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            $workout = Workout::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$workout) {
                return response()->json([
                    'success' => false,
                    'message' => 'Workout not found'
                ], 404);
            }

            // Delete image if exists
            if ($workout->image && file_exists(public_path('uploads/workouts/' . $workout->image))) {
                unlink(public_path('uploads/workouts/' . $workout->image));
            }

            // Delete video if exists
            if ($workout->video && file_exists(public_path('uploads/workouts/' . $workout->video))) {
                unlink(public_path('uploads/workouts/' . $workout->video));
            }

            $workout->delete();

            return response()->json([
                'success' => true,
                'message' => 'Workout deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting workout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete multiple workouts
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
                'ids.*' => 'integer|exists:workouts,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $ids = $request->input('ids');
            $workouts = Workout::whereIn('id', $ids)
                ->where('user_id', $userId)
                ->get();

            if ($workouts->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No workouts found to delete'
                ], 404);
            }

            // Delete files and workouts
            foreach ($workouts as $workout) {
                if ($workout->image && file_exists(public_path('uploads/workouts/' . $workout->image))) {
                    unlink(public_path('uploads/workouts/' . $workout->image));
                }
                if ($workout->video && file_exists(public_path('uploads/workouts/' . $workout->video))) {
                    unlink(public_path('uploads/workouts/' . $workout->video));
                }
                $workout->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Selected workouts deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting workouts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get workout types for the authenticated user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWorkoutTypes()
    {
        try {
            $user = Auth::user();
            $id = $user->user_id ?? $user->id;

            $workoutTypes = WorkoutType::where('user_id', $id)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $workoutTypes,
                'message' => 'Workout types retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving workout types: ' . $e->getMessage()
            ], 500);
        }
    }
}

