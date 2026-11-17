<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Package;
use App\Models\MemberWorkout;
use App\Models\WorkoutType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || $user->user_id != null) {
                $this->middleware('permission:member-list', ['only' => ['index','memberDetail','memberPaymentHistory']]);
                $this->middleware('permission:member-create', ['only' => ['create','store']]);
                $this->middleware('permission:member-edit', ['only' => ['edit','update']]);
                $this->middleware('permission:member-delete', ['only' => ['destroy','deleteSelectedMembers']]);
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
        $title = 'Members';
		return view('admin.members.index',compact('title'));
    }

    public function getMembers(Request $request){
        $columns = array(
            0 => 'id',
            1 => 'name',
            2 => 'code',
            3 => 'phone',
            4 => 'package_id',
            5 => 'start_date',
            6 => 'status',
            7 => 'created_at',
            8 => 'workout',
            9 => 'action'
        );
        $id = auth()->user()->user_id ?? auth()->user()->id;
        $totalData = Member::where('user_id',$id)->count();
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value'))){
            $members = Member::with('package')
                ->where('user_id',$id)
                ->offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->get();
            $totalFiltered = Member::where('user_id',$id)->count();
        }else{
            $search = $request->input('search.value');
            $members = Member::with('package')
                ->where([['name', 'like', "%{$search}%"],['user_id',$id]])
                ->orWhere([['code', 'like', "%{$search}%"],['user_id',$id]])
                ->orWhere([['phone', 'like', "%{$search}%"],['user_id',$id]])
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Member::where([['name', 'like', "%{$search}%"],['user_id',$id]])
                ->orWhere([['code', 'like', "%{$search}%"],['user_id',$id]])
                ->orWhere([['phone', 'like', "%{$search}%"],['user_id',$id]])
                ->count();
        }

        $data = array();

        if($members){
            foreach($members as $r){
                $edit_url = route('members.edit',$r->id);
                $nestedData['id'] = '<td><label class="checkbox checkbox-outline checkbox-success">
                                    <input type="checkbox" name="members[]" value="'.$r->id.'"><span></span></label>
                                    </td>';
                $nestedData['name'] = $r->name;
                $nestedData['code'] = $r->code;
                $nestedData['phone'] = $r->phone;
                $nestedData['package_id'] = $r->package ? $r->package->name : 'N/A';
                $nestedData['start_date'] = $r->start_date ? date('M d, Y', strtotime($r->start_date)) : 'N/A';
                $nestedData['status'] = $r->status == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
                $nestedData['created_at'] = $r->created_at->diffForHumans();
                
                // Workout assignment button
                $workout_assignment = '<a class="btn btn-sm btn-icon btn-bg-light btn-icon-warning btn-hover-warning" onclick="event.preventDefault();assignWorkout('.$r->id.');" title="Assign Workout" href="javascript:void(0)">
                    <i class="fas fa-dumbbell"></i>
                </a>';
                
                $nestedData['workout'] = $workout_assignment;
                
                $auth_user = Auth::user();
                $edit = '';
                $delete = '';
                $show = '';

                if ($auth_user->can('member-list')) {
                    $show = ' <a class="btn btn-sm btn-icon btn-bg-light btn-icon-primary btn-hover-primary" onclick="event.preventDefault();viewInfo('.$r->id.');" title="View Member" href="javascript:void(0)">
                        <i class="flaticon-eye"></i>
                    </a>';
                }
                if ($auth_user->can('member-edit')) {
                    $edit = ' <a title="Edit Member" class="btn btn-sm btn-icon btn-bg-light btn-icon-success btn-hover-success"
                        href="'.$edit_url.'">
                        <i class="flaticon-edit"></i>
                    </a>';
                }
                if ($auth_user->can('member-delete')) {
                    $delete = '<a class="btn btn-sm btn-icon btn-bg-light btn-icon-danger btn-hover-danger" onclick="event.preventDefault();del('.$r->id.');" title="Delete Member" href="javascript:void(0)">
                        <i class="flaticon-delete"></i>
                    </a>';
                }

                $payment_history = '<a class="btn btn-sm btn-icon btn-bg-light btn-icon-info btn-hover-info" onclick="event.preventDefault();viewPaymentHistory('.$r->id.');" title="Payment History" href="javascript:void(0)">
                    <i class="fas fa-credit-card"></i>
                </a>';

                $nestedData['action'] = '
                    <div>
                    <td>
                        '.$show.'
                        '.$edit.'
                        '.$payment_history.'
                        '.$delete.'
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
        $title = 'Add New Member';
        $id = auth()->user()->user_id ?? auth()->user()->id;
        $packages = Package::where('user_id',$id)->get();
        return view('admin.members.create',compact('title', 'packages'));
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
			'name' => 'required|max:255',
			'email' => 'nullable|email|unique:members,email',
			'code' => 'required|unique:members,code',
			'phone' => 'required|string|max:20',
			'address' => 'required|string',
			'city' => 'required|string',
			'package_id' => 'required|exists:packages,id',
			'start_date' => 'required|date',
			'status' => 'required|in:0,1',
			'password' => 'nullable|string|min:6|confirmed',
		]);

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
            $member->user_id = auth()->user()->user_id ?? auth()->user()->id;
            
            // Handle password
            if ($request->filled('password')) {
                $member->password = bcrypt($request->input('password'));
            }
            
			$member->save();

			Session::flash('success_message', 'Success! Member saved successfully!');

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
        $member = Member::with('package')->find($id);
        return view('admin.members.single', ['title' => 'Member detail', 'member' => $member]);
    }

    public function memberDetail(Request $request)
    {
        $member = Member::with('package')->find($request->id);
        return view('admin.members.detail', ['title' => 'Member Detail','member' => $member]);
    }

    public function memberPaymentHistory(Request $request)
    {
        $member = Member::with(['package', 'payments'])->find($request->id);
        return view('admin.members.payment_history', ['title' => 'Member Payment History','member' => $member]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $member = Member::find($id);
        $id = auth()->user()->user_id ?? auth()->user()->id;
        $packages = Package::where('user_id',$id)->get();
        $title="Edit Member";
        return view('admin.members.edit',compact('title','member', 'packages'));
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
                'name' => 'required|max:255',
                'email' => 'nullable|email|unique:members,email,'.$id,
                'code' => 'required|unique:members,code,'.$id,
                'phone' => 'required|string|max:20',
                'address' => 'required|string',
                'city' => 'required|string',
                'package_id' => 'required|exists:packages,id',
                'start_date' => 'required|date',
                'status' => 'required|in:0,1',
                'password' => 'nullable|string|min:6|confirmed',
            ]);
            $member = Member::findOrFail($id);
            $member->name = $request->input('name');
            $member->code = $request->input('code');
            $member->email = $request->input('email');
            $member->phone = $request->input('phone');
            $member->address = $request->input('address');
            $member->city = $request->input('city');
            $member->package_id = $request->input('package_id');
            $member->start_date = $request->input('start_date');
            $member->status = $request->input('status');
            
            // Handle password update
            if ($request->filled('password')) {
                $member->password = bcrypt($request->input('password'));
            }
            
            $member->save();
			Session::flash('success_message', 'Success! Member successfully updated!');
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
			$member = Member::find($id);
            $member->payments()->delete();
				$member->delete();

				Session::flash('success_message', 'Member successfully deleted!');

			return redirect()->route('members.index');

		}

		public function deleteSelectedMembers(Request $request)
		{
			$input = $request->all();
			$this->validate($request, [
				'members' => 'required',

			]);
			foreach ($input['members'] as $index => $id) {

				$member = Member::find($id);
                $member->payments()->delete();
				$member->delete();

			}
			Session::flash('success_message', 'Members successfully deleted!');
			return redirect()->back();

		}

    /**
     * Show import form
     */
    public function showImportForm()
    {
        $title = 'Import Members';
        return view('admin.members.import', compact('title'));
    }

    /**
     * Download sample file
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
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
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

            Session::flash('success_message', $message);
            
            if (!empty($errors)) {
                Session::flash('import_errors', $errors);
            }

            return redirect()->route('members.index');

        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('error_message', 'Import failed: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Process a single import row
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

    /**
     * Show workout assignment page for a member
     */
    public function showWorkoutAssignment($id)
    {
        $member = Member::where('id', $id)
            ->where('user_id', auth()->user()->user_id ?? auth()->user()->id)
            ->firstOrFail();

        $title = 'Workout Assignment - ' . $member->name;
        return view('admin.members.workout-assignment', compact('title', 'member'));
    }

    /**
     * Show workout assignment modal
     */
    public function showWorkoutAssignmentModal(Request $request)
    {
        $memberId = $request->input('member_id');
        $member = Member::where('id', $memberId)
            ->where('user_id', auth()->user()->user_id ?? auth()->user()->id)
            ->firstOrFail();

        $workoutTypes = WorkoutType::where('user_id', auth()->user()->user_id ?? auth()->user()->id)
            ->orderBy('name')
            ->get();

        return view('admin.members.workout-assignment-modal', compact('member', 'workoutTypes'));
    }

    /**
     * Store workout assignment
     */
    public function storeWorkoutAssignment(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'workout_type_id' => 'required|exists:workout_types,id',
            'assigned_date' => 'required|date',
            'notes' => 'nullable|string|max:1000'
        ]);

        // Check if member belongs to current user
        $member = Member::where('id', $request->member_id)
            ->where('user_id', auth()->user()->user_id ?? auth()->user()->id)
            ->firstOrFail();

        MemberWorkout::create([
            'member_id' => $request->member_id,
            'workout_type_id' => $request->workout_type_id,
            'assigned_date' => $request->assigned_date,
            'notes' => $request->notes,
            'user_id' => auth()->user()->user_id ?? auth()->user()->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Workout assigned successfully!'
        ]);
    }

    /**
     * Get member's workout assignments
     */
    public function getMemberWorkouts(Request $request)
    {
        $memberId = $request->input('member_id');
        $member = Member::where('id', $memberId)
            ->where('user_id', auth()->user()->user_id ?? auth()->user()->id)
            ->firstOrFail();

        $workouts = MemberWorkout::where('member_id', $memberId)
            ->with('workoutType')
            ->orderBy('assigned_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $workouts
        ]);
    }

    /**
     * Delete workout assignment
     */
    public function deleteWorkoutAssignment(Request $request)
    {
        $request->validate([
            'workout_assignment_id' => 'required|exists:member_workouts,id'
        ]);

        $workoutAssignment = MemberWorkout::where('id', $request->workout_assignment_id)
            ->where('user_id', auth()->user()->user_id ?? auth()->user()->id)
            ->firstOrFail();

        $workoutAssignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Workout assignment deleted successfully!'
        ]);
    }
} 