@extends('admin.layouts.master')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $title }}</h1>
        <div>
            <button class="btn btn-primary" onclick="showAssignmentModal()">
                <i class="fas fa-plus"></i> Assign Workout
            </button>
            <a href="{{ route('members.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Members
            </a>
        </div>
    </div>

    <!-- Member Information -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Member Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Name:</strong> {{ $member->name }}</p>
                    <p><strong>Code:</strong> {{ $member->code }}</p>
                    <p><strong>Phone:</strong> {{ $member->phone }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Email:</strong> {{ $member->email }}</p>
                    <p><strong>Package:</strong> {{ $member->package ? $member->package->name : 'N/A' }}</p>
                    <p><strong>Status:</strong> 
                        <span class="badge badge-{{ $member->status ? 'success' : 'danger' }}">
                            {{ $member->status ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Workout Assignments -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Workout Assignments</h6>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-outline-primary active" id="tableViewBtn" onclick="showTableView()">
                    <i class="fas fa-table"></i> Table View
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" id="calendarViewBtn" onclick="showCalendarView()">
                    <i class="fas fa-calendar"></i> Calendar View
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Table View -->
            <div id="tableView">
                <div id="workoutAssignments">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="text-muted mt-2">Loading workout assignments...</p>
                    </div>
                </div>
            </div>

            <!-- Calendar View -->
            <div id="calendarView" style="display: none;">
                <div id="workoutCalendar"></div>
            </div>
        </div>
    </div>
</div>

<!-- Workout Assignment Modal -->
<div class="modal fade" id="workoutAssignmentModal" tabindex="-1" role="dialog" aria-labelledby="workoutAssignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="workoutAssignmentModalLabel">Assign Workout</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="workoutAssignmentForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="member_id" value="{{ $member->id }}">
                    
                    <div class="form-group">
                        <label for="workout_type_id" class="form-label">Workout Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="workout_type_id" name="workout_type_id" required>
                            <option value="">Select Workout Type</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="assigned_date" class="form-label">Assignment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="assigned_date" name="assigned_date" 
                               value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Optional notes about this workout assignment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Assign Workout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('stylesheets')
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <style>
        .fc-event {
            cursor: pointer;
        }
        .fc-event:hover {
            opacity: 0.8;
        }
        .fc-toolbar-title {
            font-size: 1.5rem;
            font-weight: 600;
        }
        .fc-button {
            background-color: #007bff;
            border-color: #007bff;
        }
        .fc-button:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .fc-button:focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
    </style>
@endsection

@section('scripts')
<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script>
let calendar;
let workoutAssignments = [];

$(document).ready(function() {
    // Load workout assignments
    loadWorkoutAssignments();

    // Load workout types for modal
    loadWorkoutTypes();

    // Initialize calendar
    initializeCalendar();

    // Handle form submission
    $('#workoutAssignmentForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("members.store-workout-assignment") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#workoutAssignmentModal').modal('hide');
                    $('#workoutAssignmentForm')[0].reset();
                    loadWorkoutAssignments();
                    
                    // Show success message
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON.errors;
                let errorMessage = 'Please fix the following errors:\n';
                for (let field in errors) {
                    errorMessage += '- ' + errors[field][0] + '\n';
                }
                Swal.fire('Error', errorMessage, 'error');
            }
        });
    });
});

function loadWorkoutAssignments() {
    $.ajax({
        url: '{{ route("members.get-member-workouts") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            member_id: '{{ $member->id }}'
        },
        success: function(response) {
            if (response.success) {
                workoutAssignments = response.data;
                displayWorkoutAssignments(response.data);
                
                // Refresh calendar if it exists
                if (calendar) {
                    calendar.refetchEvents();
                }
            }
        }
    });
}

