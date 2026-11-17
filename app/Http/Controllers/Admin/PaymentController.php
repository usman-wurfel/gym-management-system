<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Member;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use PDF;

class PaymentController extends Controller
{
    function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || $user->user_id != null) {
                $this->middleware('permission:payment-list', ['only' => ['index','paymentDetail']]);
                $this->middleware('permission:payment-create', ['only' => ['create','store']]);
                $this->middleware('permission:payment-edit', ['only' => ['edit','update','updatePaymentStatus']]);
                $this->middleware('permission:payment-delete', ['only' => ['destroy','deleteSelectedPayments']]);
            }
            return $next($request);
        });
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Payments';
		return view('admin.payments.index',compact('title'));
    }

    public function pendingPayments()
    {
        $title = 'Pending Payments';
        return view('admin.payments.pending', compact('title'));
    }

    public function getPayments(Request $request){
        $columns = array(
            0 => 'id',
            1 => 'member_id',
            2 => 'package_id',
            3 => 'amount',
            4 => 'start_date',
            5 => 'end_date',
            6 => 'payment_method',
            7 => 'payment_status',
            8 => 'created_at',
            9 => 'action'
        );

        $id = auth()->user()->user_id ?? auth()->user()->id;
        
        // Get month and year filters
        $month = $request->input('month');
        $year = $request->input('year');
        
        // Build base query with date filters
        $baseQuery = Payment::where('user_id', $id);
        
        // Only apply filters if they are not empty and not just whitespace
        if (!empty(trim($month)) && !empty(trim($year))) {
            $baseQuery->whereMonth('created_at', $month)
                     ->whereYear('created_at', $year);
        } elseif (!empty(trim($month))) {
            $baseQuery->whereMonth('created_at', $month);
        } elseif (!empty(trim($year))) {
            $baseQuery->whereYear('created_at', $year);
        }
        
        $totalData = $baseQuery->count();
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value'))){
            $payments = $baseQuery->with(['member', 'package'])
                ->offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->get();
            $totalFiltered = $totalData;
        }else{
            $search = $request->input('search.value');
            $searchQuery = Payment::where('user_id', $id);
            
            // Apply date filters to search query as well
            if (!empty(trim($month)) && !empty(trim($year))) {
                $searchQuery->whereMonth('created_at', $month)
                           ->whereYear('created_at', $year);
            } elseif (!empty(trim($month))) {
                $searchQuery->whereMonth('created_at', $month);
            } elseif (!empty(trim($year))) {
                $searchQuery->whereYear('created_at', $year);
            }
            
            $searchQuery = $searchQuery->with(['member', 'package'])
                ->where(function($query) use ($search, $id) {
                    $query->whereHas('member', function($q) use ($search, $id) {
                        $q->where([['name', 'like', "%{$search}%"],['user_id', $id]])
                            ->orWhere([['code', 'like', "%{$search}%"],['user_id', $id]]);
                    })
                    ->orWhereHas('package', function($q) use ($search, $id) {
                        $q->where([['name', 'like', "%{$search}%"],['user_id', $id]]);
                    })
                    ->orWhere([['payment_method', 'like', "%{$search}%"],['user_id', $id]])
                    ->orWhere([['payment_status', 'like', "%{$search}%"],['user_id', $id]]);
                });
            
            $payments = $searchQuery->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = $searchQuery->count();
        }

        $data = array();

        if($payments){
            foreach($payments as $r){
                $edit_url = route('payments.edit',$r->id);
                $nestedData['id'] = '<td><label class="checkbox checkbox-outline checkbox-success">
                                    <input type="checkbox" name="payments[]" value="'.$r->id.'"><span></span></label>
                                    </td>';
                $nestedData['member_id'] = $r->member ? $r->member->name .' - '. $r->member->code : 'N/A';
                $nestedData['package_id'] = $r->package ? $r->package->name : 'N/A';
                $nestedData['amount'] = 'Rs' . number_format($r->amount, 2);
                $nestedData['start_date'] = $r->start_date ? date('M d, Y', strtotime($r->start_date)) : 'N/A';
                $nestedData['end_date'] = $r->end_date ? date('M d, Y', strtotime($r->end_date)) : 'N/A';
                $nestedData['payment_method'] = ucfirst($r->payment_method);
                $nestedData['payment_status'] = $r->payment_status == 'paid' ? '<span class="badge badge-success">Paid</span>' : '<span class="badge badge-warning">Pending</span>';
                $nestedData['created_at'] = $r->created_at->diffForHumans();
                
                $auth_user = Auth::user();
                $edit = '';
                $delete = '';
                $show = '';

                if ($auth_user->can('payment-list')) {
                    $show = ' <a class="btn btn-sm btn-icon btn-bg-light btn-icon-primary btn-hover-primary" onclick="event.preventDefault();viewInfo('.$r->id.');" title="View Payment" href="javascript:void(0)">
                        <i class="flaticon-eye"></i>
                    </a>';
                }
                if ($auth_user->can('payment-edit')) {
                    $edit = ' <a title="Edit Payment" class="btn btn-sm btn-icon btn-bg-light btn-icon-success btn-hover-success"
                        href="'.$edit_url.'">
                        <i class="flaticon-edit"></i>
                    </a>      <a class="btn btn-sm btn-icon btn-bg-light btn-icon-warning btn-hover-warning" onclick="event.preventDefault();updateStatus('.$r->id.', \''.$r->payment_status.'\');" title="Update Status" href="javascript:void(0)">
                            <i class="fas fa-edit"></i>
                        </a>';
                }
                if ($auth_user->can('payment-delete')) {
                    $delete = '<a class="btn btn-sm btn-icon btn-bg-light btn-icon-danger btn-hover-danger" onclick="event.preventDefault();del('.$r->id.');" title="Delete Payment" href="javascript:void(0)">
                        <i class="flaticon-delete"></i>
                    </a>';
                }

                $nestedData['action'] = '
                    <div>
                    <td>
                        '.$show.'
                        '.$edit.'
                        '.$delete.'
                        <a class="btn btn-sm btn-icon btn-bg-light btn-icon-info btn-hover-info" onclick="event.preventDefault();printInvoice('.$r->id.');" title="Print Invoice" href="javascript:void(0)">
                            <i class="fas fa-print"></i>
                        </a>
                   
                    </td>
                    </div>
                ';
                $data[] = $nestedData;
            }
        }

        $json_data = array(
            "draw"			=> intval($request->input('draw')),
            "recordsTotal"	=> intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"			=> $data
        );

        echo json_encode($json_data);
    }

    public function getPendingPayments(Request $request){
        $columns = array(
            0 => 'id',
            1 => 'member_id',
            2 => 'package_id',
            3 => 'amount',
            4 => 'start_date',
            5 => 'end_date',
            6 => 'payment_method',
            7 => 'payment_status',
            8 => 'created_at',
            9 => 'action'
        );
        $id = auth()->user()->user_id ?? auth()->user()->id;
        
        // Get month and year filters
        $month = $request->input('month');
        $year = $request->input('year');
        
        // Build base query with date filters
        $baseQuery = Payment::where('payment_status', 'pending')->where('user_id', $id);
        
        // Only apply filters if they are not empty and not just whitespace
        if (!empty(trim($month)) && !empty(trim($year))) {
            $baseQuery->whereMonth('created_at', $month)
                     ->whereYear('created_at', $year);
        } elseif (!empty(trim($month))) {
            $baseQuery->whereMonth('created_at', $month);
        } elseif (!empty(trim($year))) {
            $baseQuery->whereYear('created_at', $year);
        }
        
        $totalData = $baseQuery->count();
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value'))){
            $payments = $baseQuery->with(['member', 'package'])
                ->offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->get();
            $totalFiltered = $totalData;
        }else{
            $search = $request->input('search.value');
            $searchQuery = Payment::where('payment_status', 'pending')->where('user_id', $id);
            
            // Apply date filters to search query as well
            if (!empty(trim($month)) && !empty(trim($year))) {
                $searchQuery->whereMonth('created_at', $month)
                           ->whereYear('created_at', $year);
            } elseif (!empty(trim($month))) {
                $searchQuery->whereMonth('created_at', $month);
            } elseif (!empty(trim($year))) {
                $searchQuery->whereYear('created_at', $year);
            }
            
            $searchQuery = $searchQuery->with(['member', 'package'])
                ->where(function($query) use ($search, $id) {
                    $query->whereHas('member', function($q) use ($search, $id) {
                        $q->where([['name', 'like', "%{$search}%"],['user_id', $id]])
                        ->orWhere([['code', 'like', "%{$search}%"],['user_id', $id]]);
                    })
                    ->orWhereHas('package', function($q) use ($search, $id) {
                        $q->where([['name', 'like', "%{$search}%"],['user_id', $id]]);
                    })
                    ->orWhere([['payment_method', 'like', "%{$search}%"],['user_id', $id]]);
                });
            
            $payments = $searchQuery->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = $searchQuery->count();
        }

        $data = array();

        if($payments){
            foreach($payments as $r){
                $edit_url = route('payments.edit',$r->id);
                $nestedData['id'] = '<td><label class="checkbox checkbox-outline checkbox-success">
                                    <input type="checkbox" name="payments[]" value="'.$r->id.'"><span></span></label>
                                    </td>';
                $nestedData['member_id'] = $r->member ? $r->member->name .' - '. $r->member->code : 'N/A';
                $nestedData['package_id'] = $r->package ? $r->package->name : 'N/A';
                $nestedData['amount'] = 'Rs' . number_format($r->amount, 2);
                $nestedData['start_date'] = $r->start_date ? date('M d, Y', strtotime($r->start_date)) : 'N/A';
                $nestedData['end_date'] = $r->end_date ? date('M d, Y', strtotime($r->end_date)) : 'N/A';
                $nestedData['payment_method'] = ucfirst($r->payment_method);
                $nestedData['payment_status'] = '<span class="badge badge-warning">Pending</span>';
                $nestedData['created_at'] = $r->created_at->diffForHumans();
                
                $auth_user = Auth::user();
                $edit = '';
                $delete = '';
                $show = '';

                if ($auth_user->can('payment-list')) {
                    $show = ' <a class="btn btn-sm btn-icon btn-bg-light btn-icon-primary btn-hover-primary" onclick="event.preventDefault();viewInfo('.$r->id.');" title="View Payment" href="javascript:void(0)">
                        <i class="flaticon-eye"></i>
                    </a>';
                }
                if ($auth_user->can('payment-edit')) {
                    $edit = ' <a title="Edit Payment" class="btn btn-sm btn-icon btn-bg-light btn-icon-success btn-hover-success"
                        href="'.$edit_url.'">
                        <i class="flaticon-edit"></i>
                    </a>  <a class="btn btn-sm btn-icon btn-bg-light btn-icon-warning btn-hover-warning" onclick="event.preventDefault();updateStatus('.$r->id.', \''.$r->payment_status.'\');" title="Update Status" href="javascript:void(0)">
                            <i class="fas fa-edit"></i>
                        </a>';
                }
                if ($auth_user->can('payment-delete')) {
                    $delete = '<a class="btn btn-sm btn-icon btn-bg-light btn-icon-danger btn-hover-danger" onclick="event.preventDefault();del('.$r->id.');" title="Delete Payment" href="javascript:void(0)">
                        <i class="flaticon-delete"></i>
                    </a>';
                }

                $nestedData['action'] = '
                    <div>
                    <td>
                        '.$show.'
                        '.$edit.'
                        '.$delete.'
                        <a class="btn btn-sm btn-icon btn-bg-light btn-icon-info btn-hover-info" onclick="event.preventDefault();printInvoice('.$r->id.');" title="Print Invoice" href="javascript:void(0)">
                            <i class="fas fa-print"></i>
                        </a>
                       
                    </td>
                    </div>
                ';
                $data[] = $nestedData;
            }
        }

        $json_data = array(
            "draw"			=> intval($request->input('draw')),
            "recordsTotal"	=> intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"			=> $data
        );

        echo json_encode($json_data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Add New Payment';
        $id = auth()->user()->user_id ?? auth()->user()->id;
        $members = Member::where('user_id',$id)->get();
        $packages = Package::where('user_id',$id)->get();
        return view('admin.payments.create',compact('title', 'members', 'packages'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
		{
			$this->validate($request, [
				'member_id' => 'required|exists:members,id',
				'package_id' => 'required|exists:packages,id',
				'amount' => 'required|numeric|min:0',
				'start_date' => 'required|date',
				'end_date' => 'required|date|after:start_date',
				'payment_method' => 'required|in:cash,card,bank_transfer,online',
				'payment_status' => 'required|in:paid,pending,failed',
				'note' => 'nullable|string|max:500',
			]);

            $payment = new Payment();
            $payment->member_id = $request->input('member_id');
            $payment->package_id = $request->input('package_id');
            $payment->amount = $request->input('amount');
            $payment->start_date = $request->input('start_date');
            $payment->end_date = $request->input('end_date');
            $payment->payment_method = $request->input('payment_method');
            $payment->payment_status = $request->input('payment_status');
            $payment->note = $request->input('note');
            $payment->user_id = auth()->user()->user_id ?? auth()->user()->id;

			$payment->save();

			Session::flash('success_message', 'Success! Payment saved successfully!');

			return redirect()->back();
		}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $payment = Payment::with(['member', 'package'])->find($id);
        return view('admin.payments.single', ['title' => 'Payment detail', 'payment' => $payment]);
    }

    public function paymentDetail(Request $request)
    {
        $payment = Payment::with(['member', 'package'])->find($request->id);
        return view('admin.payments.detail', ['title' => 'Payment Detail','payment' => $payment]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $payment = Payment::find($id);
        $id = auth()->user()->user_id ?? auth()->user()->id;
        $members = Member::where('user_id',$id)->get();
        $packages = Package::where('user_id',$id)->get();
        $title="Edit Payment";
        return view('admin.payments.edit',compact('title','payment', 'members', 'packages'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
		{
            $this->validate($request, [
                'member_id' => 'required|exists:members,id',
                'package_id' => 'required|exists:packages,id',
                'amount' => 'required|numeric|min:0',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'payment_method' => 'required|in:cash,card,bank_transfer,online',
                'payment_status' => 'required|in:paid,pending,failed',
                'note' => 'nullable|string|max:500',
            ]);
            $payment = Payment::findOrFail($id);
            $payment->member_id = $request->input('member_id');
            $payment->package_id = $request->input('package_id');
            $payment->amount = $request->input('amount');
            $payment->start_date = $request->input('start_date');
            $payment->end_date = $request->input('end_date');
            $payment->payment_method = $request->input('payment_method');
            $payment->payment_status = $request->input('payment_status');
            $payment->note = $request->input('note');
            $payment->user_id = auth()->user()->user_id ?? auth()->user()->id;
            $payment->save();
			Session::flash('success_message', 'Success! Payment successfully updated!');
			return redirect()->back();
		}

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
		{
			$payment = Payment::find($id);

				$payment->delete();
				Session::flash('success_message', 'Payment successfully deleted!');

			return redirect()->route('payments.index');

		}

		public function deleteSelectedPayments(Request $request)
		{
			$input = $request->all();
			$this->validate($request, [
				'payments' => 'required',

			]);
			foreach ($input['payments'] as $index => $id) {

				$payment = Payment::find($id);

				$payment->delete();

			}
			Session::flash('success_message', 'Payments successfully deleted!');
			return redirect()->back();

		}

    /**
     * Print invoice for a payment
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function printInvoice($id)
    {
        $payment = Payment::with(['member', 'package'])->findOrFail($id);
        
        // Get settings for company information
        $settings = \App\Models\Setting::pluck('value', 'name')->toArray();
        
        $data = [
            'payment' => $payment,
            'settings' => $settings,
            'invoice_number' => 'INV-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d')
        ];

        $pdf = PDF::loadView('admin.payments.invoice', $data);
        
        $filename = 'invoice_' . $data['invoice_number'] . '_' . $payment->member->name . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Update payment status
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatePaymentStatus(Request $request)
    {
        $this->validate($request, [
            'payment_id' => 'required|exists:payments,id',
            'payment_status' => 'required|in:paid,pending,failed',
            'note' => 'nullable|string|max:500',
        ]);

        $payment = Payment::findOrFail($request->payment_id);
        
        // Check if user has permission to update this payment
        $userId = auth()->user()->user_id ?? auth()->user()->id;
        if ($payment->user_id != $userId) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $oldStatus = $payment->payment_status;
        $payment->payment_status = $request->payment_status;
        
        // Update note if provided
        if ($request->note) {
            $payment->note = $request->note;
        }
        
        $payment->save();

        return response()->json([
            'message' => 'Payment status updated successfully',
            'old_status' => $oldStatus,
            'new_status' => $payment->payment_status
        ]);
    }
} 