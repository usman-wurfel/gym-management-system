@extends('admin.layouts.master')
@section('title',$title)
@section('content')
  <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader" kt-hidden-height="54" style="">
      <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
        <!--begin::Info-->
        <div class="d-flex align-items-center flex-wrap mr-1">
          <!--begin::Page Heading-->
          <div class="d-flex align-items-baseline flex-wrap mr-5">
            <!--begin::Page Title-->
            <h5 class="text-dark font-weight-bold my-1 mr-5">Dashboard</h5>
            <!--end::Page Title-->
            <!--begin::Breadcrumb-->
            <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
              <li class="breadcrumb-item text-muted">
                <a href="{{ route('clients.index') }}" class="text-muted">Manage Players</a>
              </li>
              <li class="breadcrumb-item text-muted">
                <a href="" class="text-muted">Player Details</a>
              </li>
            </ul>
            <!--end::Breadcrumb-->
          </div>
          <!--end::Page Heading-->
        </div>
        <!--end::Info-->
      </div>
    </div>
    <!--end::Subheader-->
    <!--begin::Entry-->
    <div class="d-flex flex-column-fluid">
      <!--begin::Container-->
      <div class="container">
        <!--begin::Card-->
        <div class="card card-custom card-sticky" id="kt_page_sticky_card">
          <div class="card-header" style="">
            <div class="card-title">
              <h3 class="card-label mt-5"> {{ $user->name }}
                <i class="mr-2"></i>
                <small class="">Player Information & Workout Assignments</small></h3>
            </div>
            <div class="card-toolbar">
              <a href="{{ route('clients.index') }}" class="btn btn-light-primary font-weight-bolder mr-2">
                <i class="ki ki-long-arrow-back icon-sm"></i>Back</a>
              <button type="button" class="btn btn-primary font-weight-bolder mr-2" data-toggle="modal" data-target="#assignWorkoutModal">
                <i class="ki ki-plus icon-sm"></i>Assign Workout</button>
              <button type="button" class="btn btn-success font-weight-bolder" onclick="exportStatsAsCSV()">
                <i class="fas fa-download icon-sm"></i>Export Stats as CSV</button>
            </div>
          </div>
          <div class="card-body">
            @include('admin.partials._messages')
            
            <!--begin::Player Information with Image-->
            <div class="row mb-8">
              <div class="col-lg-8">
                <div class="card card-custom bg-gradient-primary">
                  <div class="card-body p-8">
                    <div class="d-flex align-items-center">
                      <!--begin::User Image-->
                      <div class="symbol symbol-80 symbol-light-success mr-6">
                        @if($user->image)
                          <img src="{{ asset('images/' . $user->image) }}" alt="{{ $user->name }}" class="symbol-label" style="object-fit: cover;">
                        @else
                          <span class="symbol-label bg-primary d-flex align-items-center justify-content-center">
                            <span class="text-white font-weight-bold font-size-h2">
                              {{ strtoupper(substr($user->name, 0, 1)) }}
                            </span>
                          </span>
                        @endif
                      </div>
                      <!--end::User Image-->
                      
                      <div class="flex-grow-1">
                        <h2 class="text-primary font-weight-bold mb-2">{{ $user->name }} 🏋️</h2>
                        <p class="text-white-75 mb-0">{{ $user->email }}</p>
                        
                        <!--begin::Points Display-->
                        @php
                          $completedWorkouts = $user->playerWorkouts()->where('status', 'completed')->count();
                          $totalPoints = $completedWorkouts; // 1 point per completed workout
                        @endphp
                        <div class="mt-3">
                          <div class="d-flex align-items-center">
                            <div class="bg-white bg-opacity-20 rounded-pill px-4 py-2 mr-3">
                              <span class="text-primary font-weight-bold font-size-lg">
                                <i class="fas fa-star text-warning mr-2"></i>
                                {{ $totalPoints }} Points
                              </span>
                            </div>
                            <div class="text-white-75 font-size-sm">
                              Earned from {{ $completedWorkouts }} completed workouts
                            </div>
                          </div>
                        </div>
                        <!--end::Points Display-->
                        
                        <div class="mt-3">
                          @if($user->active)
                            <span class="label label-lg label-light-success label-inline">Active</span>
                          @else
                            <span class="label label-lg label-light-danger label-inline">Inactive</span>
                          @endif
                          <span class="label label-lg label-light-info label-inline ml-2">Joined: {{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="card card-custom bg-primary">
                  <div class="card-body p-6">
                    <div class="d-flex align-items-center mb-4">
                      <div class="symbol symbol-60 symbol-light-primary mr-4">
                        <span class="symbol-label">
                          <i class="fas fa-chart-line fa-2x text-primary"></i>
                        </span>
                      </div>
                      <div class="flex-grow-1">
                        <h3 class="text-white font-weight-bold mb-1">Workout Analytics</h3>
                        <p class="text-white mb-0">Performance Overview</p>
                      </div>
                    </div>
                    
                    <div class="row">
                      <div class="col-6">
                        <div class="bg-white bg-opacity-25 rounded-lg p-4 text-center mb-3">
                          <div class="text-primary font-weight-bold font-size-h3 mb-1">{{ $workouts->count() }}</div>
                          <div class="text-primary font-size-sm">Available Workouts</div>
                        </div>
                      </div>
                      <div class="col-6">
                        <div class="bg-white bg-opacity-25 rounded-lg p-4 text-center mb-3">
                          <div class="text-primary font-weight-bold font-size-h3 mb-1" id="assignedCount">{{ $playerWorkouts->count() }}</div>
                          <div class="text-primary font-size-sm">Assigned Workouts</div>
                        </div>
                      </div>
                    </div>
                    
                    @php
                      $completionRate = $playerWorkouts->count() > 0 ? round(($user->playerWorkouts()->where('status', 'completed')->count() / $playerWorkouts->count()) * 100) : 0;
                    @endphp
                    
                    <div class="mt-4">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-white font-weight-bold">Completion Rate</span>
                        <span class="text-white font-weight-bold">{{ $completionRate }}%</span>
                      </div>
                      <div class="progress bg-white bg-opacity-25" style="height: 8px;">
                        <div class="progress-bar bg-white" role="progressbar" style="width: {{ $completionRate }}%" aria-valuenow="{{ $completionRate }}" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                    </div>
                    
                    <div class="mt-4 pt-3 border-top border-white border-opacity-30">
                      <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                          <i class="fas fa-clock text-white mr-2"></i>
                          <span class="text-white font-size-sm">Last Updated</span>
                        </div>
                        <span class="text-white font-size-sm">{{ now()->format('M d, H:i') }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!--end::Player Information-->

            <!--begin::Badges Section-->
            <div class="row mb-8">
              <div class="col-lg-12">
                <div class="card card-custom gutter-b">
                  <div class="card-header">
                    <div class="card-title">
                      <h3 class="card-label">
                        <i class="fas fa-trophy text-warning mr-2"></i>
                        Player Achievements
                      </h3>
                    </div>
                  </div>
                  <div class="card-body">
                    @php
                      $completedWorkouts = $user->playerWorkouts()->where('status', 'completed')->count();
                      $totalWorkouts = $user->playerWorkouts()->count();
                      $badges = App\Models\Badge::where("points","<=",$completedWorkouts)->get();
                     
                      
                      // Perfect Week Badge (7 workouts in a week)
                      $weekWorkouts = $user->playerWorkouts()
                          ->where('status', 'completed')
                          ->where('updated_at', '>=', now()->subWeek())
                          ->count();
                      
                      if ($weekWorkouts >= 7) {
                          $badges[] = [
                              'name' => 'Perfect Week',
                              'description' => 'Completed 7 workouts in a week',
                              'icon' => 'fas fa-calendar-check',
                              'color' => 'badge-light-info',
                              'bg_color' => 'bg-light-info'
                          ];
                      }
                      
                      // Consistency Badge (3 consecutive days)
                      $consecutiveDays = 0;
                      $currentStreak = 0;
                      $lastWorkoutDate = null;
                      
                      $recentWorkouts = $user->playerWorkouts()
                          ->where('status', 'completed')
                          ->orderBy('updated_at', 'desc')
                          ->get();
                      
                      foreach ($recentWorkouts as $workout) {
                          $workoutDate = $workout->updated_at->format('Y-m-d');
                          if ($lastWorkoutDate === null) {
                              $currentStreak = 1;
                              $lastWorkoutDate = $workoutDate;
                          } elseif ($workoutDate === date('Y-m-d', strtotime($lastWorkoutDate . ' -1 day'))) {
                              $currentStreak++;
                              $lastWorkoutDate = $workoutDate;
                          } else {
                              break;
                          }
                      }
                      
                      
                    @endphp
                    
                    @if(count($badges) > 0)
                      <div class="row">
                        @foreach($badges as $badge)
                          <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card card-custom {{ $badge['bg_color'] }} h-100">
                              <div class="card-body text-center p-6">
                                <div class="symbol symbol-60 symbol-light-success mx-auto mb-4">
                                  <span class="symbol-label">
                                    @if($badge->image)
                                      <img src="{{ asset($badge->image) }}" alt="Badge Image" style="width:50px;height:50px;object-fit:cover;">
                                    @else
                                        <span class="text-muted">No Image</span>
                                    @endif
                                  </span>
                                </div>
                                <h5 class="font-weight-bold mb-2">{{ $badge['name'] }}</h5>
                                <p class="text-muted mb-0">{{ $badge['points'] }} Session</p>
                              </div>
                            </div>
                          </div>
                        @endforeach
                      </div>
                    @else
                      <div class="text-center py-8">
                        <div class="svg-icon svg-icon-4x svg-icon-primary mb-4">
                          <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                              <polygon points="0 0 24 0 24 24 0 24"/>
                              <path d="M12,2 C13.1,2 14,2.9 14,4 C14,5.1 13.1,6 12,6 C10.9,6 10,5.1 10,4 C10,2.9 10.9,2 12,2 Z M21,9 L21,7 L19,7 L19,9 L17,9 L17,11 L19,11 L19,13 L21,13 L21,11 L23,11 L23,9 L21,9 Z M12,8 C14.21,8 16,9.79 16,12 C16,14.21 14.21,16 12,16 C9.79,16 8,14.21 8,12 C8,9.79 9.79,8 12,8 Z M12,18 C15.31,18 18,20.69 18,24 L6,24 C6,20.69 8.69,18 12,18 Z" fill="#000000" fill-rule="nonzero"/>
                            </g>
                          </svg>
                        </div>
                        <h4 class="text-muted mb-2">No Badges Yet</h4>
                        <p class="text-muted">This player hasn't earned any badges yet. Complete workouts to earn achievements!</p>
                      </div>
                    @endif
                  </div>
                </div>
              </div>
            </div>
            <!--end::Badges Section-->

            <!--begin::Assigned Workouts Table-->
            <div class="card card-custom gutter-b">
              <div class="card-header">
                <div class="card-title">
                  <h3 class="card-label">Assigned Workouts</h3>
                </div>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-head-custom table-vertical-center" id="assignedWorkoutsTable">
                    <thead>
                      <tr>
                        <th>Workout Name</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Assigned Date</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody id="assignedWorkoutsBody">
                      @foreach($playerWorkouts as $assignment)
                        <tr>
                          <td>
                            <div class="d-flex align-items-center">
                              <div class="symbol symbol-40 symbol-light-warning mr-4">
                                <span class="symbol-label">
                                  <i class="fas fa-dumbbell fa-lg text-warning"></i>
                                </span>
                              </div>
                              <div>
                                <div class="font-weight-bolder font-size-lg">{{ $assignment->workout->name }}</div>
                                @if($assignment->notes)
                                  <div class="text-muted">{{ $assignment->notes }}</div>
                                @endif
                              </div>
                            </div>
                          </td>
                          <td>{{ $assignment->workout->duration }} min</td>
                          <td>
                            @php
                              $statusClass = '';
                              $statusText = '';
                              switch($assignment->status) {
                                case 'assigned':
                                  $statusClass = 'label-light-primary';
                                  $statusText = 'Assigned';
                                  break;
                                case 'in_progress':
                                  $statusClass = 'label-light-warning';
                                  $statusText = 'In Progress';
                                  break;
                                case 'completed':
                                  $statusClass = 'label-light-success';
                                  $statusText = 'Completed';
                                  break;
                                case 'paused':
                                  $statusClass = 'label-light-info';
                                  $statusText = 'Paused';
                                  break;
                              }
                            @endphp
                            <span class="label label-lg {{ $statusClass }} label-inline">{{ $statusText }}</span>
                          </td>
                          <td>{{ $assignment->created_at->format('M d, Y') }}</td>
                          <td>
                        
                            <button type="button" class="btn btn-sm btn-light-danger" onclick="deleteAssignment({{ $assignment->id }})">
                              <i class="fa fa-trash icon-sm"></i>
                            </button>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <!--end::Assigned Workouts Table-->

          </div>
        </div>
        <!--end::Card-->

        <!--begin::Assign Workout Modal-->
        <div class="modal fade" id="assignWorkoutModal" tabindex="-1" role="dialog" aria-labelledby="assignWorkoutModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="assignWorkoutModalLabel">Assign Workout to {{ $user->name }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <form id="assignWorkoutForm">
                <div class="modal-body">
                  <input type="hidden" name="user_id" value="{{ $user->id }}">
                  <div class="form-group">
                    <label for="workout_id">Select Workout</label>
                    <select class="form-control form-control-solid" id="workout_id" name="workout_id" required>
                      <option value="">Choose a workout...</option>
                      @foreach($workouts as $workout)
                        <option value="{{ $workout->id }}" data-duration="{{ $workout->duration }}">
                          {{ $workout->name }} ({{ $workout->duration }} min)
                        </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="completion_date">Completion Date</label>
                    <input type="date" class="form-control form-control-solid" id="completion_date" name="completion_date" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                  </div>
                  <div class="form-group">
                    <label for="status">Status</label>
                    <select class="form-control form-control-solid" id="status" name="status" required>
                      <option value="assigned">Assigned</option>
                      <option value="in_progress">In Progress</option>
                      <option value="completed">Completed</option>
                      <option value="paused">Paused</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="notes">Notes (Optional)</label>
                    <textarea class="form-control form-control-solid" id="notes" name="notes" rows="3" placeholder="Add any notes about this assignment..."></textarea>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Assign Workout</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <!--end::Assign Workout Modal-->

      </div>
      <!--end::Container-->
    </div>
    <!--end::Entry-->
  </div>
@endsection

@section('stylesheets')
  <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('scripts')
  <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
  <script>
    $(document).ready(function() {
      // Handle form submission for assigning workouts
      $('#assignWorkoutForm').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        Swal.fire({
          title: 'Assigning Workout...',
          html: `
            <div class="text-center">
              <div class="spinner-border text-primary mb-3" role="status">
                <span class="sr-only">Loading...</span>
              </div>
              <p>Please wait while we assign the workout...</p>
            </div>
          `,
          showConfirmButton: false,
          allowOutsideClick: false,
          allowEscapeKey: false
        });
        
        const formData = new FormData(this);
        
        $.ajax({
          url: '{{ route("player-workouts.assign") }}',
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function(response) {
            if (response.success) {
              // Reset form and close modal
              $('#assignWorkoutForm')[0].reset();
              $('#assignWorkoutModal').modal('hide');
              
              // Show success message
              Swal.fire({
                title: 'Workout Assigned! 🎉',
                html: `
                  <div class="text-center">
                    <i class="fas fa-check-circle text-success" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <p class="mb-0">${response.message}</p>
                  </div>
                `,
                icon: 'success',
                confirmButtonText: 'Great!',
                timer: 3000,
                timerProgressBar: true
              }).then(() => {
                location.reload();
              });
            } else {
              Swal.fire({
                title: 'Error!',
                text: response.message,
                icon: 'error',
                confirmButtonText: 'OK'
              });
            }
          },
          error: function(xhr) {
            let message = 'An error occurred while assigning the workout.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
              message = xhr.responseJSON.message;
            }
            Swal.fire({
              title: 'Error!',
              text: message,
              icon: 'error',
              confirmButtonText: 'OK'
            });
          }
        });
      });
      
      // Global functions for edit and delete
      window.editAssignment = function(id) {
        // For now, we'll just show a message that editing is not implemented yet
        Swal.fire({
          title: 'Coming Soon!',
          text: 'Edit functionality will be implemented in the next update!',
          icon: 'info',
          confirmButtonText: 'OK'
        });
      };
      
      window.deleteAssignment = function(id) {
        Swal.fire({
          title: 'Are you sure?',
          text: "You won't be able to revert this!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, delete it!',
          cancelButtonText: 'Cancel'
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: '{{ route("player-workouts.delete", "") }}/' + id,
              type: 'DELETE',
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) {
                if (response.success) {
                  Swal.fire(
                    'Deleted!',
                    response.message,
                    'success'
                  ).then(() => {
                    location.reload();
                  });
                } else {
                  Swal.fire(
                    'Error!',
                    response.message,
                    'error'
                  );
                }
              },
              error: function(xhr) {
                let message = 'An error occurred while deleting the assignment.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                  message = xhr.responseJSON.message;
                }
                Swal.fire(
                  'Error!',
                  message,
                  'error'
                );
              }
            });
          }
        });
      };
      
      // Export stats as CSV function
      window.exportStatsAsCSV = function() {
        // Show loading state
        Swal.fire({
          title: 'Generating CSV...',
          text: 'Please wait while we prepare your export.',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        
        // Get player data
        const playerData = {
          name: '{{ $user->name }}',
          email: '{{ $user->email }}',
          joined_date: '{{ $user->created_at->format("Y-m-d") }}',
          status: '{{ $user->active ? "Active" : "Inactive" }}',
          total_workouts: {{ $playerWorkouts->count() }},
          completed_workouts: {{ $user->playerWorkouts()->where('status', 'completed')->count() }},
          in_progress_workouts: {{ $user->playerWorkouts()->where('status', 'in_progress')->count() }},
          assigned_workouts: {{ $user->playerWorkouts()->where('status', 'assigned')->count() }},
          total_points: {{ $user->playerWorkouts()->where('status', 'completed')->count() }},
          completion_rate: '{{ $playerWorkouts->count() > 0 ? round(($user->playerWorkouts()->where("status", "completed")->count() / $playerWorkouts->count()) * 100) : 0 }}%'
        };
        
        // Create CSV content
        let csvContent = "data:text/csv;charset=utf-8,";
        
        // Add headers
        csvContent += "Player Name,Email,Joined Date,Status,Total Workouts,Completed Workouts,In Progress Workouts,Assigned Workouts,Total Points,Completion Rate\n";
        
        // Add data row
        csvContent += `"${playerData.name}","${playerData.email}","${playerData.joined_date}","${playerData.status}",${playerData.total_workouts},${playerData.completed_workouts},${playerData.in_progress_workouts},${playerData.assigned_workouts},${playerData.total_points},"${playerData.completion_rate}"\n`;
        
        // Add workout details
        csvContent += "\nWorkout Details\n";
        csvContent += "Workout Name,Duration,Status,Assigned Date,Notes\n";
        
        @foreach($playerWorkouts as $assignment)
          csvContent += `"{{ $assignment->workout->name }}",{{ $assignment->workout->duration }},"{{ ucfirst(str_replace('_', ' ', $assignment->status)) }}","{{ $assignment->created_at->format('Y-m-d') }}","{{ $assignment->notes ?? '' }}"\n`;
        @endforeach
        
        // Create download link
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "{{ $user->name }}_workout_stats_" + new Date().toISOString().split('T')[0] + ".csv");
        document.body.appendChild(link);
        
        // Trigger download
        link.click();
        document.body.removeChild(link);
        
        // Show success message
        Swal.fire({
          title: 'Export Successful!',
          text: 'Player statistics have been exported as CSV.',
          icon: 'success',
          confirmButtonText: 'OK'
        });
      };
    });
  </script>
@endsection 