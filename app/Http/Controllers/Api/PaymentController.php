<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Payment;
use App\Models\Member;
use App\Models\Package;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Constructor with middleware and permission checks
     */
    function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || $user->user_id != null) {
                $this->middleware('permission:payment-list', ['only' => ['index', 'show', 'getPaymentForEdit', 'getPendingPayments']]);
                $this->middleware('permission:payment-create', ['only' => ['store']]);
                $this->middleware('permission:payment-edit', ['only' => ['update']]);
                $this->middleware('permission:payment-delete', ['only' => ['destroy', 'deleteSelected']]);
            }
            return $next($request);
        });
    }

    /**
     * Get all payments for the authenticated user
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
            $paymentStatus = $request->input('payment_status', ''); // all, paid, pending, failed
            $month = $request->input('month', '');
            $year = $request->input('year', '');

            // Build query - only get payments for this business
            $query = Payment::with(['member', 'package'])->where('user_id', $id);

            // Apply date filters
            if (!empty($month) && !empty($year)) {
                $query->whereMonth('created_at', $month)
                     ->whereYear('created_at', $year);
            } elseif (!empty($month)) {
                $query->whereMonth('created_at', $month);
            } elseif (!empty($year)) {
                $query->whereYear('created_at', $year);
            }

            // Apply payment status filter
            if (!empty($paymentStatus) && $paymentStatus !== 'all') {
                $query->where('payment_status', $paymentStatus);
            }

            // Apply search
            if (!empty($search)) {
                $query->where(function ($q) use ($search, $id) {
                    $q->whereHas('member', function($memberQuery) use ($search, $id) {
                        $memberQuery->where([['name', 'like', "%{$search}%"],['user_id', $id]])
                            ->orWhere([['code', 'like', "%{$search}%"],['user_id', $id]]);
                    })
                    ->orWhereHas('package', function($packageQuery) use ($search, $id) {
                        $packageQuery->where([['name', 'like', "%{$search}%"],['user_id', $id]]);
                    })
                    ->orWhere([['payment_method', 'like', "%{$search}%"],['user_id', $id]])
                    ->orWhere([['payment_status', 'like', "%{$search}%"],['user_id', $id]]);
                });
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Get paginated results
            $payments = $query->paginate($perPage);

            // Transform data
            $paymentData = $payments->getCollection()->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'member_id' => $payment->member_id,
                    'member_name' => $payment->member ? $payment->member->name . ' - ' . $payment->member->code : 'N/A',
                    'package_id' => $payment->package_id,
                    'package_name' => $payment->package ? $payment->package->name : 'N/A',
                    'amount' => $payment->amount,
                    'amount_formatted' => 'Rs' . number_format($payment->amount, 2),
                    'start_date' => $payment->start_date,
                    'start_date_formatted' => $payment->start_date ? date('M d, Y', strtotime($payment->start_date)) : 'N/A',
                    'end_date' => $payment->end_date,
                    'end_date_formatted' => $payment->end_date ? date('M d, Y', strtotime($payment->end_date)) : 'N/A',
                    'payment_method' => $payment->payment_method,
                    'payment_method_formatted' => ucfirst($payment->payment_method),
                    'payment_status' => $payment->payment_status,
                    'payment_status_formatted' => $payment->payment_status == 'paid' ? 'Paid' : ($payment->payment_status == 'pending' ? 'Pending' : 'Failed'),
                    'note' => $payment->note,
                    'user_id' => $payment->user_id,
                    'created_at' => $payment->created_at,
                    'updated_at' => $payment->updated_at,
                    'created_at_formatted' => $payment->created_at->diffForHumans()
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Payments retrieved successfully',
                'data' => [
                    'payments' => $paymentData,
                    'pagination' => [
                        'current_page' => $payments->currentPage(),
                        'last_page' => $payments->lastPage(),
                        'per_page' => $payments->perPage(),
                        'total' => $payments->total(),
                        'from' => $payments->firstItem(),
                        'to' => $payments->lastItem(),
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving payments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending payments for the authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPendingPayments(Request $request)
    {
        try {
            $user = Auth::user();
            $id = $user->user_id ?? $user->id;

            // Get query parameters
            $search = $request->input('search', '');
            $perPage = $request->input('per_page', 10);
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $month = $request->input('month', '');
            $year = $request->input('year', '');

            // Build query - only get pending payments for this business
            $query = Payment::with(['member', 'package'])
                ->where('payment_status', 'pending')
                ->where('user_id', $id);

            // Apply date filters
            if (!empty($month) && !empty($year)) {
                $query->whereMonth('created_at', $month)
                     ->whereYear('created_at', $year);
            } elseif (!empty($month)) {
                $query->whereMonth('created_at', $month);
            } elseif (!empty($year)) {
                $query->whereYear('created_at', $year);
            }

            // Apply search
            if (!empty($search)) {
                $query->where(function ($q) use ($search, $id) {
                    $q->whereHas('member', function($memberQuery) use ($search, $id) {
                        $memberQuery->where([['name', 'like', "%{$search}%"],['user_id', $id]])
                            ->orWhere([['code', 'like', "%{$search}%"],['user_id', $id]]);
                    })
                    ->orWhereHas('package', function($packageQuery) use ($search, $id) {
                        $packageQuery->where([['name', 'like', "%{$search}%"],['user_id', $id]]);
                    })
                    ->orWhere([['payment_method', 'like', "%{$search}%"],['user_id', $id]]);
                });
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Get paginated results
            $payments = $query->paginate($perPage);

            // Transform data
            $paymentData = $payments->getCollection()->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'member_id' => $payment->member_id,
                    'member_name' => $payment->member ? $payment->member->name . ' - ' . $payment->member->code : 'N/A',
                    'package_id' => $payment->package_id,
                    'package_name' => $payment->package ? $payment->package->name : 'N/A',
                    'amount' => $payment->amount,
                    'amount_formatted' => 'Rs' . number_format($payment->amount, 2),
                    'start_date' => $payment->start_date,
                    'start_date_formatted' => $payment->start_date ? date('M d, Y', strtotime($payment->start_date)) : 'N/A',
                    'end_date' => $payment->end_date,
                    'end_date_formatted' => $payment->end_date ? date('M d, Y', strtotime($payment->end_date)) : 'N/A',
                    'payment_method' => $payment->payment_method,
                    'payment_method_formatted' => ucfirst($payment->payment_method),
                    'payment_status' => $payment->payment_status,
                    'payment_status_formatted' => 'Pending',
                    'note' => $payment->note,
                    'user_id' => $payment->user_id,
                    'created_at' => $payment->created_at,
                    'updated_at' => $payment->updated_at,
                    'created_at_formatted' => $payment->created_at->diffForHumans()
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Pending payments retrieved successfully',
                'data' => [
                    'payments' => $paymentData,
                    'pagination' => [
                        'current_page' => $payments->currentPage(),
                        'last_page' => $payments->lastPage(),
                        'per_page' => $payments->perPage(),
                        'total' => $payments->total(),
                        'from' => $payments->firstItem(),
                        'to' => $payments->lastItem(),
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving pending payments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all members and packages for creating/editing payments
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMembersAndPackages()
    {
        try {
            $user = Auth::user();
            $id = $user->user_id ?? $user->id;

            $members = Member::where('user_id', $id)->get();
            $packages = Package::where('user_id', $id)->get();

            return response()->json([
                'success' => true,
                'message' => 'Members and packages retrieved successfully',
                'data' => [
                    'members' => $members->map(function ($member) {
                        return [
                            'id' => $member->id,
                            'name' => $member->name . ' - ' . $member->code,
                            'code' => $member->code,
                            'phone' => $member->phone,
                        ];
                    }),
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
                'message' => 'Error retrieving members and packages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new payment
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'member_id' => 'required|exists:members,id',
                'package_id' => 'required|exists:packages,id',
                'amount' => 'required|numeric|min:0',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'payment_method' => 'required|in:cash,card,bank_transfer,online',
                'payment_status' => 'required|in:paid,pending,failed',
                'note' => 'nullable|string|max:500',
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

            // Verify member belongs to this business
            $member = Member::where('id', $request->input('member_id'))
                          ->where('user_id', $id)
                          ->first();

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected member not found or does not belong to your business'
                ], 422);
            }

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

            // Create payment
            $payment = new Payment();
            $payment->member_id = $request->input('member_id');
            $payment->package_id = $request->input('package_id');
            $payment->amount = $request->input('amount');
            $payment->start_date = $request->input('start_date');
            $payment->end_date = $request->input('end_date');
            $payment->payment_method = $request->input('payment_method');
            $payment->payment_status = $request->input('payment_status');
            $payment->note = $request->input('note');
            $payment->user_id = $id;
            $payment->save();

            // Load relationships
            $payment->load(['member', 'package']);

            return response()->json([
                'success' => true,
                'message' => 'Payment created successfully',
                'data' => [
                    'payment' => [
                        'id' => $payment->id,
                        'member_id' => $payment->member_id,
                        'member_name' => $payment->member ? $payment->member->name . ' - ' . $payment->member->code : 'N/A',
                        'package_id' => $payment->package_id,
                        'package_name' => $payment->package ? $payment->package->name : 'N/A',
                        'amount' => $payment->amount,
                        'amount_formatted' => 'Rs' . number_format($payment->amount, 2),
                        'start_date' => $payment->start_date,
                        'start_date_formatted' => $payment->start_date ? date('M d, Y', strtotime($payment->start_date)) : 'N/A',
                        'end_date' => $payment->end_date,
                        'end_date_formatted' => $payment->end_date ? date('M d, Y', strtotime($payment->end_date)) : 'N/A',
                        'payment_method' => $payment->payment_method,
                        'payment_method_formatted' => ucfirst($payment->payment_method),
                        'payment_status' => $payment->payment_status,
                        'payment_status_formatted' => $payment->payment_status == 'paid' ? 'Paid' : ($payment->payment_status == 'pending' ? 'Pending' : 'Failed'),
                        'note' => $payment->note,
                        'user_id' => $payment->user_id,
                        'created_at' => $payment->created_at,
                        'updated_at' => $payment->updated_at,
                        'created_at_formatted' => $payment->created_at->diffForHumans()
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific payment
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            $payment = Payment::with(['member', 'package'])
                          ->where('id', $id)
                          ->where('user_id', $userId)
                          ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment retrieved successfully',
                'data' => [
                    'payment' => [
                        'id' => $payment->id,
                        'member_id' => $payment->member_id,
                        'member_name' => $payment->member ? $payment->member->name . ' - ' . $payment->member->code : 'N/A',
                        'member_details' => $payment->member ? [
                            'id' => $payment->member->id,
                            'name' => $payment->member->name,
                            'code' => $payment->member->code,
                            'phone' => $payment->member->phone,
                            'email' => $payment->member->email,
                        ] : null,
                        'package_id' => $payment->package_id,
                        'package_name' => $payment->package ? $payment->package->name : 'N/A',
                        'package_details' => $payment->package ? [
                            'id' => $payment->package->id,
                            'name' => $payment->package->name,
                            'price' => $payment->package->price,
                            'duration' => $payment->package->duration,
                        ] : null,
                        'amount' => $payment->amount,
                        'amount_formatted' => 'Rs' . number_format($payment->amount, 2),
                        'start_date' => $payment->start_date,
                        'start_date_formatted' => $payment->start_date ? date('M d, Y', strtotime($payment->start_date)) : 'N/A',
                        'end_date' => $payment->end_date,
                        'end_date_formatted' => $payment->end_date ? date('M d, Y', strtotime($payment->end_date)) : 'N/A',
                        'payment_method' => $payment->payment_method,
                        'payment_method_formatted' => ucfirst($payment->payment_method),
                        'payment_status' => $payment->payment_status,
                        'payment_status_formatted' => $payment->payment_status == 'paid' ? 'Paid' : ($payment->payment_status == 'pending' ? 'Pending' : 'Failed'),
                        'note' => $payment->note,
                        'user_id' => $payment->user_id,
                        'created_at' => $payment->created_at,
                        'updated_at' => $payment->updated_at,
                        'created_at_formatted' => $payment->created_at->diffForHumans()
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment details for editing
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentForEdit($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            $payment = Payment::where('id', $id)
                          ->where('user_id', $userId)
                          ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment retrieved successfully for editing',
                'data' => [
                    'payment' => [
                        'id' => $payment->id,
                        'member_id' => $payment->member_id,
                        'package_id' => $payment->package_id,
                        'amount' => $payment->amount,
                        'start_date' => $payment->start_date,
                        'end_date' => $payment->end_date,
                        'payment_method' => $payment->payment_method,
                        'payment_status' => $payment->payment_status,
                        'note' => $payment->note,
                        'user_id' => $payment->user_id,
                        'created_at' => $payment->created_at,
                        'updated_at' => $payment->updated_at
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving payment for editing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing payment
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

            // Find payment
            $payment = Payment::where('id', $id)
                          ->where('user_id', $userId)
                          ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'member_id' => 'required|exists:members,id',
                'package_id' => 'required|exists:packages,id',
                'amount' => 'required|numeric|min:0',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'payment_method' => 'required|in:cash,card,bank_transfer,online',
                'payment_status' => 'required|in:paid,pending,failed',
                'note' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify member belongs to this business
            $member = Member::where('id', $request->input('member_id'))
                          ->where('user_id', $userId)
                          ->first();

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected member not found or does not belong to your business'
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

            // Update payment
            $payment->member_id = $request->input('member_id');
            $payment->package_id = $request->input('package_id');
            $payment->amount = $request->input('amount');
            $payment->start_date = $request->input('start_date');
            $payment->end_date = $request->input('end_date');
            $payment->payment_method = $request->input('payment_method');
            $payment->payment_status = $request->input('payment_status');
            $payment->note = $request->input('note');
            $payment->save();

            // Load relationships
            $payment->load(['member', 'package']);

            return response()->json([
                'success' => true,
                'message' => 'Payment updated successfully',
                'data' => [
                    'payment' => [
                        'id' => $payment->id,
                        'member_id' => $payment->member_id,
                        'member_name' => $payment->member ? $payment->member->name . ' - ' . $payment->member->code : 'N/A',
                        'package_id' => $payment->package_id,
                        'package_name' => $payment->package ? $payment->package->name : 'N/A',
                        'amount' => $payment->amount,
                        'amount_formatted' => 'Rs' . number_format($payment->amount, 2),
                        'start_date' => $payment->start_date,
                        'start_date_formatted' => $payment->start_date ? date('M d, Y', strtotime($payment->start_date)) : 'N/A',
                        'end_date' => $payment->end_date,
                        'end_date_formatted' => $payment->end_date ? date('M d, Y', strtotime($payment->end_date)) : 'N/A',
                        'payment_method' => $payment->payment_method,
                        'payment_method_formatted' => ucfirst($payment->payment_method),
                        'payment_status' => $payment->payment_status,
                        'payment_status_formatted' => $payment->payment_status == 'paid' ? 'Paid' : ($payment->payment_status == 'pending' ? 'Pending' : 'Failed'),
                        'note' => $payment->note,
                        'user_id' => $payment->user_id,
                        'created_at' => $payment->created_at,
                        'updated_at' => $payment->updated_at,
                        'created_at_formatted' => $payment->created_at->diffForHumans()
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a payment
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id ?? $user->id;

            // Find payment
            $payment = Payment::where('id', $id)
                          ->where('user_id', $userId)
                          ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            // Delete payment
            $payment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Payment deleted successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete multiple payments
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSelected(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'payment_ids' => 'required|array',
                'payment_ids.*' => 'integer|exists:payments,id'
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

            // Find and delete payments
            $deletedCount = 0;
            foreach ($request->input('payment_ids') as $paymentId) {
                $payment = Payment::where('id', $paymentId)
                              ->where('user_id', $userId)
                              ->first();

                if ($payment) {
                    $payment->delete();
                    $deletedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} payment(s) deleted successfully",
                'data' => [
                    'deleted_count' => $deletedCount
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting payments',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
