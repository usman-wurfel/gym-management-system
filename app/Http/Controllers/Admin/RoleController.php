<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryProvince;
use App\Models\Product;
use App\Models\Province;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;


class RoleController extends Controller
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
                $this->middleware('permission:role-list', ['only' => ['index','show','clientDetail']]);
                $this->middleware('permission:role-create', ['only' => ['create','store']]);
                $this->middleware('permission:role-edit', ['only' => ['edit','update']]);
                $this->middleware('permission:role-delete', ['only' => ['destroy','deleteSelectedClients']]);
            }
            return $next($request);
        });
        
    }
    public function index()
    {
	    $title = 'Roles';
	    return view('admin.roles.index',compact('title'));
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
			2 => 'created_at',
			5 => 'action'
		);
		$id = auth()->user()->user_id ?? auth()->user()->id;
		$totalData = Role::where('user_id',$id)->count();
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir = $request->input('order.0.dir');

		if(empty($request->input('search.value'))){
			$datas = Role::where('user_id',$id)->offset($start)
				->limit($limit)
				->orderBy($order,$dir)
				->get();
			$totalFiltered = Role::where('user_id',$id)->count();
		}else{
			$search = $request->input('search.value');
			$datas = Role::where([
				['user_id',$id],
				['name','like',"%{$search}%"],
			])
				->orWhere([
					['user_id',$id],
					['created_at','like',"%{$search}%"],
				])
				->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
			$totalFiltered = Role::where([
				['user_id',$id],
				['name', 'like', "%{$search}%"],
			])
				->orWhere([
					['user_id',$id],
					['created_at','like',"%{$search}%"],
				])
			  	->count();
		}


		$data = array();

		if($datas){
			foreach($datas as $r){
				$edit_url = route('roles.edit',$r->id);
				$nestedData['id'] = '<td><label class="checkbox checkbox-outline checkbox-success"><input type="checkbox" name="roles[]" value="'.$r->id.'"><span></span></label></td>';
				$nestedData['name'] = $r->name;

                $auth_user = Auth::user();
                $edit = '';
                $delete = '';
                $show = '';
                if ($auth_user->can('role-list')){
                    $show = ' <a class="btn btn-sm btn-icon btn-bg-light btn-icon-primary btn-hover-primary" onclick="event.preventDefault();viewInfo('.$r->id.');" title="View " href="javascript:void(0)">
                                        <i class="flaticon-eye"></i>
                                    </a>';
                }
                if ($auth_user->can('role-edit')){
                    $edit = ' <a title="Edit " class="btn btn-sm btn-icon btn-bg-light btn-icon-success btn-hover-success"
                                       href="'.$edit_url.'">
                                       <i class="flaticon-edit"></i>
                                    </a>';
                }
                if ($auth_user->can('role-delete')){
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
	    $title = 'Add New ';
        $permission = Permission::get();
	    return view('admin.roles.create',["title"=>$title],compact('permission'));
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
            'name' => 'required|unique:roles,name',
            'permission' => 'required',
	    ]);
        $id = auth()->user()->user_id ?? auth()->user()->id;
        $role = new Role();
        $role->user_id = $id;
        $role->name = $request->input('name');
        $role->save();
        $role->syncPermissions($request->input('permission'));

	    Session::flash('success_message', 'Great! Role has been saved successfully!');
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
	    $data = Role::find($id);
	    return view('admin.roles.single', ['title' => 'Status detail', 'user' => $data]);
    }

	public function clientDetail(Request $request)
	{

		$data = Role::findOrFail($request->id);
        $rolePermissions = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
            ->where("role_has_permissions.role_id",$data->id)
            ->get();

		return view('admin.roles.detail', ['title' => 'Role Detail', 'user' => $data,'rolePermissions' => $rolePermissions,]);
	}



    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
	    $data = Role::find($id);
        $permission = Permission::get();
        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id",$id)
            ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
            ->all();

        return view('admin.roles.edit', ['title' => 'Edit Role details'],compact('permission','rolePermissions'))->withUser($data);
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
            'name' => 'required',
            'permission' => 'required',
        ]);

        $role = Role::find($id);
        $role->name = $request->input('name');
        $role->save();
        $role->syncPermissions($request->input('permission'));
	    Session::flash('success_message', 'Great! Role successfully updated!');
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
	    $data = Role::find($id);

        $data->delete();
        Session::flash('success_message', 'Role successfully deleted!');

	    return redirect()->route('roles.index');

    }
	public function deleteSelectedClients(Request $request)
	{
		$input = $request->all();
		$this->validate($request, [
			'roles' => 'required',
		]);
		foreach ($input['roles'] as $index => $id) {

			$data = Role::find($id);
				$data->delete();

		}
		Session::flash('success_message', 'Roles successfully deleted!');
		return redirect()->back();

	}
}
