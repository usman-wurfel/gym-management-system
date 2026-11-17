<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;

class RoleController extends Controller
{
    /**
     * Constructor with middleware and permission checks
     */
    function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || $user->user_id != null) {
                $this->middleware('permission:role-list', ['only' => ['index', 'show']]);
                $this->middleware('permission:role-create', ['only' => ['store']]);
                $this->middleware('permission:role-edit', ['only' => ['update']]);
                $this->middleware('permission:role-delete', ['only' => ['destroy', 'deleteSelected']]);
            }
            return $next($request);
        });
    }

    /**
     * Get all roles for the authenticated user
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

            // Build query
            $query = Role::where('user_id', $id);

            // Apply search
            if (!empty($search)) {
                $query->where('name', 'like', "%{$search}%");
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Get paginated results
            $roles = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Roles retrieved successfully',
                'data' => [
                    'roles' => $roles->items(),
                    'pagination' => [
                        'current_page' => $roles->currentPage(),
                        'last_page' => $roles->lastPage(),
                        'per_page' => $roles->perPage(),
                        'total' => $roles->total(),
                        'from' => $roles->firstItem(),
                        'to' => $roles->lastItem(),
                    ]
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
     * Get a specific role with its permissions
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            $role = Role::where('id', $id)
                       ->where('user_id', $userId)
                       ->first();

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found'
                ], 404);
            }

            // Get role permissions
            $rolePermissions = Permission::join("role_has_permissions", "role_has_permissions.permission_id", "=", "permissions.id")
                ->where("role_has_permissions.role_id", $role->id)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Role retrieved successfully',
                'data' => [
                    'role' => [
                        'id' => $role->id,
                        'name' => $role->name,
                        'user_id' => $role->user_id,
                        'created_at' => $role->created_at,
                        'updated_at' => $role->updated_at,
                    ],
                    'permissions' => $rolePermissions->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'guard_name' => $permission->guard_name,
                        ];
                    })
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all permissions for creating/editing roles
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPermissions()
    {
        try {
            $permissions = Permission::all();

            return response()->json([
                'success' => true,
                'message' => 'Permissions retrieved successfully',
                'data' => [
                    'permissions' => $permissions->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'guard_name' => $permission->guard_name,
                        ];
                    })
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new role
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:roles,name',
                'permissions' => 'required|array',
                'permissions.*' => 'exists:permissions,id'
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

            // Create role
            $role = new Role();
            $role->user_id = $id;
            $role->name = $request->input('name');
            $role->save();

            // Sync permissions
            $role->syncPermissions($request->input('permissions'));

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully',
                'data' => [
                    'role' => [
                        'id' => $role->id,
                        'name' => $role->name,
                        'user_id' => $role->user_id,
                        'created_at' => $role->created_at,
                        'updated_at' => $role->updated_at,
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing role
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:roles,name,' . $id,
                'permissions' => 'required|array',
                'permissions.*' => 'exists:permissions,id'
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

            // Find role
            $role = Role::where('id', $id)
                       ->where('user_id', $userId)
                       ->first();

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found'
                ], 404);
            }

            // Update role
            $role->name = $request->input('name');
            $role->save();

            // Sync permissions
            $role->syncPermissions($request->input('permissions'));

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
                'data' => [
                    'role' => [
                        'id' => $role->id,
                        'name' => $role->name,
                        'user_id' => $role->user_id,
                        'created_at' => $role->created_at,
                        'updated_at' => $role->updated_at,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a role
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            // Find role
            $role = Role::where('id', $id)
                       ->where('user_id', $userId)
                       ->first();

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found'
                ], 404);
            }

            // Delete role
            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete multiple roles
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSelected(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'role_ids' => 'required|array',
                'role_ids.*' => 'integer|exists:roles,id'
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

            // Find and delete roles
            $deletedCount = 0;
            foreach ($request->input('role_ids') as $roleId) {
                $role = Role::where('id', $roleId)
                           ->where('user_id', $userId)
                           ->first();

                if ($role) {
                    $role->delete();
                    $deletedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} role(s) deleted successfully",
                'data' => [
                    'deleted_count' => $deletedCount
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get role permissions for editing
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRolePermissions($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            $role = Role::where('id', $id)
                       ->where('user_id', $userId)
                       ->first();

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found'
                ], 404);
            }

            // Get role permissions
            $rolePermissions = DB::table("role_has_permissions")
                ->where("role_has_permissions.role_id", $id)
                ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
                ->all();

            return response()->json([
                'success' => true,
                'message' => 'Role permissions retrieved successfully',
                'data' => [
                    'role' => [
                        'id' => $role->id,
                        'name' => $role->name,
                        'user_id' => $role->user_id,
                    ],
                    'role_permissions' => array_keys($rolePermissions)
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving role permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
