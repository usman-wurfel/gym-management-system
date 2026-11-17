<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\MemberAuthController;
use App\Http\Controllers\Api\WorkoutTypeController;
use App\Http\Controllers\Api\WorkoutController;
use App\Http\Controllers\Api\MemberWorkoutController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public routes (no authentication required)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Member authentication routes (public)
Route::post('/member/login', [MemberAuthController::class, 'login']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/password', [AuthController::class, 'updatePassword']);
    
    // Role Management Routes
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::get('/permissions', [RoleController::class, 'getPermissions']);
        Route::post('/', [RoleController::class, 'store']);
        Route::get('/{id}', [RoleController::class, 'show']);
        Route::get('/{id}/permissions', [RoleController::class, 'getRolePermissions']);
        Route::put('/{id}', [RoleController::class, 'update']);
        Route::delete('/{id}', [RoleController::class, 'destroy']);
        Route::post('/delete-selected', [RoleController::class, 'deleteSelected']);
    });
    
    // Staff Management Routes
    Route::prefix('staff')->group(function () {
        Route::get('/', [StaffController::class, 'index']);
        Route::get('/roles', [StaffController::class, 'getRoles']);
        Route::post('/', [StaffController::class, 'store']);
        Route::get('/{id}', [StaffController::class, 'show']);
        Route::get('/{id}/roles', [StaffController::class, 'getStaffRoles']);
        Route::put('/{id}', [StaffController::class, 'update']);
        Route::delete('/{id}', [StaffController::class, 'destroy']);
        Route::post('/delete-selected', [StaffController::class, 'deleteSelected']);
    });
    
    // Package Management Routes
    Route::prefix('packages')->group(function () {
        Route::get('/', [PackageController::class, 'index']);
        Route::post('/', [PackageController::class, 'store']);
        Route::get('/{id}', [PackageController::class, 'show']);
        Route::get('/{id}/edit', [PackageController::class, 'getPackageForEdit']);
        Route::put('/{id}', [PackageController::class, 'update']);
        Route::delete('/{id}', [PackageController::class, 'destroy']);
        Route::post('/delete-selected', [PackageController::class, 'deleteSelected']);
    });
    
    // Member Management Routes
    Route::prefix('members')->group(function () {
        Route::get('/', [MemberController::class, 'index']);
        Route::get('/packages', [MemberController::class, 'getPackages']);
        Route::post('/', [MemberController::class, 'store']);
        Route::get('/{id}', [MemberController::class, 'show']);
        Route::get('/{id}/edit', [MemberController::class, 'getMemberForEdit']);
        Route::get('/{id}/payment-history', [MemberController::class, 'getMemberPaymentHistory']);
        Route::put('/{id}', [MemberController::class, 'update']);
        Route::delete('/{id}', [MemberController::class, 'destroy']);
        Route::post('/delete-selected', [MemberController::class, 'deleteSelected']);
        Route::get('/download-sample', [MemberController::class, 'downloadSample']);
        Route::post('/import', [MemberController::class, 'import']);
    });

    // Payment Management Routes
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);
        Route::get('/pending', [PaymentController::class, 'getPendingPayments']);
        Route::get('/members-packages', [PaymentController::class, 'getMembersAndPackages']);
        Route::post('/', [PaymentController::class, 'store']);
        Route::get('/{id}', [PaymentController::class, 'show']);
        Route::get('/{id}/edit', [PaymentController::class, 'getPaymentForEdit']);
        Route::put('/{id}', [PaymentController::class, 'update']);
        Route::delete('/{id}', [PaymentController::class, 'destroy']);
        Route::post('/delete-selected', [PaymentController::class, 'deleteSelected']);
    });

    // Report Management Routes
    Route::prefix('reports')->group(function () {
        Route::post('/generate', [ReportController::class, 'generateReport']);
        Route::post('/download-pdf', [ReportController::class, 'downloadPDF']);
        Route::post('/download-excel', [ReportController::class, 'downloadExcel']);
        Route::get('/dashboard-stats', [ReportController::class, 'getDashboardStats']);
    });

    // Workout Type Management Routes
    Route::prefix('workout-types')->group(function () {
        Route::get('/', [WorkoutTypeController::class, 'index']);
        Route::post('/', [WorkoutTypeController::class, 'store']);
        Route::get('/{id}', [WorkoutTypeController::class, 'show']);
        Route::put('/{id}', [WorkoutTypeController::class, 'update']);
        Route::delete('/{id}', [WorkoutTypeController::class, 'destroy']);
        Route::post('/delete-selected', [WorkoutTypeController::class, 'deleteSelected']);
    });

    // Workout Management Routes
    Route::prefix('workouts')->group(function () {
        Route::get('/', [WorkoutController::class, 'index']);
        Route::get('/workout-types', [WorkoutController::class, 'getWorkoutTypes']);
        Route::post('/', [WorkoutController::class, 'store']);
        Route::get('/{id}', [WorkoutController::class, 'show']);
        Route::put('/{id}', [WorkoutController::class, 'update']);
        Route::delete('/{id}', [WorkoutController::class, 'destroy']);
        Route::post('/delete-selected', [WorkoutController::class, 'deleteSelected']);
    });

    // Member Workout Assignment Routes
    Route::prefix('member-workouts')->group(function () {
        Route::get('/', [MemberWorkoutController::class, 'index']);
        Route::get('/assignment-data', [MemberWorkoutController::class, 'getAssignmentData']);
        Route::get('/today', [MemberWorkoutController::class, 'getTodayWorkouts']);
        Route::get('/by-date', [MemberWorkoutController::class, 'getWorkoutsByDate']);
        Route::post('/', [MemberWorkoutController::class, 'store']);
        Route::post('/assign', [MemberWorkoutController::class, 'assignWorkout']);
        Route::get('/{id}', [MemberWorkoutController::class, 'show']);
        Route::put('/{id}', [MemberWorkoutController::class, 'update']);
        Route::delete('/{id}', [MemberWorkoutController::class, 'destroy']);
        Route::post('/delete-selected', [MemberWorkoutController::class, 'deleteSelected']);
        Route::get('/member/{memberId}', [MemberWorkoutController::class, 'getMemberWorkouts']);
    });
});

// Member authentication routes (protected - requires member token)
Route::middleware('auth:sanctum')->prefix('member')->group(function () {
    Route::post('/logout', [MemberAuthController::class, 'logout']);
    Route::get('/profile', [MemberAuthController::class, 'profile']);
    Route::put('/password', [MemberAuthController::class, 'updatePassword']);
    Route::get('/workouts', [MemberAuthController::class, 'getAllWorkouts']);
    Route::get('/workouts/today', [MemberAuthController::class, 'getCurrentDayWorkouts']);
    Route::get('/workouts/date', [MemberAuthController::class, 'getWorkoutsByDate']);
});
