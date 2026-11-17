<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Package;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    /**
     * Constructor with middleware and permission checks
     */
    function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || $user->user_id != null) {
                $this->middleware('permission:package-list', ['only' => ['index', 'show']]);
                $this->middleware('permission:package-create', ['only' => ['store']]);
                $this->middleware('permission:package-edit', ['only' => ['update']]);
                $this->middleware('permission:package-delete', ['only' => ['destroy', 'deleteSelected']]);
            }
            return $next($request);
        });
    }

    /**
     * Get all packages for the authenticated user
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

            // Build query - only get packages for this business
            $query = Package::where('user_id', $id);

            // Apply search
            if (!empty($search)) {
                $query->where('name', 'like', "%{$search}%");
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Get paginated results
            $packages = $query->paginate($perPage);

            // Transform data
            $packageData = $packages->getCollection()->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'price' => $package->price,
                    'duration' => $package->duration,
                    'slug' => $package->slug,
                    'user_id' => $package->user_id,
                    'created_at' => $package->created_at,
                    'updated_at' => $package->updated_at,
                    'created_at_formatted' => $package->created_at->diffForHumans()
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Packages retrieved successfully',
                'data' => [
                    'packages' => $packageData,
                    'pagination' => [
                        'current_page' => $packages->currentPage(),
                        'last_page' => $packages->lastPage(),
                        'per_page' => $packages->perPage(),
                        'total' => $packages->total(),
                        'from' => $packages->firstItem(),
                        'to' => $packages->lastItem(),
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving packages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific package
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            $package = Package::where('id', $id)
                            ->where('user_id', $userId)
                            ->first();

            if (!$package) {
                return response()->json([
                    'success' => false,
                    'message' => 'Package not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Package retrieved successfully',
                'data' => [
                    'package' => [
                        'id' => $package->id,
                        'name' => $package->name,
                        'price' => $package->price,
                        'duration' => $package->duration,
                        'slug' => $package->slug,
                        'user_id' => $package->user_id,
                        'created_at' => $package->created_at,
                        'updated_at' => $package->updated_at,
                        'created_at_formatted' => $package->created_at->diffForHumans()
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving package',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new package
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
                'price' => 'required|numeric|min:0',
                'duration' => 'required|integer|min:1',
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

            // Create package
            $package = new Package();
            $package->user_id = $id;
            $package->name = $request->input('name');
            $package->price = $request->input('price');
            $package->duration = $request->input('duration');
            
            // Generate slug
            $package->slug = $this->createSlug($request->input('name'), null);
            
            $package->save();

            return response()->json([
                'success' => true,
                'message' => 'Package created successfully',
                'data' => [
                    'package' => [
                        'id' => $package->id,
                        'name' => $package->name,
                        'price' => $package->price,
                        'duration' => $package->duration,
                        'slug' => $package->slug,
                        'user_id' => $package->user_id,
                        'created_at' => $package->created_at,
                        'updated_at' => $package->updated_at,
                        'created_at_formatted' => $package->created_at->diffForHumans()
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating package',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing package
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

            // Find package
            $package = Package::where('id', $id)
                            ->where('user_id', $userId)
                            ->first();

            if (!$package) {
                return response()->json([
                    'success' => false,
                    'message' => 'Package not found'
                ], 404);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'duration' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update package
            $package->name = $request->input('name');
            $package->price = $request->input('price');
            $package->duration = $request->input('duration');
            
            // Generate new slug if name changed
            if ($package->isDirty('name')) {
                $package->slug = $this->createSlug($request->input('name'), $package->id);
            }
            
            $package->save();

            return response()->json([
                'success' => true,
                'message' => 'Package updated successfully',
                'data' => [
                    'package' => [
                        'id' => $package->id,
                        'name' => $package->name,
                        'price' => $package->price,
                        'duration' => $package->duration,
                        'slug' => $package->slug,
                        'user_id' => $package->user_id,
                        'created_at' => $package->created_at,
                        'updated_at' => $package->updated_at,
                        'created_at_formatted' => $package->created_at->diffForHumans()
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating package',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a package
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            // Find package
            $package = Package::where('id', $id)
                            ->where('user_id', $userId)
                            ->first();

            if (!$package) {
                return response()->json([
                    'success' => false,
                    'message' => 'Package not found'
                ], 404);
            }

            // Delete package
            $package->delete();

            return response()->json([
                'success' => true,
                'message' => 'Package deleted successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting package',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete multiple packages
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSelected(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'package_ids' => 'required|array',
                'package_ids.*' => 'integer|exists:packages,id'
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

            // Find and delete packages
            $deletedCount = 0;
            foreach ($request->input('package_ids') as $packageId) {
                $package = Package::where('id', $packageId)
                                ->where('user_id', $userId)
                                ->first();

                if ($package) {
                    $package->delete();
                    $deletedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} package(s) deleted successfully",
                'data' => [
                    'deleted_count' => $deletedCount
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting packages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get package details for editing
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPackageForEdit($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            $package = Package::where('id', $id)
                            ->where('user_id', $userId)
                            ->first();

            if (!$package) {
                return response()->json([
                    'success' => false,
                    'message' => 'Package not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Package retrieved successfully for editing',
                'data' => [
                    'package' => [
                        'id' => $package->id,
                        'name' => $package->name,
                        'price' => $package->price,
                        'duration' => $package->duration,
                        'slug' => $package->slug,
                        'user_id' => $package->user_id,
                        'created_at' => $package->created_at,
                        'updated_at' => $package->updated_at
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving package for editing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a unique slug for the package
     *
     * @param string $title
     * @param int|null $id
     * @return string
     * @throws \Exception
     */
    private function createSlug($title, $id = null)
    {
        // Normalize the title
        $slug = Str::slug($title);

        // Get any that could possibly be related.
        // This cuts the queries down by doing it once.
        $allSlugs = $this->getRelatedSlugs($slug, $id);

        // If we haven't used it before then we are all good.
        if (!$allSlugs->contains('slug', $slug)) {
            return $slug;
        }

        // Just append numbers like a savage until we find not used.
        for ($i = 1; $i <= 10; $i++) {
            $newSlug = $slug . '-' . $i;
            if (!$allSlugs->contains('slug', $newSlug)) {
                return $newSlug;
            }
        }

        throw new \Exception('Can not create a unique slug');
    }

    /**
     * Get related slugs for uniqueness check
     *
     * @param string $slug
     * @param int|null $id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getRelatedSlugs($slug, $id = null)
    {
        $query = Package::select('slug')->where('slug', 'like', $slug . '%');
        
        if ($id !== null) {
            $query->where('id', '<>', $id);
        }
        
        return $query->get();
    }
}
