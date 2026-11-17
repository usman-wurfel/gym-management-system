<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Session;
use DB;
use Spatie\Permission\Models\Role;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
		$this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || $user->user_id != null) {
                $this->middleware('permission:user-list', ['only' => ['index','clientDetail']]);
				$this->middleware('permission:user-create', ['only' => ['create','store']]);
				$this->middleware('permission:user-edit', ['only' => ['edit','update']]);
				$this->middleware('permission:user-delete', ['only' => ['destroy','deleteSelectedClients']]);
            }
            return $next($request);
        });
        
    }

    public function index()
    {
	    $title = 'Users';
	    return view('admin.clients.index',compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

	public function getClients(Request $request){
		$columns = array(
			0 => 'id',
			1 => 'name',
			2 => 'email',
			3 => 'active',
			4 => 'created_at',
			5 => 'action'
		);
		$id = auth()->user()->user_id ?? auth()->user()->id;
		$totalData = User::where('is_admin',1)->where('user_id',$id)->count();
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');

		if(empty($request->input('search.value'))){
			$users = User::where('is_admin',1)->where('user_id',$id)->offset($start)
				->limit($limit)
				->orderBy($order,$dir)
				->get();
			$totalFiltered = User::where('is_admin',1)->where('user_id',$id)->count();
		}else{
			$search = $request->input('search.value');
			$users = User::where([
				['is_admin',1],
				['user_id',$id],
				['name', 'like', "%{$search}%"],
			])
				->orWhere([
					['is_admin',1],
					['user_id',$id],
					['email','like',"%{$search}%"],
				])
				->orWhere([
					['is_admin',1],
					['user_id',$id],
					['created_at','like',"%{$search}%"],
				])
				->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
			$totalFiltered = User::where([
				['is_admin',1],
				['user_id',$id],
				['name', 'like', "%{$search}%"],
			])
				->orWhere([
					['is_admin',1],
					['user_id',$id],
					['name', 'like', "%{$search}%"],
				])
				->orWhere([
					['is_admin',1],
					['user_id',$id],
					['email','like',"%{$search}%"],
				])
				->orWhere('created_at','like',"%{$search}%")
				->count();
		}


		$data = array();

		if($users){
			foreach($users as $r){
				$edit_url = route('clients.edit',$r->id);
				$shifts_url = route('clients.show',$r->id);
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
                if ($auth_user->can('user-list')){
                    $show = ' <a class="btn btn-sm btn-icon btn-bg-light btn-icon-primary btn-hover-primary" onclick="event.preventDefault();viewInfo('.$r->id.');" title="View " href="javascript:void(0)">
                                        <i class="flaticon-eye"></i>
                                    </a>';
                }
                if ($auth_user->can('user-edit')){
                    $edit = ' <a title="Edit " class="btn btn-sm btn-icon btn-bg-light btn-icon-success btn-hover-success"
                                       href="'.$edit_url.'">
                                       <i class="flaticon-edit"></i>
                                    </a>';
                }

                if ($auth_user->can('user-delete')){
                    $delete = '<a class="btn btn-sm btn-icon btn-bg-light btn-icon-danger btn-hover-danger" onclick="event.preventDefault();del('.$r->id.');" title="Delete " href="javascript:void(0)">
                                        <i class="flaticon-delete"></i>
                                    </a>';
                }
				$nestedData['created_at'] = date('d-m-Y',strtotime($r->created_at));
				$nestedData['action'] = '
                                <div>
                                <td>

                                   '.$show.'
                                   '.$edit.'
                                   '.$history.'
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
    public function create()
    {
	    $title = 'Add New Users';
		$id = auth()->user()->user_id ?? auth()->user()->id;
        $roles = Role::where('user_id',$id)->pluck('name','name')->all();
	    return view('admin.clients.create',compact('title','roles','id'));
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
		    'password' => 'required|min:6',
		    'role' => 'required',
	    ]);
		$id = auth()->user()->user_id ?? auth()->user()->id;
	    $input = $request->all();
	    $user = new User();
		$user->user_id = $id;
	    $user->name = $input['name'];
	    $user->email = $input['email'];
	    $user->is_admin = 1;
	    $res = array_key_exists('active', $input);
	    if ($res == false) {
		    $user->active = 0;
	    } else {
		    $user->active = 1;

	    }
	    $user->password = bcrypt($input['password']);
	    $user->save();
        $user->assignRole($request->input('role'));
	    Session::flash('success_message', 'Great! User has been saved successfully!');
	    $user->save();
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
	    return view('admin.clients.single', ['title' => 'User Shits', 'user' => $user]);
    }

	public function clientDetail(Request $request)
	{

		$user = User::findOrFail($request->id);


		return view('admin.clients.detail', ['title' => 'User Detail', 'user' => $user]);
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
		$id = auth()->user()->user_id ?? auth()->user()->id;
        $roles = Role::where('user_id',$id)->pluck('name','name')->all();
        $userRole = $user->roles->pluck('name','name')->all();
	    return view('admin.clients.edit', ['title' => 'Edit User details'],compact('roles','userRole'))->withUser($user);
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
		    'role' => 'required',
	    ]);
	    $input = $request->all();

	    $user->name = $input['name'];
	    $user->email = $input['email'];
	    $user->is_admin = 1;
	    $res = array_key_exists('active', $input);
	    if ($res == false) {
		    $user->active = 0;
	    } else {
		    $user->active = 1;

	    }
	    if(!empty($input['password'])) {
		    $user->password = bcrypt($input['password']);
	    }

	    $user->save();
        DB::table('model_has_roles')->where('model_id',$id)->delete();
        $user->assignRole($request->input('role'));
	    Session::flash('success_message', 'Great! User successfully updated!');
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
	    if($user->is_admin == 0){
		    $user->delete();
		    Session::flash('success_message', 'User successfully deleted!');
	    }
	    return redirect()->route('clients.index');

    }
	public function deleteSelectedClients(Request $request)
	{
		$input = $request->all();
		$this->validate($request, [
			'users' => 'required',

		]);
		foreach ($input['users'] as $index => $id) {

			$user = User::find($id);
			if($user->is_admin == 0){
				$user->delete();
			}

		}
		Session::flash('success_message', 'Users successfully deleted!');
		return redirect()->back();

	}
}
