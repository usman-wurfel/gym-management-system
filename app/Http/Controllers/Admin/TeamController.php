<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PlayerWorkout;
use App\Models\Workout;
use App\Notifications\WorkoutAssignedNotification;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Notification;
use DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\Member;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || $user->super_admin == 0) {
                return redirect()->route('admin.dashboard');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $title = 'Business';
        return view('admin.team.index',compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

	public function getTeam(Request $request){
		$columns = array(
			0 => 'id',
			1 => 'name',
			2 => 'email',
			3 => 'active',
			4 => 'created_at',
			5 => 'action'
		);

		$totalData = User::where([['is_admin',1],['super_admin',0],['user_id',null]])->count();
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');

		if(empty($request->input('search.value'))){
			$users = User::where([['is_admin',1],['super_admin',0],['user_id',null]])->offset($start)
				->limit($limit)
				->orderBy($order,$dir)
				->get();
			$totalFiltered = User::where([['is_admin',1],['super_admin',0],['user_id',null]])->count();
		}else{
			$search = $request->input('search.value');
			$users = User::where([
				['is_admin',1],
				['super_admin',0],
				['user_id',null],
				['name', 'like', "%{$search}%"],
			])
				->orWhere([
					['is_admin',1],
					['super_admin',0],
					['user_id',null],
					['email', 'like', "%{$search}%"],
				])
				->orWhere([
					['is_admin',1],
					['super_admin',0],
					['user_id',null],
					['created_at', 'like', "%{$search}%"],
				])
				->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
			$totalFiltered = User::where([
				['is_admin',1],
				['super_admin',0],
				['user_id',null],
				['name', 'like', "%{$search}%"],
			])
				->orWhere([
					['is_admin',1],
					['super_admin',0],
					['user_id',null],
					['email', 'like', "%{$search}%"],
				])
				->orWhere([
					['is_admin',1],
					['super_admin',0],
					['user_id',null],
					['name', 'like', "%{$search}%"],
				])
				->orWhere([
					['is_admin',1],
					['super_admin',0],
					['user_id',null],
					['created_at', 'like', "%{$search}%"],
				])
				->count();
		}


		$data = array();

		if($users){
			foreach($users as $r){
				$edit_url = route('team.edit',$r->id);
				$nestedData['id'] = '<td><label class="checkbox checkbox-outline checkbox-success"><input type="checkbox" name="users[]" value="'.$r->id.'"><span></span></label></td>';
				$nestedData['name'] = $r->name;
				$nestedData['email'] = $r->email;
				if($r->active){
					$nestedData['active'] = '<span class="label label-success label-inline mr-2">Active</span>';
				}else{
					$nestedData['active'] = '<span class="label label-danger label-inline mr-2">Inactive</span>';
				}
				$auth_user = Auth::user();
                $edit = '';
                $delete = '';
                $show = '';
                $history = '';
                    $show = ' <a class="btn btn-sm btn-icon btn-bg-light btn-icon-primary btn-hover-primary" onclick="event.preventDefault();viewInfo('.$r->id.');" title="View " href="javascript:void(0)">
                                        <i class="flaticon-eye"></i>
                                    </a>';
                    $edit = ' <a title="Edit " class="btn btn-sm btn-icon btn-bg-light btn-icon-success btn-hover-success"
                                       href="'.$edit_url.'">
                                       <i class="flaticon-edit"></i>
                                    </a>';
              
                    $delete = '<a class="btn btn-sm btn-icon btn-bg-light btn-icon-danger btn-hover-danger" onclick="event.preventDefault();del('.$r->id.');" title="Delete " href="javascript:void(0)">
                                        <i class="flaticon-delete"></i>
                                    </a>';

				$nestedData['created_at'] = date('d-m-Y',strtotime($r->created_at));
				                				$nestedData['action'] = '
                                <div>
                                <td>
                                   '.$show.'
                                   '.$edit.'
                                   '.$delete.'
                                   <a class="btn btn-sm btn-icon btn-bg-light btn-icon-info btn-hover-info" onclick="event.preventDefault();subscribe('.$r->id.');" title="Subscribe" href="javascript:void(0)">
                                        <i class="fa fa-plus"></i>
                                    </a>
                                   <a class="btn btn-sm btn-icon btn-bg-light btn-icon-warning btn-hover-warning" onclick="event.preventDefault();showMembers('.$r->id.');" title="Members" href="javascript:void(0)">
                                        <i class="fa fa-users"></i>
                                    </a>
                                   <a class="btn btn-sm btn-icon btn-bg-light btn-icon-primary btn-hover-primary" onclick="event.preventDefault();showPackages('.$r->id.');" title="Packages" href="javascript:void(0)">
                                        <i class="fa fa-box"></i>
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
    public function create()
    {
	    $title = 'Add New Team';
	    return view('admin.team.create',compact('title'));
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
		    'email' => 'required|unique:users,email',
		    'password' => 'nullable|min:6',
			'image' => 'nullable|image|mimes:jpeg,png,jpg,gif'
	    ]);

	    $input = $request->all();
	    $user = new User();
	    $user->name = $input['name'];
	    $user->email = $input['email'];
	    $user->is_admin = 1;
	    $user->super_admin = 0;
	    $res = array_key_exists('active', $input);
	    if ($res == false) {
		    $user->active = 0;
	    } else {
		    $user->active = 1;
	    }
		if($request->hasFile('image')){
			$image = $request->file('image');
			$imageName = time().'.'.$image->getClientOriginalExtension();
			$image->move(public_path('images'), $imageName);
			$user->image = $imageName;
		}
		if(!empty($input['password'])) {
			$user->password = bcrypt($input['password']);
		}
	    
	   
	    
	    $user->save();
	    $user->assignRole('Admin');
	    
	    Session::flash('success_message', 'Great! User has been saved successfully!');
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
	    $user = User::find($id);
	    return view('admin.team.show', [
	        'title' => 'Business Details', 
	        'user' => $user,
	    ]);
    }

	public function teamDetail(Request $request)
	{

		$user = User::findOrFail($request->id);


		return view('admin.team.detail', ['title' => 'Business Detail', 'user' => $user]);
	}

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
	    $user = User::find($id);
	    return view('admin.team.edit', ['title' => 'Edit Business details'])->withUser($user);
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
	    $user = User::find($id);
	    $this->validate($request, [
		    'name' => 'required|max:255',
		    'email' => 'required|unique:users,email,'.$user->id,
			'image' => 'nullable|image|mimes:jpeg,png,jpg,gif'
	    ]);
	    $input = $request->all();

	    $user->name = $input['name'];
	    $user->email = $input['email'];
	    $user->is_admin = 1;
	    $user->super_admin = 0;
	    $res = array_key_exists('active', $input);
	    if ($res == false) {
		    $user->active = 0;
	    } else {
		    $user->active = 1;

	    }
	    if(!empty($input['password'])) {
		    $user->password = bcrypt($input['password']);
	    }
		if($request->hasFile('image')){
			$image = $request->file('image');
			$imageName = time().'.'.$image->getClientOriginalExtension();
			$image->move(public_path('images'), $imageName);
			$user->image = $imageName;
		}
	    $user->save();
		$user->assignRole('Admin');
	    Session::flash('success_message', 'Great! Business successfully updated!');
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
	    $user = User::find($id);
			$user->delete();
		    Session::flash('success_message', 'Business successfully deleted!');
	    return redirect()->route('team.index');

    }
	public function deleteSelectedTeam(Request $request)
	{
		$input = $request->all();
		$this->validate($request, [
			'users' => 'required',

		]);
		foreach ($input['users'] as $index => $id) {

			$user = User::find($id);
			$user->delete();

		}
		Session::flash('success_message', 'Businesses successfully deleted!');
		return redirect()->back();

	}

	/**
     * Show subscription modal for business owner
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
	public function showSubscriptionModal(Request $request)
	{
		$user = User::findOrFail($request->user_id);
		$packages = \App\Models\SubscriptionPackage::where('active', true)->get();
		
		return view('admin.team.subscription-modal', [
			'user' => $user,
			'packages' => $packages
		]);
	}

	/**
     * Store subscription for business owner
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
	public function storeSubscription(Request $request)
	{
		$request->validate([
			'user_id' => 'required|exists:users,id',
			'package_id' => 'required|exists:subscription_packages,id',
			'start_date' => 'required|date',
		]);

		$user = User::findOrFail($request->user_id);
		$package = \App\Models\SubscriptionPackage::findOrFail($request->package_id);
		
		// Calculate end date based on package duration
		$startDate = \Carbon\Carbon::parse($request->start_date);
		$endDate = $startDate->copy()->addDays($package->duration);

		// Create subscription history
		$subscription = new \App\Models\SubscribtionHistory();
		$subscription->user_id = $user->id;
		$subscription->package_id = $package->id;
		$subscription->start_date = $startDate;
		$subscription->end_date = $endDate;
		$subscription->price = $package->price;
		$subscription->save();

		// Send email notification to user
		try {
			$this->sendSubscriptionEmail($user, $package, $startDate, $endDate);
		} catch (\Exception $e) {
			// Log error but don't fail the subscription creation
			\Log::error('Failed to send subscription email: ' . $e->getMessage());
		}

		return response()->json([
			'success' => true,
			'message' => 'Subscription created successfully!'
		]);
	}

	/**
     * Send subscription email to user
     *
     * @param \App\Models\User $user
     * @param \App\Models\SubscriptionPackage $package
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return void
     */
	private function sendSubscriptionEmail($user, $package, $startDate, $endDate)
	{
		// Get settings for email configuration
		$setting = \App\Models\Setting::pluck('value','name')->toArray();
		$siteTitle = isset($setting['site_title']) ? $setting['site_title'] : 'Gym Management System';
		$adminEmail = isset($setting['email']) ? $setting['email'] : 'admin@domain.com';
		$adminPhone = isset($setting['phone']) ? $setting['phone'] : 'N/A';

		$subject = "Subscription Activated - {$siteTitle}";
		
		$message = "
		<html>
		<head>
			<style>
				body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
				.container { max-width: 600px; margin: 0 auto; padding: 20px; }
				.header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
				.content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
				.details { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #667eea; }
				.footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
				.btn { display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
				.highlight { color: #667eea; font-weight: bold; }
			</style>
		</head>
		<body>
			<div class='container'>
				<div class='header'>
					<h1>🎉 Subscription Activated!</h1>
					<p>Welcome to {$siteTitle}</p>
				</div>
				
				<div class='content'>
					<h2>Hello {$user->name},</h2>
					
					<p>Great news! Your subscription has been successfully activated. You now have full access to our gym management system.</p>
					
					<div class='details'>
						<h3>📋 Subscription Details:</h3>
						<p><strong>Package:</strong> <span class='highlight'>{$package->name}</span></p>
						<p><strong>Price:</strong> <span class='highlight'>\${$package->price}</span></p>
						<p><strong>Start Date:</strong> <span class='highlight'>{$startDate->format('M d, Y')}</span></p>
						<p><strong>End Date:</strong> <span class='highlight'>{$endDate->format('M d, Y')}</span></p>
						<p><strong>Duration:</strong> <span class='highlight'>{$package->duration} days</span></p>
					</div>
					
					<div class='details'>
						<h3>📝 Package Features:</h3>
						<p>" . nl2br(e($package->features)) . "</p>
					</div>
					
					<p>You can now access all the features included in your subscription package. If you have any questions or need assistance, please don't hesitate to contact us.</p>
					
					<div class='details'>
						<h3>📞 Contact Information:</h3>
						<p><strong>Phone:</strong> {$adminPhone}</p>
						<p><strong>Email:</strong> {$adminEmail}</p>
					</div>
					
					<p>Thank you for choosing {$siteTitle}!</p>
					
					<p>Best regards,<br>
					<strong>The {$siteTitle} Team</strong></p>
				</div>
				
				<div class='footer'>
					<p>This is an automated message. Please do not reply to this email.</p>
					<p>&copy; " . date('Y') . " {$siteTitle}. All rights reserved.</p>
				</div>
			</div>
		</body>
		</html>";

		// Send email using Laravel's Mail facade
		\Mail::raw('', function($mailMessage) use ($user, $subject, $message) {
			$mailMessage->to($user->email, $user->name)
					->subject($subject)
					->setBody($message, 'text/html');
		});
	}

	public function showMembers($id)
	{
		$user = User::findOrFail($id);
		$members = Member::where('user_id', $user->id)
						->with(['package', 'payments'])
						->orderBy('created_at', 'desc')
						->get();

		return view('admin.team.members-page', compact('user', 'members'));
	}

	public function showMemberPayments(Request $request)
	{
		$member = Member::with(['package', 'payments'])->findOrFail($request->member_id);
		$payments = $member->payments()->orderBy('created_at', 'desc')->get();

		return view('admin.team.member-payments', compact('member', 'payments'));
	}

	public function showBusinessPackages(Request $request)
	{
		$user = User::findOrFail($request->user_id);
		$packages = \App\Models\Package::where('user_id', $user->id)
						->orderBy('created_at', 'desc')
						->get();

		return view('admin.team.business-packages', compact('user', 'packages'));
	}

	

}
