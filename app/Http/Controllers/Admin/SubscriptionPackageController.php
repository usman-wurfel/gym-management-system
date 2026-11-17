<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPackage;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Session;
use DB;

class SubscriptionPackageController extends Controller
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
        $title = 'Subscription Packages';
        return view('admin.subscription-packages.index', compact('title'));
    }

    /**
     * Get subscription packages for DataTable
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getSubscriptionPackages(Request $request)
    {
        $columns = array(
            0 => 'id',
            1 => 'name',
            2 => 'description',
            3 => 'price',
            4 => 'duration',
            5 => 'features',
            6 => 'active',
            7 => 'created_at',
            8 => 'action'
        );

        $totalData = SubscriptionPackage::count();
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value'))){
            $packages = SubscriptionPackage::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = SubscriptionPackage::count();
        } else {
            $search = $request->input('search.value');
            $packages = SubscriptionPackage::where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('price', 'like', "%{$search}%")
                ->orWhere('duration', 'like', "%{$search}%")
                ->orWhere('features', 'like', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = SubscriptionPackage::where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('price', 'like', "%{$search}%")
                ->orWhere('duration', 'like', "%{$search}%")
                ->orWhere('features', 'like', "%{$search}%")
                ->count();
        }

        $data = array();

        if($packages){
            foreach($packages as $r){
                $edit_url = route('subscription-packages.edit', $r->id);
                $nestedData['id'] = '<td><label class="checkbox checkbox-outline checkbox-success">
                                    <input type="checkbox" name="packages[]" value="'.$r->id.'"><span></span></label></td>';
                $nestedData['name'] = $r->name;
                $nestedData['description'] = $r->description;
                $nestedData['price'] = '$' . number_format($r->price, 2);
                $nestedData['duration'] = $r->duration . ' days';
                $nestedData['features'] = $r->features;
                
                if($r->active){
                    $nestedData['active'] = '<span class="label label-success label-inline mr-2">Active</span>';
                } else {
                    $nestedData['active'] = '<span class="label label-danger label-inline mr-2">Inactive</span>';
                }
                
                $nestedData['created_at'] = date('d-m-Y', strtotime($r->created_at));
                
                $auth_user = Auth::user();
                $edit = '';
                $delete = '';
                $show = '';

                $show = ' <a class="btn btn-sm btn-icon btn-bg-light btn-icon-primary btn-hover-primary" onclick="event.preventDefault();viewInfo('.$r->id.');" title="View Package" href="javascript:void(0)">
                            <i class="flaticon-eye"></i>
                        </a>';
                $edit = ' <a title="Edit Package" class="btn btn-sm btn-icon btn-bg-light btn-icon-success btn-hover-success"
                               href="'.$edit_url.'">
                               <i class="flaticon-edit"></i>
                            </a>';
                $delete = '<a class="btn btn-sm btn-icon btn-bg-light btn-icon-danger btn-hover-danger" onclick="event.preventDefault();del('.$r->id.');" title="Delete Package" href="javascript:void(0)">
                            <i class="flaticon-delete"></i>
                        </a>';

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
            "draw"          => intval($request->input('draw')),
            "recordsTotal"  => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"          => $data
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
        $title = 'Add New Subscription Package';
        return view('admin.subscription-packages.create', compact('title'));
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
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'features' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $input = $request->all();
        $package = new SubscriptionPackage();
        $package->name = $input['name'];
        $package->description = $input['description'];
        $package->price = $input['price'];
        $package->duration = $input['duration'];
        $package->features = $input['features'];
        
        $res = array_key_exists('active', $input);
        if ($res == false) {
            $package->active = 0;
        } else {
            $package->active = 1;
        }

        if($request->hasFile('image')){
            $image = $request->file('image');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/packages'), $imageName);
            $package->image = $imageName;
        }
        
        $package->save();
        
        Session::flash('success_message', 'Great! Subscription Package has been saved successfully!');
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
        $package = SubscriptionPackage::find($id);
        return view('admin.subscription-packages.show', [
            'title' => 'Subscription Package Details', 
            'package' => $package,
        ]);
    }

    /**
     * Get package detail for modal
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function packageDetail(Request $request)
    {
        $package = SubscriptionPackage::findOrFail($request->id);
        
        // If it's an AJAX request for features only
        if ($request->ajax()) {
            return view('admin.subscription-packages.features', ['package' => $package]);
        }
        
        return view('admin.subscription-packages.detail', ['title' => 'Package Detail', 'package' => $package]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $package = SubscriptionPackage::find($id);
        return view('admin.subscription-packages.edit', ['title' => 'Edit Subscription Package', 'package' => $package]);
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
        $package = SubscriptionPackage::find($id);
        $this->validate($request, [
            'name' => 'required|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'features' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        
        $input = $request->all();

        $package->name = $input['name'];
        $package->description = $input['description'];
        $package->price = $input['price'];
        $package->duration = $input['duration'];
        $package->features = $input['features'];
        
        $res = array_key_exists('active', $input);
        if ($res == false) {
            $package->active = 0;
        } else {
            $package->active = 1;
        }

        if($request->hasFile('image')){
            // Delete old image if exists
            if($package->image && file_exists(public_path('uploads/packages/'.$package->image))){
                unlink(public_path('uploads/packages/'.$package->image));
            }
            
            $image = $request->file('image');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/packages'), $imageName);
            $package->image = $imageName;
        }
        
        $package->save();
        
        Session::flash('success_message', 'Great! Subscription Package successfully updated!');
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
        $package = SubscriptionPackage::find($id);
        
        // Delete image if exists
        if($package->image && file_exists(public_path('uploads/packages/'.$package->image))){
            unlink(public_path('uploads/packages/'.$package->image));
        }
        
        $package->delete();
        Session::flash('success_message', 'Subscription Package successfully deleted!');
        return redirect()->route('subscription-packages.index');
    }

    /**
     * Delete selected packages
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteSelectedPackages(Request $request)
    {
        $input = $request->all();
        $this->validate($request, [
            'packages' => 'required',
        ]);
        
        foreach ($input['packages'] as $index => $id) {
            $package = SubscriptionPackage::find($id);
            
            // Delete image if exists
            if($package->image && file_exists(public_path('uploads/packages/'.$package->image))){
                unlink(public_path('uploads/packages/'.$package->image));
            }
            
            $package->delete();
        }
        
        Session::flash('success_message', 'Subscription Packages successfully deleted!');
        return redirect()->back();
    }
} 