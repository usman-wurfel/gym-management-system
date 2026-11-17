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
use PDF;
use Excel;

class ReportController extends Controller
{
    /**
     * Constructor with middleware and permission checks
     */
    function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || $user->user_id != null) {
                $this->middleware('permission:payment-list', ['only' => ['generateReport', 'downloadPDF', 'downloadExcel']]);
            }
            return $next($request);
        });
    }

    /**
     * Generate report based on filters
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generateReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'payment_status' => 'required|in:all,paid,pending,failed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $paymentStatus = $request->input('payment_status');

        $query = Payment::with(['member', 'package'])
            ->where('user_id', auth()->user()->user_id ?? auth()->user()->id)
            ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);

        if ($paymentStatus !== 'all') {
            $query->where('payment_status', $paymentStatus);
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        // Calculate summary statistics
        $totalAmount = $payments->sum('amount');
        $paidAmount = $payments->where('payment_status', 'paid')->sum('amount');
        $pendingAmount = $payments->where('payment_status', 'pending')->sum('amount');
        $failedAmount = $payments->where('payment_status', 'failed')->sum('amount');
        
        $totalCount = $payments->count();
        $paidCount = $payments->where('payment_status', 'paid')->count();
        $pendingCount = $payments->where('payment_status', 'pending')->count();
        $failedCount = $payments->where('payment_status', 'failed')->count();

        // Format payments for API response
        $formattedPayments = $payments->map(function ($payment) {
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
                'payment_status_formatted' => ucfirst($payment->payment_status),
                'note' => $payment->note,
                'user_id' => $payment->user_id,
                'created_at' => $payment->created_at,
                'updated_at' => $payment->updated_at,
                'created_at_formatted' => $payment->created_at ? $payment->created_at->diffForHumans() : 'N/A'
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Report generated successfully',
            'data' => [
                'payments' => $formattedPayments,
                'summary' => [
                    'total_amount' => $totalAmount,
                    'total_amount_formatted' => 'Rs' . number_format($totalAmount, 2),
                    'paid_amount' => $paidAmount,
                    'paid_amount_formatted' => 'Rs' . number_format($paidAmount, 2),
                    'pending_amount' => $pendingAmount,
                    'pending_amount_formatted' => 'Rs' . number_format($pendingAmount, 2),
                    'failed_amount' => $failedAmount,
                    'failed_amount_formatted' => 'Rs' . number_format($failedAmount, 2),
                    'total_count' => $totalCount,
                    'paid_count' => $paidCount,
                    'pending_count' => $pendingCount,
                    'failed_count' => $failedCount
                ],
                'filters' => [
                    'from_date' => $fromDate,
                    'to_date' => $toDate,
                    'payment_status' => $paymentStatus
                ]
            ]
        ]);
    }

    /**
     * Download PDF report
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function downloadPDF(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'payment_status' => 'required|in:all,paid,pending,failed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $paymentStatus = $request->input('payment_status');

        $query = Payment::with(['member', 'package'])
            ->where('user_id', auth()->user()->user_id ?? auth()->user()->id)
            ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);

        if ($paymentStatus !== 'all') {
            $query->where('payment_status', $paymentStatus);
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        // Calculate summary statistics
        $totalAmount = $payments->sum('amount');
        $paidAmount = $payments->where('payment_status', 'paid')->sum('amount');
        $pendingAmount = $payments->where('payment_status', 'pending')->sum('amount');
        $failedAmount = $payments->where('payment_status', 'failed')->sum('amount');
        
        $totalCount = $payments->count();
        $paidCount = $payments->where('payment_status', 'paid')->count();
        $pendingCount = $payments->where('payment_status', 'pending')->count();
        $failedCount = $payments->where('payment_status', 'failed')->count();

        $data = [
            'payments' => $payments,
            'summary' => [
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'pending_amount' => $pendingAmount,
                'failed_amount' => $failedAmount,
                'total_count' => $totalCount,
                'paid_count' => $paidCount,
                'pending_count' => $pendingCount,
                'failed_count' => $failedCount
            ],
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'payment_status' => $paymentStatus
            ]
        ];

        $pdf = PDF::loadView('admin.reports.pdf', $data);
        
        $filename = 'payment_report_' . $fromDate . '_to_' . $toDate . '_' . $paymentStatus . '.pdf';
        
        return response()->json([
            'success' => true,
            'message' => 'PDF report generated successfully',
            'data' => [
                'download_url' => route('api.reports.download-pdf', [
                    'from_date' => $fromDate,
                    'to_date' => $toDate,
                    'payment_status' => $paymentStatus
                ]),
                'filename' => $filename
            ]
        ]);
    }

    /**
     * Download Excel report
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function downloadExcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'payment_status' => 'required|in:all,paid,pending,failed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $paymentStatus = $request->input('payment_status');

        $query = Payment::with(['member', 'package'])
            ->where('user_id', auth()->user()->user_id ?? auth()->user()->id)
            ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);

        if ($paymentStatus !== 'all') {
            $query->where('payment_status', $paymentStatus);
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        // Calculate summary statistics
        $totalAmount = $payments->sum('amount');
        $paidAmount = $payments->where('payment_status', 'paid')->sum('amount');
        $pendingAmount = $payments->where('payment_status', 'pending')->sum('amount');
        $failedAmount = $payments->where('payment_status', 'failed')->sum('amount');
        
        $totalCount = $payments->count();
        $paidCount = $payments->where('payment_status', 'paid')->count();
        $pendingCount = $payments->where('payment_status', 'pending')->count();
        $failedCount = $payments->where('payment_status', 'failed')->count();

        $data = [
            'payments' => $payments,
            'summary' => [
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'pending_amount' => $pendingAmount,
                'failed_amount' => $failedAmount,
                'total_count' => $totalCount,
                'paid_count' => $paidCount,
                'pending_count' => $pendingCount,
                'failed_count' => $failedCount
            ],
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'payment_status' => $paymentStatus
            ]
        ];

        $filename = 'payment_report_' . $fromDate . '_to_' . $toDate . '_' . $paymentStatus . '.xlsx';

        return response()->json([
            'success' => true,
            'message' => 'Excel report generated successfully',
            'data' => [
                'download_url' => route('api.reports.download-excel', [
                    'from_date' => $fromDate,
                    'to_date' => $toDate,
                    'payment_status' => $paymentStatus
                ]),
                'filename' => $filename
            ]
        ]);
    }

    /**
     * Get dashboard statistics
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getDashboardStats(Request $request)
    {
        $userId = auth()->user()->user_id ?? auth()->user()->id;

        // Get today's date
        $today = now()->format('Y-m-d');
        
        // Get current month
        $currentMonth = now()->format('Y-m');
        $monthStart = now()->startOfMonth()->format('Y-m-d');
        $monthEnd = now()->endOfMonth()->format('Y-m-d');

        // Today's payments
        $todayPayments = Payment::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->get();

        $todayTotal = $todayPayments->sum('amount');
        $todayPaid = $todayPayments->where('payment_status', 'paid')->sum('amount');
        $todayPending = $todayPayments->where('payment_status', 'pending')->sum('amount');

        // Current month payments
        $monthPayments = Payment::where('user_id', $userId)
            ->whereBetween('created_at', [$monthStart . ' 00:00:00', $monthEnd . ' 23:59:59'])
            ->get();

        $monthTotal = $monthPayments->sum('amount');
        $monthPaid = $monthPayments->where('payment_status', 'paid')->sum('amount');
        $monthPending = $monthPayments->where('payment_status', 'pending')->sum('amount');

        // Total members
        $totalMembers = Member::where('user_id', $userId)->count();
        $activeMembers = Member::where('user_id', $userId)->where('status', 1)->count();

        // Total packages
        $totalPackages = Package::where('user_id', $userId)->count();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard statistics retrieved successfully',
            'data' => [
                'today' => [
                    'total_amount' => $todayTotal,
                    'total_amount_formatted' => 'Rs' . number_format($todayTotal, 2),
                    'paid_amount' => $todayPaid,
                    'paid_amount_formatted' => 'Rs' . number_format($todayPaid, 2),
                    'pending_amount' => $todayPending,
                    'pending_amount_formatted' => 'Rs' . number_format($todayPending, 2),
                    'total_count' => $todayPayments->count(),
                    'paid_count' => $todayPayments->where('payment_status', 'paid')->count(),
                    'pending_count' => $todayPayments->where('payment_status', 'pending')->count()
                ],
                'current_month' => [
                    'total_amount' => $monthTotal,
                    'total_amount_formatted' => 'Rs' . number_format($monthTotal, 2),
                    'paid_amount' => $monthPaid,
                    'paid_amount_formatted' => 'Rs' . number_format($monthPaid, 2),
                    'pending_amount' => $monthPending,
                    'pending_amount_formatted' => 'Rs' . number_format($monthPending, 2),
                    'total_count' => $monthPayments->count(),
                    'paid_count' => $monthPayments->where('payment_status', 'paid')->count(),
                    'pending_count' => $monthPayments->where('payment_status', 'pending')->count()
                ],
                'members' => [
                    'total' => $totalMembers,
                    'active' => $activeMembers,
                    'inactive' => $totalMembers - $activeMembers
                ],
                'packages' => [
                    'total' => $totalPackages
                ]
            ]
        ]);
    }
}
