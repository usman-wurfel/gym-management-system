<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class PackageController extends Controller
{
    function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || $user->user_id != null) {
                $this->middleware('permission:package-list', ['only' => ['index','packageDetail']]);
                $this->middleware('permission:package-create', ['only' => ['create','store']]);
                $this->middleware('permission:package-edit', ['only' => ['edit','update']]);
                $this->middleware('permission:package-delete', ['only' => ['destroy','deleteSelectedPackages']]);
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
        $title = 'Packages';
		return view('admin.packages.index',compact('title'));
    }



    public function getPackages(Request $request){
        $columns = array(
            0 => 'id',
            1 => 'name',
            2 => 'price',
            3 => 'duration',
            4 => 'created_at',
            5 => 'action'
        );

        $id = auth()->user()->user_id ?? auth()->user()->id;
        $totalData = Package::where('user_id',$id)->count();
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value'))){
            $packages = Package::where('user_id',$id)->offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->get();
            $totalFiltered = Package::where('user_id',$id)->count();
        }else{
            $search = $request->input('search.value');
            $packages = Package::where([
                ['user_id',$id],
                ['name', 'like', "%{$search}%"],
            ])
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Package::where([
                ['user_id',$id],
                ['name', 'like', "%{$search}%"],
            ])
                ->count();
        }


        $data = array();

        if($packages){
            foreach($packages as $r){
                $edit_url = route('packages.edit',$r->id);
                $nestedData['id'] = '<td><label class="checkbox checkbox-outline checkbox-success">
                                    <input type="checkbox" name="packages[]" value="'.$r->id.'"><span></span></label>
                                    </td>';
                $nestedData['name'] = $r->name;
                $nestedData['price'] = $r->price;
                $nestedData['duration'] = $r->duration;
                $nestedData['created_at'] = $r->created_at->diffForHumans();
                
                $auth_user = Auth::user();
                $edit = '';
                $delete = '';
                $show = '';

                if ($auth_user->can('package-list')) {
                    $show = ' <a class="btn btn-sm btn-icon btn-bg-light btn-icon-primary btn-hover-primary" onclick="event.preventDefault();viewInfo('.$r->id.');" title="View Package" href="javascript:void(0)">
                        <i class="flaticon-eye"></i>
                    </a>';
                }
                if ($auth_user->can('package-edit')) {
                    $edit = ' <a title="Edit Package" class="btn btn-sm btn-icon btn-bg-light btn-icon-success btn-hover-success"
                        href="'.$edit_url.'">
                        <i class="flaticon-edit"></i>
                    </a>';
                }
                if ($auth_user->can('package-delete')) {
                    $delete = '<a class="btn btn-sm btn-icon btn-bg-light btn-icon-danger btn-hover-danger" onclick="event.preventDefault();del('.$r->id.');" title="Delete Package" href="javascript:void(0)">
                        <i class="flaticon-delete"></i>
                    </a>';
                }

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
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Add New Package';
        return view('admin.packages.create',compact('title'));
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
				'price' => 'required|numeric',
				'duration' => 'required|integer',
			]);

            $package = new Package();
            $package->user_id = auth()->user()->user_id ?? auth()->user()->id;
            $package->name = $request->input('name');
            $package->price = $request->input('price');
            $package->duration = $request->input('duration');


			$package->save();

			Session::flash('success_message', 'Success! Package saved successfully!');

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
        $package = Package::find($id);
        return view('admin.packages.single', ['title' => 'Package detail', 'package' => $package]);
    }

    public function packageDetail(Request $request)
    {
        $package = Package::find($request->id);
        return view('admin.packages.detail', ['title' => 'Package Detail','package' => $package]);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $package = Package::find($id);
        $title="Edit Package";
        return view('admin.packages.edit',compact('title','package'));
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
                'price' => 'required|numeric',
                'duration' => 'required|integer',
            ]);
            $package =  Package::findOrFail($id);
            $package->name = $request->input('name');
            $package->price = $request->input('price');
            $package->duration = $request->input('duration');
            $package->save();
			Session::flash('success_message', 'Success! package successfully updated!');
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
			$package = Package::find($id);

				$package->delete();
				Session::flash('success_message', 'Package successfully deleted!');

			return redirect()->route('packages.index');

		}
		public function deleteSelectedPackages(Request $request)
		{
			$input = $request->all();
			$this->validate($request, [
				'packages' => 'required',

			]);
			foreach ($input['packages'] as $index => $id) {

				$package = Package::find($id);

				$package->delete();


			}
			Session::flash('success_message', 'packages successfully deleted!');
			return redirect()->back();

		}
    public function createSlug($title, $id)
    {
        // Normalize the title
        $slug = Str::slug($title);

        // Get any that could possibly be related.
        // This cuts the queries down by doing it once.
        $allSlugs = $this->getRelatedSlugs($slug, $id);

        // If we haven't used it before then we are all good.
        if (! $allSlugs->contains('slug', $slug)){
            return $slug;
        }

        // Just append numbers like a savage until we find not used.
        for ($i = 1; $i <= 10; $i++) {
            $newSlug = $slug.'-'.$i;
            if (! $allSlugs->contains('slug', $newSlug)) {
                return $newSlug;
            }
        }

        throw new \Exception('Can not create a unique slug');
    }

    protected function getRelatedSlugs($slug, $id )
    {
        return Package::select('slug')->where('slug', 'like', $slug.'%')
            ->where('id', '<>', $id)
            ->get();
    }
}
