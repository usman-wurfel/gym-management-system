<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\WorkoutType;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WorkoutTypeController extends Controller
{
    /**
     * Constructor with middleware and permission checks
     */
    function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || $user->user_id != null) {
                $this->middleware('permission:workout-type-list', ['only' => ['index', 'show']]);
                $this->middleware('permission:workout-type-create', ['only' => ['store']]);
                $this->middleware('permission:workout-type-edit', ['only' => ['update']]);
                $this->middleware('permission:workout-type-delete', ['only' => ['destroy', 'deleteSelected']]);
            }
            return $next($request);
        });
    }

    /**
     * Get all workout types for the authenticated user
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

            // Build query - only get workout types for this business
            $query = WorkoutType::where('user_id', $id);

            // Apply search
            if (!empty($search)) {
                $query->where('name', 'like', "%{$search}%");
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Get paginated results
            $workoutTypes = $query->paginate($perPage);

            // Add image URL to each workout type
            $workoutTypes->getCollection()->transform(function ($workoutType) {
                $workoutType->image_url = $workoutType->image_url;
                return $workoutType;
            });

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

    /**
     * Store a newly created workout type
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
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->only(['name']);
            $data['user_id'] = $id;

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/workout-types'), $imageName);
                $data['image'] = $imageName;
            }

            $workoutType = WorkoutType::create($data);
            $workoutType->image_url = $workoutType->image_url;

            return response()->json([
                'success' => true,
                'data' => $workoutType,
                'message' => 'Workout type created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating workout type: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified workout type
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            $workoutType = WorkoutType::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$workoutType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Workout type not found'
                ], 404);
            }

            $workoutType->image_url = $workoutType->image_url;

            return response()->json([
                'success' => true,
                'data' => $workoutType,
                'message' => 'Workout type retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving workout type: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified workout type
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

            $workoutType = WorkoutType::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$workoutType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Workout type not found'
                ], 404);
            }

            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->only(['name']);

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($workoutType->image && file_exists(public_path('uploads/workout-types/' . $workoutType->image))) {
                    unlink(public_path('uploads/workout-types/' . $workoutType->image));
                }

                $image = $request->file('image');
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/workout-types'), $imageName);
                $data['image'] = $imageName;
            }

            $workoutType->update($data);
            $workoutType->image_url = $workoutType->image_url;

            return response()->json([
                'success' => true,
                'data' => $workoutType,
                'message' => 'Workout type updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating workout type: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified workout type
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            $workoutType = WorkoutType::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$workoutType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Workout type not found'
                ], 404);
            }

            // Delete image if exists
            if ($workoutType->image && file_exists(public_path('uploads/workout-types/' . $workoutType->image))) {
                unlink(public_path('uploads/workout-types/' . $workoutType->image));
            }

            $workoutType->delete();

            return response()->json([
                'success' => true,
                'message' => 'Workout type deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting workout type: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete multiple workout types
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
                'ids.*' => 'integer|exists:workout_types,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $ids = $request->input('ids');
            $workoutTypes = WorkoutType::whereIn('id', $ids)
                ->where('user_id', $userId)
                ->get();

            if ($workoutTypes->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No workout types found to delete'
                ], 404);
            }

            // Delete images and workout types
            foreach ($workoutTypes as $workoutType) {
                if ($workoutType->image && file_exists(public_path('uploads/workout-types/' . $workoutType->image))) {
                    unlink(public_path('uploads/workout-types/' . $workoutType->image));
                }
                $workoutType->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Selected workout types deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting workout types: ' . $e->getMessage()
            ], 500);
        }
    }
}