function displayWorkoutAssignments(workouts) {
    let html = '';
    
    if (workouts.length === 0) {
        html = `
            <div class="text-center py-5">
                <i class="fas fa-dumbbell fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No workout assignments</h5>
                <p class="text-muted">This member hasn't been assigned any workouts yet.</p>
                <button class="btn btn-primary" onclick="showAssignmentModal()">
                    <i class="fas fa-plus"></i> Assign First Workout
                </button>
            </div>
        `;
    } else {
        html = `
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Workout Type</th>
                            <th>Assigned Date</th>
                            <th>Notes</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        workouts.forEach(function(workout) {
            html += `
                <tr>
                    <td>
                        <span class="badge badge-primary">${workout.workout_type.name}</span>
                    </td>
                    <td>${new Date(workout.assigned_date).toLocaleDateString()}</td>
                    <td>${workout.notes || 'No notes'}</td>
                    <td>${new Date(workout.created_at).toLocaleDateString()}</td>
                    <td>
                        <button class="btn btn-sm btn-danger" onclick="deleteWorkoutAssignment(${workout.id})" title="Delete Assignment">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
    }
    
    $('#workoutAssignments').html(html);
}

function loadWorkoutTypes() {
    $.ajax({
        url: '{{ route("members.workout-assignment-modal") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            member_id: '{{ $member->id }}'
        },
        success: function(response) {
            $('#workout_type_id').html(response);
        }
    });
}

function showAssignmentModal() {
    $('#workoutAssignmentModal').modal('show');
}

function initializeCalendar() {
    const calendarEl = document.getElementById('workoutCalendar');
    
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: function(info, successCallback, failureCallback) {
            // Convert workout assignments to calendar events
            const events = workoutAssignments.map(workout => ({
                id: workout.id,
                title: workout.workout_type.name,
                start: workout.assigned_date,
                allDay: true,
                backgroundColor: getWorkoutTypeColor(workout.workout_type.name),
                borderColor: getWorkoutTypeColor(workout.workout_type.name),
                extendedProps: {
                    notes: workout.notes,
                    workoutType: workout.workout_type.name,
                    createdAt: workout.created_at
                }
            }));
            successCallback(events);
        },
        eventClick: function(info) {
            showWorkoutDetails(info.event);
        },
        eventDidMount: function(info) {
            // Add tooltip
            info.el.setAttribute('title', `${info.event.title} - ${info.event.extendedProps.notes || 'No notes'}`);
        },
        height: 'auto',
        aspectRatio: 1.8
    });
    
    calendar.render();
}

function getWorkoutTypeColor(workoutType) {
    const colors = {
        'Chest': '#e74c3c',
        'Back': '#3498db',
        'Legs': '#2ecc71',
        'Arms': '#f39c12',
        'Shoulders': '#9b59b6',
        'Cardio': '#e67e22',
        'Core': '#1abc9c',
        'Full Body': '#34495e'
    };
    return colors[workoutType] || '#6c757d';
}

function showWorkoutDetails(event) {
    const workoutType = event.extendedProps.workoutType;
    const notes = event.extendedProps.notes || 'No notes';
    const assignedDate = event.start.toLocaleDateString();
    const createdAt = new Date(event.extendedProps.createdAt).toLocaleDateString();
    
    Swal.fire({
        title: workoutType,
        html: `
            <div class="text-left">
                <p><strong>Assigned Date:</strong> ${assignedDate}</p>
                <p><strong>Notes:</strong> ${notes}</p>
                <p><strong>Created:</strong> ${createdAt}</p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Delete Assignment',
        cancelButtonText: 'Close',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            deleteWorkoutAssignment(event.id);
        }
    });
}

function showTableView() {
    $('#tableView').show();
    $('#calendarView').hide();
    $('#tableViewBtn').addClass('active');
    $('#calendarViewBtn').removeClass('active');
}

function showCalendarView() {
    $('#tableView').hide();
    $('#calendarView').show();
    $('#tableViewBtn').removeClass('active');
    $('#calendarViewBtn').addClass('active');
    
    // Refresh calendar when switching to calendar view
    if (calendar) {
        calendar.refetchEvents();
    }
}

function deleteWorkoutAssignment(workoutId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("members.delete-workout-assignment") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    workout_assignment_id: workoutId
                },
                success: function(response) {
                    if (response.success) {
                        loadWorkoutAssignments();
                        Swal.fire({
                            title: 'Deleted!',
                            text: response.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Failed to delete workout assignment', 'error');
                }
            });
        }
    });
}
</script>
@endsection
