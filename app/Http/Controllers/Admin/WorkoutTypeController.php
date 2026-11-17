<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkoutType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WorkoutTypeController extends Controller
{
    /**
     * Constructor with middleware and permission checks
     */
    function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || $user->user_id != null) {
                $this->middleware('permission:workout-type-list', ['only' => ['index', 'show']]);
                $this->middleware('permission:workout-type-create', ['only' => ['create', 'store']]);
                $this->middleware('permission:workout-type-edit', ['only' => ['edit', 'update']]);
                $this->middleware('permission:workout-type-delete', ['only' => ['destroy']]);
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
        $title = 'Workout Types';
        $workoutTypes = WorkoutType::where('user_id', auth()->user()->user_id ?? auth()->user()->id)
            ->withCount('workouts')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.workout-types.index', compact('title', 'workoutTypes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Add Workout Type';
        return view('admin.workout-types.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:workout_types,name,NULL,id,user_id,' . (auth()->user()->user_id ?? auth()->user()->id),
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = [
            'name' => $request->name,
            'user_id' => auth()->user()->user_id ?? auth()->user()->id
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/workout-types'), $imageName);
            $data['image'] = $imageName;
        }

        WorkoutType::create($data);

        return redirect()->route('workout-types.index')
            ->with('success', 'Workout type created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $workoutType = WorkoutType::where('id', $id)
            ->where('user_id', auth()->user()->user_id ?? auth()->user()->id)
            ->with('workouts')
            ->firstOrFail();

        $title = 'Workout Type Details';
        return view('admin.workout-types.show', compact('title', 'workoutType'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $workoutType = WorkoutType::where('id', $id)
            ->where('user_id', auth()->user()->user_id ?? auth()->user()->id)
            ->firstOrFail();

        $title = 'Edit Workout Type';
        return view('admin.workout-types.edit', compact('title', 'workoutType'));
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
        $workoutType = WorkoutType::where('id', $id)
            ->where('user_id', auth()->user()->user_id ?? auth()->user()->id)
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255|unique:workout_types,name,' . $id . ',id,user_id,' . (auth()->user()->user_id ?? auth()->user()->id),
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = ['name' => $request->name];

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($workoutType->image && file_exists(public_path('uploads/workout-types/' . $workoutType->image))) {
                unlink(public_path('uploads/workout-types/' . $workoutType->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/workout-types'), $imageName);
            $data['image'] = $imageName;
        }

        $workoutType->update($data);

        return redirect()->route('workout-types.index')
            ->with('success', 'Workout type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $workoutType = WorkoutType::where('id', $id)
            ->where('user_id', auth()->user()->user_id ?? auth()->user()->id)
            ->firstOrFail();

        // Check if workout type has associated workouts
        if ($workoutType->workouts()->count() > 0) {
            return redirect()->route('workout-types.index')
                ->with('error', 'Cannot delete workout type. It has associated workouts.');
        }

        $workoutType->delete();

        return redirect()->route('workout-types.index')
            ->with('success', 'Workout type deleted successfully.');
    }
}
