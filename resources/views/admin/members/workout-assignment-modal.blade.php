<option value="">Select Workout Type</option>
@foreach($workoutTypes as $type)
    <option value="{{ $type->id }}">{{ $type->name }}</option>
@endforeach





