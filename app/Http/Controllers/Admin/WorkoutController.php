<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Workout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WorkoutController extends Controller
{
    /**
     * Constructor with middleware and permission checks
     */
    function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || $user->user_id != null) {
                $this->middleware('permission:workout-list', ['only' => ['index', 'show']]);
                $this->middleware('permission:workout-create', ['only' => ['create', 'store']]);
                $this->middleware('permission:workout-edit', ['only' => ['edit', 'update']]);
                $this->middleware('permission:workout-delete', ['only' => ['destroy']]);
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
        $title = 'Workouts';
        $workouts = Workout::where('user_id', auth()->user()->user_id ?? auth()->user()->id)
            ->with('workoutType')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.workouts.index', compact('title', 'workouts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Add Workout';
        return view('admin.workouts.create', compact('title'));
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'workout_type_id' => 'nullable|exists:workout_types,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:10240'
        ]);

        $data = $request->only(['name', 'description', 'workout_type_id']);
        $data['user_id'] = auth()->user()->user_id ?? auth()->user()->id;

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/workouts'), $imageName);
            $data['image'] = $imageName;
        }

        // Handle video upload
        if ($request->hasFile('video')) {
            $video = $request->file('video');
            $videoName = time() . '_' . Str::random(10) . '.' . $video->getClientOriginalExtension();
            $video->move(public_path('uploads/workouts'), $videoName);
            $data['video'] = $videoName;
        }

        Workout::create($data);

        return redirect()->route('workouts.index')
            ->with('success', 'Workout created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $workout = Workout::where('id', $id)
            ->where('user_id', auth()->user()->user_id ?? auth()->user()->id)
            ->firstOrFail();

        $title = 'Workout Details';
        return view('admin.workouts.show', compact('title', 'workout'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $workout = Workout::where('id', $id)
            ->where('user_id', auth()->user()->user_id ?? auth()->user()->id)
            ->firstOrFail();

        $title = 'Edit Workout';
        return view('admin.workouts.edit', compact('title', 'workout'));
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
        $workout = Workout::where('id', $id)
            ->where('user_id', auth()->user()->user_id ?? auth()->user()->id)
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'workout_type_id' => 'nullable|exists:workout_types,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:10240'
        ]);

        $data = $request->only(['name', 'description', 'workout_type_id']);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($workout->image && file_exists(public_path('uploads/workouts/' . $workout->image))) {
                unlink(public_path('uploads/workouts/' . $workout->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/workouts'), $imageName);
            $data['image'] = $imageName;
        }

        // Handle video upload
        if ($request->hasFile('video')) {
            // Delete old video
            if ($workout->video && file_exists(public_path('uploads/workouts/' . $workout->video))) {
                unlink(public_path('uploads/workouts/' . $workout->video));
            }

            $video = $request->file('video');
            $videoName = time() . '_' . Str::random(10) . '.' . $video->getClientOriginalExtension();
            $video->move(public_path('uploads/workouts'), $videoName);
            $data['video'] = $videoName;
        }

        $workout->update($data);

        return redirect()->route('workouts.index')
            ->with('success', 'Workout updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $workout = Workout::where('id', $id)
            ->where('user_id', auth()->user()->user_id ?? auth()->user()->id)
            ->firstOrFail();

        // Delete associated files
        if ($workout->image && file_exists(public_path('uploads/workouts/' . $workout->image))) {
            unlink(public_path('uploads/workouts/' . $workout->image));
        }

        if ($workout->video && file_exists(public_path('uploads/workouts/' . $workout->video))) {
            unlink(public_path('uploads/workouts/' . $workout->video));
        }

        $workout->delete();

        return redirect()->route('workouts.index')
            ->with('success', 'Workout deleted successfully.');
    }
}
