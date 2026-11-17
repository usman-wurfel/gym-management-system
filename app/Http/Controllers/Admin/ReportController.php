<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Member;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;
use Excel;

class ReportController extends Controller
{
    function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || $user->user_id != null) {
                $this->middleware('permission:payment-list', ['only' => ['index', 'generateReport', 'downloadPDF']]);
            }
            return $next($request);
        });
    }

    /**
     * Display the reports page
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Payment Reports';
        return view('admin.reports.index', compact('title'));
    }

    /**
     * Generate report based on filters
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generateReport(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'payment_status' => 'required|in:all,paid,pending,failed'
        ]);

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

        return response()->json([
            'success' => true,
            'data' => [
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
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'payment_status' => 'required|in:all,paid,pending,failed'
        ]);

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $paymentStatus = $request->input('payment_status');

        $query = Payment::with(['member', 'package'])
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
        
        return $pdf->download($filename);
    }

    /**
     * Download Excel report
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function downloadExcel(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'payment_status' => 'required|in:all,paid,pending,failed'
        ]);

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $paymentStatus = $request->input('payment_status');

        $query = Payment::with(['member', 'package'])
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

        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithMapping, \Maatwebsite\Excel\Concerns\WithStyles, \Maatwebsite\Excel\Concerns\WithTitle {
            private $data;

            public function __construct($data) {
                $this->data = $data;
            }

            public function collection() {
                return $this->data['payments'];
            }

            public function headings(): array {
                return [
                    'Member Name',
                    'Package',
                    'Amount',
                    'Payment Method',
                    'Status',
                    'Start Date',
                    'End Date',
                    'Created Date'
                ];
            }

            public function map($payment): array {
                return [
                    $payment->member ? $payment->member->name : 'N/A',
                    $payment->package ? $payment->package->name : 'N/A',
                    number_format($payment->amount, 2),
                    ucfirst($payment->payment_method),
                    ucfirst($payment->payment_status),
                    $payment->start_date ? date('Y-m-d', strtotime($payment->start_date)) : 'N/A',
                    $payment->end_date ? date('Y-m-d', strtotime($payment->end_date)) : 'N/A',
                    $payment->created_at ? date('Y-m-d H:i:s', strtotime($payment->created_at)) : 'N/A'
                ];
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet) {
                return [
                    1 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'E2EFDA']]],
                ];
            }

            public function title(): string {
                return 'Payment Report';
            }
        }, $filename);
    }
} 