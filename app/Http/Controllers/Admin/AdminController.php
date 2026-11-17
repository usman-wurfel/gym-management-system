<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Shift;
use App\Models\Status;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Dompdf\Dompdf;


class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        $title = 'Dashboard';

        return view('admin.dashboard.index',compact('title'));
    }

    // Method to start a new shift
    public function startShift(Request $request)
    {
        $request->validate([
            'beginning_amount' => 'required|numeric',
        ]);

        $shift = new Shift();
        $shift->user_id = Auth::id();
        $shift->beginning_amount = $request->input('beginning_amount');
//        $shift->start_time = now();
        $shift->save();

        return redirect()->back()->with('success_message', 'Shift started successfully.');
    }

    // Method to close the current shift
    public function closeShift(Request $request, $id)
    {
        $request->validate([
            'ending_amount' => 'required|numeric',
            'money_transfer' => 'required|numeric',
            'money_orders_sold' => 'required|numeric',
        ]);

        $shift = Shift::findOrFail($id);
        $shift->ending_amount = $request->input('ending_amount');
        $shift->money_transfer = $request->input('money_transfer');
        $shift->money_orders_sold = $request->input('money_orders_sold');
//        $shift->end_time = now();
        $shift->save();

        return redirect()->back()->with('success_message', 'Shift closed successfully.');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }
    public function search(Request $request)
    {
        $title = 'Dashboard ';

        return view('admin.dashboard.index',compact('title'));
    }
    public function category($id)
    {
        $title = 'Dashboard Category Search';

        return view('admin.dashboard.index',compact('title'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(){
        $user = Auth::user();
        return view('admin.settings.edit', ['title' => 'Edit Admin Profile','user'=>$user]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $admin = Auth::user();
        $this->validate($request, [
            'name' => 'required|max:255',
            'email' => 'required|unique:users,email,'.$admin->id,
        ]);
        $input = $request->all();
        if (empty($input['password'])) {
            $input['password'] = $admin->password;
        } else {
            $input['password'] = bcrypt($input['password']);
        }
        if($request->hasFile('image')){
            $image = $request->file('image');
            $image_name = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('images'), $image_name);
            $input['image'] = $image_name;
        }
        $admin->fill($input)->save();
        Session::flash('success_message', 'Great! profile successfully updated!');
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
        //
    }
}
