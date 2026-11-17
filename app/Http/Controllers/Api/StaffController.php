<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
use DB;

class StaffController extends Controller
{
    /**
     * Constructor with middleware and permission checks
     */
    function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || $user->user_id != null) {
                $this->middleware('permission:user-list', ['only' => ['index', 'show']]);
                $this->middleware('permission:user-create', ['only' => ['store']]);
                $this->middleware('permission:user-edit', ['only' => ['update']]);
                $this->middleware('permission:user-delete', ['only' => ['destroy', 'deleteSelected']]);
            }
            return $next($request);
        });
    }

    /**
     * Get all staff for the authenticated user
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

            // Build query - only get admin users (staff) for this business
            $query = User::where('is_admin', 1)->where('user_id', $id);

            // Apply search
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('created_at', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Get paginated results
            $staff = $query->paginate($perPage);

            // Transform data to include roles
            $staffData = $staff->getCollection()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'active' => (bool) $user->active,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'roles' => $user->roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                        ];
                    })
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Staff retrieved successfully',
                'data' => [
                    'staff' => $staffData,
                    'pagination' => [
                        'current_page' => $staff->currentPage(),
                        'last_page' => $staff->lastPage(),
                        'per_page' => $staff->perPage(),
                        'total' => $staff->total(),
                        'from' => $staff->firstItem(),
                        'to' => $staff->lastItem(),
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving staff',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific staff member
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            $staffMember = User::where('id', $id)
                              ->where('is_admin', 1)
                              ->where('user_id', $userId)
                              ->first();

            if (!$staffMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff member not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Staff member retrieved successfully',
                'data' => [
                    'staff' => [
                        'id' => $staffMember->id,
                        'name' => $staffMember->name,
                        'email' => $staffMember->email,
                        'active' => (bool) $staffMember->active,
                        'is_admin' => (bool) $staffMember->is_admin,
                        'user_id' => $staffMember->user_id,
                        'created_at' => $staffMember->created_at,
                        'updated_at' => $staffMember->updated_at,
                        'roles' => $staffMember->roles->map(function ($role) {
                            return [
                                'id' => $role->id,
                                'name' => $role->name,
                            ];
                        })
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving staff member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all roles for creating/editing staff
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoles()
    {
        try {
            $user = Auth::user();
            $id = $user->user_id ?? $user->id;

            $roles = Role::where('user_id', $id)->get();

            return response()->json([
                'success' => true,
                'message' => 'Roles retrieved successfully',
                'data' => [
                    'roles' => $roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                        ];
                    })
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new staff member
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'role' => 'required|string|exists:roles,name',
                'active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $id = $user->user_id ?? $user->id;

            // Create staff member
            $staffMember = new User();
            $staffMember->user_id = $id;
            $staffMember->name = $request->input('name');
            $staffMember->email = $request->input('email');
            $staffMember->is_admin = 1;
            $staffMember->active = $request->input('active', true);
            $staffMember->password = Hash::make($request->input('password'));
            $staffMember->save();

            // Assign role
            $staffMember->assignRole($request->input('role'));

            return response()->json([
                'success' => true,
                'message' => 'Staff member created successfully',
                'data' => [
                    'staff' => [
                        'id' => $staffMember->id,
                        'name' => $staffMember->name,
                        'email' => $staffMember->email,
                        'active' => (bool) $staffMember->active,
                        'is_admin' => (bool) $staffMember->is_admin,
                        'user_id' => $staffMember->user_id,
                        'created_at' => $staffMember->created_at,
                        'updated_at' => $staffMember->updated_at,
                        'roles' => $staffMember->roles->map(function ($role) {
                            return [
                                'id' => $role->id,
                                'name' => $role->name,
                            ];
                        })
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating staff member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing staff member
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

            // Find staff member
            $staffMember = User::where('id', $id)
                              ->where('is_admin', 1)
                              ->where('user_id', $userId)
                              ->first();

            if (!$staffMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff member not found'
                ], 404);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $staffMember->id,
                'role' => 'required|string|exists:roles,name',
                'password' => 'nullable|string|min:6',
                'active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update staff member
            $staffMember->name = $request->input('name');
            $staffMember->email = $request->input('email');
            $staffMember->is_admin = 1;
            $staffMember->active = $request->input('active', true);

            // Update password if provided
            if ($request->filled('password')) {
                $staffMember->password = Hash::make($request->input('password'));
            }

            $staffMember->save();

            // Update role
            DB::table('model_has_roles')->where('model_id', $id)->delete();
            $staffMember->assignRole($request->input('role'));

            return response()->json([
                'success' => true,
                'message' => 'Staff member updated successfully',
                'data' => [
                    'staff' => [
                        'id' => $staffMember->id,
                        'name' => $staffMember->name,
                        'email' => $staffMember->email,
                        'active' => (bool) $staffMember->active,
                        'is_admin' => (bool) $staffMember->is_admin,
                        'user_id' => $staffMember->user_id,
                        'created_at' => $staffMember->created_at,
                        'updated_at' => $staffMember->updated_at,
                        'roles' => $staffMember->roles->map(function ($role) {
                            return [
                                'id' => $role->id,
                                'name' => $role->name,
                            ];
                        })
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating staff member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a staff member
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            // Find staff member
            $staffMember = User::where('id', $id)
                              ->where('is_admin', 1)
                              ->where('user_id', $userId)
                              ->first();

            if (!$staffMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff member not found'
                ], 404);
            }

            // Check if it's not a super admin (is_admin == 0 means regular user)
            if ($staffMember->is_admin == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete super admin users'
                ], 403);
            }

            // Delete staff member
            $staffMember->delete();

            return response()->json([
                'success' => true,
                'message' => 'Staff member deleted successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting staff member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete multiple staff members
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSelected(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'user_ids' => 'required|array',
                'user_ids.*' => 'integer|exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            // Find and delete staff members
            $deletedCount = 0;
            foreach ($request->input('user_ids') as $staffId) {
                $staffMember = User::where('id', $staffId)
                                  ->where('is_admin', 1)
                                  ->where('user_id', $userId)
                                  ->first();

                if ($staffMember && $staffMember->is_admin != 0) {
                    $staffMember->delete();
                    $deletedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} staff member(s) deleted successfully",
                'data' => [
                    'deleted_count' => $deletedCount
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting staff members',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get staff member roles for editing
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStaffRoles($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            $staffMember = User::where('id', $id)
                              ->where('is_admin', 1)
                              ->where('user_id', $userId)
                              ->first();

            if (!$staffMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff member not found'
                ], 404);
            }

            $userRole = $staffMember->roles->pluck('name', 'name')->all();

            return response()->json([
                'success' => true,
                'message' => 'Staff member roles retrieved successfully',
                'data' => [
                    'staff' => [
                        'id' => $staffMember->id,
                        'name' => $staffMember->name,
                        'email' => $staffMember->email,
                        'active' => (bool) $staffMember->active,
                    ],
                    'user_roles' => array_keys($userRole)
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving staff member roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
