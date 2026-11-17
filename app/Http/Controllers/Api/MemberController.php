<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Member;
use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MemberController extends Controller
{
    /**
     * Constructor with middleware and permission checks
     */
    function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || $user->user_id != null) {
                $this->middleware('permission:member-list', ['only' => ['index', 'show', 'getMemberForEdit', 'getMemberPaymentHistory']]);
                $this->middleware('permission:member-create', ['only' => ['store', 'import', 'downloadSample']]);
                $this->middleware('permission:member-edit', ['only' => ['update']]);
                $this->middleware('permission:member-delete', ['only' => ['destroy', 'deleteSelected']]);
            }
            return $next($request);
        });
    }

    /**
     * Get all members for the authenticated user
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

            // Build query - only get members for this business
            $query = Member::with('package')->where('user_id', $id);

            // Apply search
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Get paginated results
            $members = $query->paginate($perPage);

            // Transform data
            $memberData = $members->getCollection()->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'code' => $member->code,
                    'phone' => $member->phone,
                    'address' => $member->address,
                    'city' => $member->city,
                    'package_id' => $member->package_id,
                    'package_name' => $member->package ? $member->package->name : 'N/A',
                    'start_date' => $member->start_date,
                    'start_date_formatted' => $member->start_date ? date('M d, Y', strtotime($member->start_date)) : 'N/A',
                    'status' => (bool) $member->status,
                    'status_text' => $member->status == 1 ? 'Active' : 'Inactive',
                    'user_id' => $member->user_id,
                    'created_at' => $member->created_at,
                    'updated_at' => $member->updated_at,
                    'created_at_formatted' => $member->created_at->diffForHumans()
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Members retrieved successfully',
                'data' => [
                    'members' => $memberData,
                    'pagination' => [
                        'current_page' => $members->currentPage(),
                        'last_page' => $members->lastPage(),
                        'per_page' => $members->perPage(),
                        'total' => $members->total(),
                        'from' => $members->firstItem(),
                        'to' => $members->lastItem(),
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving members',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific member
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            $member = Member::with('package')
                          ->where('id', $id)
                          ->where('user_id', $userId)
                          ->first();

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Member retrieved successfully',
                'data' => [
                    'member' => [
                        'id' => $member->id,
                        'name' => $member->name,
                        'email' => $member->email,
                        'code' => $member->code,
                        'phone' => $member->phone,
                        'address' => $member->address,
                        'city' => $member->city,
                        'package_id' => $member->package_id,
                        'package_name' => $member->package ? $member->package->name : 'N/A',
                        'start_date' => $member->start_date,
                        'start_date_formatted' => $member->start_date ? date('M d, Y', strtotime($member->start_date)) : 'N/A',
                        'status' => (bool) $member->status,
                        'status_text' => $member->status == 1 ? 'Active' : 'Inactive',
                        'user_id' => $member->user_id,
                        'created_at' => $member->created_at,
                        'updated_at' => $member->updated_at,
                        'created_at_formatted' => $member->created_at->diffForHumans()
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all packages for creating/editing members
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPackages()
    {
        try {
            $user = Auth::user();
            $id = $user->user_id ?? $user->id;

            $packages = Package::where('user_id', $id)->get();

            return response()->json([
                'success' => true,
                'message' => 'Packages retrieved successfully',
                'data' => [
                    'packages' => $packages->map(function ($package) {
                        return [
                            'id' => $package->id,
                            'name' => $package->name,
                            'price' => $package->price,
                            'duration' => $package->duration,
                        ];
                    })
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
     * Create a new member
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
                'email' => 'nullable|email|unique:members,email',
                'code' => 'required|string|unique:members,code',
                'phone' => 'required|string|max:20',
                'address' => 'required|string',
                'city' => 'required|string',
                'package_id' => 'required|exists:packages,id',
                'start_date' => 'required|date',
                'status' => 'required|in:0,1',
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

            // Verify package belongs to this business
            $package = Package::where('id', $request->input('package_id'))
                            ->where('user_id', $id)
                            ->first();

            if (!$package) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected package not found or does not belong to your business'
                ], 422);
            }

            // Create member
            $member = new Member();
            $member->name = $request->input('name');
            $member->email = $request->input('email');
            $member->code = $request->input('code');
            $member->phone = $request->input('phone');
            $member->address = $request->input('address');
            $member->city = $request->input('city');
            $member->package_id = $request->input('package_id');
            $member->start_date = $request->input('start_date');
            $member->status = $request->input('status');
            $member->user_id = $id;
            $member->save();

            // Load package relationship
            $member->load('package');

            return response()->json([
                'success' => true,
                'message' => 'Member created successfully',
                'data' => [
                    'member' => [
                        'id' => $member->id,
                        'name' => $member->name,
                        'email' => $member->email,
                        'code' => $member->code,
                        'phone' => $member->phone,
                        'address' => $member->address,
                        'city' => $member->city,
                        'package_id' => $member->package_id,
                        'package_name' => $member->package ? $member->package->name : 'N/A',
                        'start_date' => $member->start_date,
                        'start_date_formatted' => $member->start_date ? date('M d, Y', strtotime($member->start_date)) : 'N/A',
                        'status' => (bool) $member->status,
                        'status_text' => $member->status == 1 ? 'Active' : 'Inactive',
                        'user_id' => $member->user_id,
                        'created_at' => $member->created_at,
                        'updated_at' => $member->updated_at,
                        'created_at_formatted' => $member->created_at->diffForHumans()
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing member
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

            // Find member
            $member = Member::where('id', $id)
                          ->where('user_id', $userId)
                          ->first();

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member not found'
                ], 404);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:members,email,' . $member->id,
                'code' => 'required|string|unique:members,code,' . $member->id,
                'phone' => 'required|string|max:20',
                'address' => 'required|string',
                'city' => 'required|string',
                'package_id' => 'required|exists:packages,id',
                'start_date' => 'required|date',
                'status' => 'required|in:0,1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify package belongs to this business
            $package = Package::where('id', $request->input('package_id'))
                            ->where('user_id', $userId)
                            ->first();

            if (!$package) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected package not found or does not belong to your business'
                ], 422);
            }

            // Update member
            $member->name = $request->input('name');
            $member->email = $request->input('email');
            $member->code = $request->input('code');
            $member->phone = $request->input('phone');
            $member->address = $request->input('address');
            $member->city = $request->input('city');
            $member->package_id = $request->input('package_id');
            $member->start_date = $request->input('start_date');
            $member->status = $request->input('status');
            $member->save();

            // Load package relationship
            $member->load('package');

            return response()->json([
                'success' => true,
                'message' => 'Member updated successfully',
                'data' => [
                    'member' => [
                        'id' => $member->id,
                        'name' => $member->name,
                        'email' => $member->email,
                        'code' => $member->code,
                        'phone' => $member->phone,
                        'address' => $member->address,
                        'city' => $member->city,
                        'package_id' => $member->package_id,
                        'package_name' => $member->package ? $member->package->name : 'N/A',
                        'start_date' => $member->start_date,
                        'start_date_formatted' => $member->start_date ? date('M d, Y', strtotime($member->start_date)) : 'N/A',
                        'status' => (bool) $member->status,
                        'status_text' => $member->status == 1 ? 'Active' : 'Inactive',
                        'user_id' => $member->user_id,
                        'created_at' => $member->created_at,
                        'updated_at' => $member->updated_at,
                        'created_at_formatted' => $member->created_at->diffForHumans()
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a member
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            // Find member
            $member = Member::where('id', $id)
                          ->where('user_id', $userId)
                          ->first();

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member not found'
                ], 404);
            }

            // Delete associated payments first
            $member->payments()->delete();
            
            // Delete member
            $member->delete();

            return response()->json([
                'success' => true,
                'message' => 'Member deleted successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete multiple members
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSelected(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'member_ids' => 'required|array',
                'member_ids.*' => 'integer|exists:members,id'
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

            // Find and delete members
            $deletedCount = 0;
            foreach ($request->input('member_ids') as $memberId) {
                $member = Member::where('id', $memberId)
                              ->where('user_id', $userId)
                              ->first();

                if ($member) {
                    // Delete associated payments first
                    $member->payments()->delete();
                    $member->delete();
                    $deletedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} member(s) deleted successfully",
                'data' => [
                    'deleted_count' => $deletedCount
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting members',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get member details for editing
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMemberForEdit($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            $member = Member::where('id', $id)
                          ->where('user_id', $userId)
                          ->first();

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Member retrieved successfully for editing',
                'data' => [
                    'member' => [
                        'id' => $member->id,
                        'name' => $member->name,
                        'email' => $member->email,
                        'code' => $member->code,
                        'phone' => $member->phone,
                        'address' => $member->address,
                        'city' => $member->city,
                        'package_id' => $member->package_id,
                        'start_date' => $member->start_date,
                        'status' => (bool) $member->status,
                        'user_id' => $member->user_id,
                        'created_at' => $member->created_at,
                        'updated_at' => $member->updated_at
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving member for editing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get member payment history
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMemberPaymentHistory($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            $member = Member::with(['package', 'payments'])
                          ->where('id', $id)
                          ->where('user_id', $userId)
                          ->first();

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Member payment history retrieved successfully',
                'data' => [
                    'member' => [
                        'id' => $member->id,
                        'name' => $member->name,
                        'email' => $member->email,
                        'code' => $member->code,
                        'phone' => $member->phone,
                        'package_name' => $member->package ? $member->package->name : 'N/A',
                        'start_date' => $member->start_date,
                        'start_date_formatted' => $member->start_date ? date('M d, Y', strtotime($member->start_date)) : 'N/A',
                        'status' => (bool) $member->status,
                        'status_text' => $member->status == 1 ? 'Active' : 'Inactive',
                    ],
                    'payments' => $member->payments->map(function ($payment) {
                        return [
                            'id' => $payment->id,
                            'amount' => $payment->amount,
                            'payment_date' => $payment->payment_date,
                            'payment_date_formatted' => $payment->payment_date ? date('M d, Y', strtotime($payment->payment_date)) : 'N/A',
                            'status' => $payment->status,
                            'status_text' => $payment->status == 1 ? 'Paid' : 'Pending',
                            'created_at' => $payment->created_at,
                            'created_at_formatted' => $payment->created_at->diffForHumans()
                        ];
                    })
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving member payment history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download sample file
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadSample()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="members_sample.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, [
                'Name',
                'Email', 
                'Phone',
                'Address',
                'City',
                'Code',
                'Package Name',
                'Start Date (YYYY-MM-DD)',
                'Status (1 for Active, 0 for Inactive)'
            ]);

            // Add sample data
            fputcsv($file, [
                'John Doe',
                'john@example.com',
                '+1234567890',
                '123 Main Street',
                'New York',
                'MEM001',
                'Basic Package',
                '2024-01-01',
                '1'
            ]);

            fputcsv($file, [
                'Jane Smith',
                'jane@example.com',
                '+1234567891',
                '456 Oak Avenue',
                'Los Angeles',
                'MEM002',
                'Premium Package',
                '2024-01-15',
                '1'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import members from Excel/CSV
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $userId = auth()->user()->user_id ?? auth()->user()->id;
            
            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            if ($extension === 'csv') {
                $handle = fopen($file->getPathname(), 'r');
                
                // Skip header row
                $header = fgetcsv($handle);
                
                $rowNumber = 2; // Start from row 2 (after header)
                
                while (($data = fgetcsv($handle)) !== false) {
                    $result = $this->processImportRow($data, $userId, $rowNumber);
                    
                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $errorCount++;
                        $errors[] = $result['error'];
                    }
                    
                    $rowNumber++;
                }
                
                fclose($handle);
            } else {
                // For Excel files, we'll use a simple approach
                $data = Excel::toArray([], $file);
                
                if (!empty($data[0])) {
                    $rows = $data[0];
                    
                    // Skip header row
                    array_shift($rows);
                    
                    $rowNumber = 2;
                    foreach ($rows as $row) {
                        $result = $this->processImportRow($row, $userId, $rowNumber);
                        
                        if ($result['success']) {
                            $successCount++;
                        } else {
                            $errorCount++;
                            $errors[] = $result['error'];
                        }
                        
                        $rowNumber++;
                    }
                }
            }

            DB::commit();

            $message = "Import completed! Successfully imported {$successCount} members.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} rows had errors.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Process a single import row
     *
     * @param array $data
     * @param int $userId
     * @param int $rowNumber
     * @return array
     */
    private function processImportRow($data, $userId, $rowNumber)
    {
        // Validate data structure
        if (count($data) < 9) {
            return [
                'success' => false,
                'error' => "Row {$rowNumber}: Insufficient data columns"
            ];
        }

        $name = trim($data[0]);
        $email = trim($data[1]);
        $phone = trim($data[2]);
        $address = trim($data[3]);
        $city = trim($data[4]);
        $code = trim($data[5]);
        $packageName = trim($data[6]);
        $startDate = trim($data[7]);
        $status = trim($data[8]);

        // Validate required fields
        if (empty($name) || empty($phone) || empty($address) || empty($city) || empty($code)) {
            return [
                'success' => false,
                'error' => "Row {$rowNumber}: Required fields (Name, Phone, Address, City, Code) cannot be empty"
            ];
        }

        // Validate email format if provided
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'error' => "Row {$rowNumber}: Invalid email format"
            ];
        }

        // Check if code already exists
        if (Member::where('code', $code)->where('user_id', $userId)->exists()) {
            return [
                'success' => false,
                'error' => "Row {$rowNumber}: Member code '{$code}' already exists"
            ];
        }

        // Check if email already exists (if provided)
        if (!empty($email) && Member::where('email', $email)->where('user_id', $userId)->exists()) {
            return [
                'success' => false,
                'error' => "Row {$rowNumber}: Email '{$email}' already exists"
            ];
        }

        // Find package by name
        $package = Package::where('name', $packageName)->where('user_id', $userId)->first();
        if (!$package) {
            return [
                'success' => false,
                'error' => "Row {$rowNumber}: Package '{$packageName}' not found"
            ];
        }

        // Validate start date
        if (!strtotime($startDate)) {
            return [
                'success' => false,
                'error' => "Row {$rowNumber}: Invalid start date format. Use YYYY-MM-DD"
            ];
        }

        // Validate status
        if (!in_array($status, ['0', '1'])) {
            return [
                'success' => false,
                'error' => "Row {$rowNumber}: Status must be 0 or 1"
            ];
        }

        // Create member
        try {
            Member::create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'city' => $city,
                'code' => $code,
                'package_id' => $package->id,
                'start_date' => $startDate,
                'status' => $status,
                'user_id' => $userId,
            ]);

            return ['success' => true];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => "Row {$rowNumber}: " . $e->getMessage()
            ];
        }
    }
}
