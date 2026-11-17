<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/vehicle-data', 'HomeController@vehicleData')->name('vehicle-data');
Route::get('/', function () {
    return view('auth.login');
});
Route::get('/calculator', function () {
    return view('frontend.calculator');
});
Route::get('/clear',function(){
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
});

Auth::routes();

Route::group([
    'middleware'    => ['auth'],
    'prefix'        => 'client',
    'namespace'     => 'Client'
], function ()
{
    Route::get('/dashboard', 'ClientController@index')->name('client.dashboard');
	Route::get('/profile', 'ClientController@edit')->name('client-profile');
	Route::post('/admin-update', 'ClientController@update')->name('client-update');


});

Route::group([
    'middleware'    => ['auth','is_admin'],
    'prefix'        => 'admin',
    'namespace'     => 'Admin'
], function ()
{
    Route::get('/dashboard', 'AdminController@index')->name('admin.dashboard');
    Route::get('/pdf-download', 'AdminController@downloadPDF')->name('pdf.download');
    Route::get('/dashboard/search', 'AdminController@search')->name('dashboard-search');
    Route::get('/dashboard/category/{id}', 'AdminController@category')->name('dashboard-category');
    Route::get('/profile', 'AdminController@edit')->name('admin-profile');
    Route::post('/admin-update', 'AdminController@update')->name('admin-update');
    Route::post('/shift/start','AdminController@startShift')->name('shift.start');
    Route::put('/shift/close/{id}','AdminController@closeShift')->name('shift.close');

    //Setting Routes
    Route::resource('setting','SettingController');

	//User Routes
	Route::resource('clients','ClientController');
	Route::post('get-clients', 'ClientController@getClients')->name('admin.getClients');
	Route::post('get-client', 'ClientController@clientDetail')->name('admin.getClient');
	Route::get('client/delete/{id}', 'ClientController@destroy');
	Route::post('delete-selected-clients', 'ClientController@deleteSelectedClients')->name('admin.delete-selected-clients');

	   //Team Routes
	   Route::resource('team','TeamController');
	   Route::post('get-team', 'TeamController@getTeam')->name('admin.getTeam');
	   Route::post('get-team-detail', 'TeamController@teamDetail')->name('admin.getTeamDetail');
	   Route::get('team/delete/{id}', 'TeamController@destroy');
	   Route::post('delete-selected-team', 'TeamController@deleteSelectedTeam')->name('admin.delete-selected-team');
	   Route::post('show-subscription-modal', 'TeamController@showSubscriptionModal')->name('admin.show-subscription-modal');
	   Route::post('store-subscription', 'TeamController@storeSubscription')->name('admin.store-subscription');
	   Route::get('team/{id}/members', 'TeamController@showMembers')->name('admin.show-members');
	   Route::post('show-member-payments', 'TeamController@showMemberPayments')->name('admin.show-member-payments');
	   Route::post('show-business-packages', 'TeamController@showBusinessPackages')->name('admin.show-business-packages');
	
	//Workout Type Routes
	Route::resource('workout-types','WorkoutTypeController');
	Route::get('workout-type/delete/{id}', 'WorkoutTypeController@destroy');
	
	//Workout Routes
	Route::resource('workouts','WorkoutController');
	Route::get('workout/delete/{id}', 'WorkoutController@destroy');
	   
	//Supplier Routes - Commented out due to missing controller
	// Route::resource('suppliers','SupplierController');
	// Route::post('get-suppliers', 'SupplierController@getClients')->name('admin.getSuppliers');
	// Route::post('get-supplier', 'SupplierController@clientDetail')->name('admin.getSupplier');
	// Route::post('supplier-history-store', 'SupplierController@historyStore')->name('supplier-history-store');
	// Route::get('supplier/delete/{id}', 'SupplierController@destroy');
	// Route::post('delete-selected-suppliers', 'SupplierController@deleteSelectedClients')->name('admin.delete-selected-suppliers');

	//Main Box Routes - Commented out due to missing controller
	// Route::resource('main-boxes','MainBoxController');
	// Route::post('get-main-boxes', 'MainBoxController@getClients')->name('admin.getMainBoxes');
	// Route::post('get-main-box', 'MainBoxController@clientDetail')->name('admin.getMainBox');
	// Route::post('main-box-history-store', 'MainBoxController@historyStore')->name('main-box-history-store');
	// Route::get('main-box/delete/{id}', 'MainBoxController@destroy');
	// Route::post('delete-selected-main-boxes', 'MainBoxController@deleteSelectedClients')->name('admin.delete-selected-main-boxes');

	//Income Routes - Commented out due to missing controller
	// Route::resource('income-transactions','IncomeTransactionController');
	// Route::post('get-income-transactions', 'IncomeTransactionController@getClients')->name('admin.getIncTransactions');
	// Route::post('get-income-transaction', 'IncomeTransactionController@clientDetail')->name('admin.getIncTransaction');
	// Route::post('income-transaction-history-store', 'IncomeTransactionController@historyStore')->name('income-transaction-history-store');
	// Route::post('credit-transaction-store', 'IncomeTransactionController@creditStore')->name('credit-transaction-store');
	// Route::get('income-transaction/delete/{id}', 'IncomeTransactionController@destroy');
	// Route::get('income-transaction/verify/{id}', 'IncomeTransactionController@verify');
	// Route::post('delete-selected-income-transactions', 'IncomeTransactionController@deleteSelectedClients')->name('admin.delete-selected-income-transactions');

    //Lessons Routes - Commented out due to missing controller
    // Route::resource('tables','TableController');
    // Route::post('tables/get-columns','TableController@getColumns')->name('admin.tables.get-columns');
    // Route::get('user-update','TableController@userUpdate')->name('admin.tables.user-update');
    // Route::get('category-update','TableController@categoryUpdate')->name('admin.tables.category-update');
    // Route::get('article-images-update','TableController@articleImagesUpdate')->name('admin.tables.article-images-update');
    // Route::get('article-cat-update','TableController@articleCatUpdate')->name('admin.tables.article-cat-update');

    //Role Routes
	Route::resource('roles','RoleController');
	Route::post('get-roles', 'RoleController@getClients')->name('admin.getRoles');
	Route::post('get-role', 'RoleController@clientDetail')->name('admin.getRole');
	Route::get('role/delete/{id}', 'RoleController@destroy');
	Route::post('delete-selected-roles', 'RoleController@deleteSelectedClients')->name('admin.delete-selected-roles');


    //Category Routes
	Route::resource('packages','PackageController');
	Route::post('get-packages', 'PackageController@getPackages')->name('admin.getPackages');
	Route::post('get-package', 'PackageController@packageDetail')->name('admin.getPackage');
	Route::get('package/delete/{id}', 'PackageController@destroy');
	Route::post('delete-selected-packages', 'PackageController@deleteSelectedPackages')->name('admin.delete-selected-packages');

	//Member Routes
	Route::resource('members','MemberController');
	Route::post('get-members', 'MemberController@getMembers')->name('admin.getMembers');
	Route::post('get-member', 'MemberController@memberDetail')->name('admin.getMember');
	Route::post('get-member-payment-history', 'MemberController@memberPaymentHistory')->name('admin.getMemberPaymentHistory');
	Route::get('member/delete/{id}', 'MemberController@destroy');
	Route::post('delete-selected-members', 'MemberController@deleteSelectedMembers')->name('admin.delete-selected-members');
	Route::get('members-import', 'MemberController@showImportForm')->name('members.import');
	Route::post('members-import', 'MemberController@import')->name('members.import.store');
	Route::get('members-download-sample', 'MemberController@downloadSample')->name('members.download-sample');
	Route::get('members/{id}/workout-assignment', 'MemberController@showWorkoutAssignment')->name('members.workout-assignment');
	Route::post('members/workout-assignment-modal', 'MemberController@showWorkoutAssignmentModal')->name('members.workout-assignment-modal');
	Route::post('members/store-workout-assignment', 'MemberController@storeWorkoutAssignment')->name('members.store-workout-assignment');
	Route::post('members/get-member-workouts', 'MemberController@getMemberWorkouts')->name('members.get-member-workouts');
	Route::post('members/delete-workout-assignment', 'MemberController@deleteWorkoutAssignment')->name('members.delete-workout-assignment');

    //Payment Routes
	Route::resource('payments','PaymentController');
	Route::get('pending-payments', 'PaymentController@pendingPayments')->name('admin.pending-payments');
	Route::post('get-payments', 'PaymentController@getPayments')->name('admin.getPayments');
	Route::post('get-payments/{id}', 'PaymentController@getPayments')->name('admin.getPaymentsById');
	Route::post('get-pending-payments', 'PaymentController@getPendingPayments')->name('admin.getPendingPayments');
	Route::post('get-pending-payments/{id}', 'PaymentController@getPendingPayments')->name('admin.getPendingPaymentsById');
	Route::post('get-payment', 'PaymentController@paymentDetail')->name('admin.getPayment');
	Route::get('payment/delete/{id}', 'PaymentController@destroy');
	Route::post('delete-selected-payments', 'PaymentController@deleteSelectedPayments')->name('admin.delete-selected-payments');
	Route::get('payment/invoice/{id}', 'PaymentController@printInvoice')->name('admin.print-invoice');
	Route::post('update-payment-status', 'PaymentController@updatePaymentStatus')->name('admin.updatePaymentStatus');

    //Report Routes
	Route::resource('reports','ReportController');
	Route::post('generate-report', 'ReportController@generateReport')->name('admin.generate-report');
	Route::post('download-report-pdf', 'ReportController@downloadPDF')->name('admin.download-report-pdf');
	Route::post('download-report-excel', 'ReportController@downloadExcel')->name('admin.download-report-excel');

    //Subscription Package Routes
	Route::resource('subscription-packages','SubscriptionPackageController');
	Route::post('get-subscription-packages', 'SubscriptionPackageController@getSubscriptionPackages')->name('admin.getSubscriptionPackages');
	Route::post('get-subscription-package', 'SubscriptionPackageController@packageDetail')->name('admin.getSubscriptionPackage');
	Route::post('delete-selected-subscription-packages', 'SubscriptionPackageController@deleteSelectedPackages')->name('admin.delete-selected-subscription-packages');

  
});

