<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Facility\OfficeController;
use App\Http\Controllers\Lms\BookCategoryController;
use App\Http\Controllers\Lms\BookshelveController;
use App\Http\Controllers\Lms\BookController;
use App\Http\Controllers\Lms\LostBookController;
use App\Http\Controllers\Lms\MemberController;
use App\Http\Controllers\Lms\IssueController;
use App\Http\Controllers\Facility\CabBookingController;
use App\Http\Controllers\Facility\TrainBookingController;
use App\Http\Controllers\Facility\FlightBookingController;
use App\Http\Controllers\Facility\HotelBookingController;
use App\Http\Controllers\Facility\PropertyController;
use App\Http\Controllers\Facility\MatterCodeController;
use App\Http\Controllers\Cave\CaveFormController;
use App\Http\Controllers\Cave\CaveLocationController;
use App\Http\Controllers\Cave\CaveCategoryController;
use App\Http\Controllers\Front\Auth\FrontAuthController;
Use App\Http\Controllers\Front\FlightController;
Use App\Http\Controllers\Front\TrainController;
Use App\Http\Controllers\Front\CabController;
use App\Http\Controllers\Front\HotelController;
use Illuminate\Support\Facades\Route;
Route::get('/cache-clear', function() {
	// \Artisan::call('route:cache');
	\Artisan::call('config:cache');
	\Artisan::call('permission:cache-reset');
   //	\Artisan::call('cache:clear');
	\Artisan::call('view:clear');
	\Artisan::call('config:clear');
	\Artisan::call('view:cache');
	\Artisan::call('route:clear');
	dd('Cache cleared');
});
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
//Route::group(['middleware' => ['role:super-admin|lms-admin']], function() {
Route::group(['middleware' => ['auth']], function() {

    Route::resource('permissions', App\Http\Controllers\PermissionController::class);
    Route::get('permissions/{permissionId}/delete', [App\Http\Controllers\PermissionController::class, 'destroy']);

    Route::resource('roles', App\Http\Controllers\RoleController::class);
    Route::get('roles/{roleId}/delete', [App\Http\Controllers\RoleController::class, 'destroy']);
    Route::get('roles/{roleId}/give-permissions', [App\Http\Controllers\RoleController::class, 'addPermissionToRole']);
    Route::put('roles/{roleId}/give-permissions', [App\Http\Controllers\RoleController::class, 'givePermissionToRole']);

    Route::resource('users', App\Http\Controllers\UserController::class);
    Route::get('users/{userId}/delete', [App\Http\Controllers\UserController::class, 'destroy']);
    
    
    //facility
    
    Route::resource('offices', OfficeController::class);
    Route::get('offices/{userId}/delete', [OfficeController::class, 'destroy']);
    Route::get('offices/{userId}/issue/list', [OfficeController::class, 'bookIssueList']);
    Route::get('offices/{userId}/available/list', [OfficeController::class, 'availableBookList']);
    
    //lms
    Route::resource('bookcategories', BookCategoryController::class);
    Route::get('bookcategories/{userId}/delete', [BookCategoryController::class, 'destroy']);
    Route::get('bookcategories/{userId}/status/change', [BookCategoryController::class, 'status']);
    
    
    Route::resource('bookshelves', BookshelveController::class);
    Route::get('bookshelves/{userId}/delete', [BookshelveController::class, 'destroy']);
    Route::get('bookshelves/export/csv', [BookshelveController::class, 'csvExport']);
    Route::post('bookshelves/upload/csv', [BookshelveController::class, 'csvImport']);
    
    
    Route::resource('books', BookController::class);
    Route::get('books/{userId}/delete', [BookController::class, 'destroy']);
    Route::get('books/{userId}/status/change', [BookController::class, 'status']);
    Route::get('books/export/csv', [BookController::class, 'csvExport']);
    Route::post('books/upload/csv', [BookController::class, 'csvImport']);
    Route::post('books/update/csv/upload', [BookController::class, 'bookUpdatedCsv']);
    Route::get('bookshelves/list/officewise/{userId}', [BookController::class, 'bookshelveOffice']);
    Route::get('books/{userId}/issue/list', [BookController::class, 'bookIssueList']);
    Route::get('books/issue/list/export/csv/{id}', [BookController::class, 'bookIssuecsvExport']);
    
    //lost books
     Route::resource('lostbooks', LostBookController::class);
     Route::get('lostbooks/{userId}/delete', [LostBookController::class, 'destroy']);
     Route::get('lost/books/{userId}/status/change', [LostBookController::class, 'status']);
     Route::get('lost/books/export/csv', [LostBookController::class, 'csvExport']);
     Route::post('lost/books/upload/csv', [LostBookController::class, 'csvImport']);
    
    //history
    
    Route::get('books/{userId}/history', [BookController::class, 'bookHistory']);
    Route::get('books/history/export/csv/{id}', [BookController::class, 'bookHistorycsvExport']);
    
    
    Route::get('bookshelves/get/{userId}', [BookController::class, 'bookshelveDetail']);
    //total available books per office
    Route::get('offices/available/books/{officeId}/list', [BookController::class, 'availableBookListOffice']);
    //total issue books per office
    Route::get('offices/issue/books/{officeId}/list', [BookController::class, 'issueBookListOffice']);
    
    //unreturned book list
    Route::get('unreturned/books/list', [BookController::class, 'unreturnedBookList']);
    Route::get('unreturned/books/export/csv', [BookController::class, 'unreturnedBookcsvExport']);
    //bulk issue
    Route::get('bulk-issue/books/list', [BookController::class, 'bulkissueBookList']);
    Route::get('bulk-issue/books/export/csv', [BookController::class, 'bulkissueBookcsvExport']);
    //test book delete
    Route::post('test/books/delete', [BookController::class, 'testBookDelete']);
    //member
    Route::resource('members', MemberController::class);
    Route::get('members/{userId}/delete', [MemberController::class, 'destroy']);
    Route::get('members/{userId}/status/change', [MemberController::class, 'status']);
    Route::get('members/{userId}/issue/list', [MemberController::class, 'bookIssueList']);
    Route::get('members/issue/list/csv/export', [MemberController::class, 'bookIssueListcsvExport']);
    Route::get('members/export/csv', [MemberController::class, 'csvExport']);
    Route::post('members/upload/csv', [MemberController::class, 'csvImport']);
    Route::post('/members/permissions/{id}', [MemberController::class, 'getPermissionsAndMembers'])->name('members.getPermissionsAndMembers');
    Route::delete('/members/permissions/delete{id}', [MemberController::class, 'getPermissionsAndMembersDelete'])->name('members.getPermissionsAndMembers.delete');
    //all issue list
    Route::resource('issues', IssueController::class);
    Route::get('issues/books/{userId}/status/change', [IssueController::class, 'status']);
    
    //cab booking
     Route::get('cab-booking/list', [CabBookingController::class, 'index']);
     Route::get('cab-booking/export/csv', [CabBookingController::class, 'csvExport'])->name('cab-booking.export.csv');
     Route::get('cab-booking/details/{id}', [CabBookingController::class, 'show']);
     Route::get('cab-booking/{id}/edit', [CabBookingController::class, 'edit']);
     Route::post('cab-booking/{id}/update', [CabBookingController::class, 'update']);
     Route::get('cab-booking/status/change/{id}/{status}', [CabBookingController::class, 'status']);
     Route::post('cab-booking/{id}/upload-ticket', [CabBookingController::class, 'uploadTicket'])->name('cab.upload.ticket');
     
     Route::get('train-booking/list', [TrainBookingController::class, 'index']);
     Route::get('train-booking/export/csv', [TrainBookingController::class, 'csvExport'])->name('train-booking.export.csv');
     Route::get('train-booking/details/{id}', [TrainBookingController::class, 'show']);
     Route::get('train-booking/{id}/edit', [TrainBookingController::class, 'edit']);
     Route::post('train-booking/{id}/update', [TrainBookingController::class, 'update']);
     Route::get('train-booking/status/change/{id}/{status}', [TrainBookingController::class, 'status']);
     Route::post('train-booking/{id}/upload-ticket', [TrainBookingController::class, 'uploadTicket'])->name('train.upload.ticket');
     
     Route::get('flight-booking/list', [FlightBookingController::class, 'index']);
     Route::get('flight-booking/export/csv', [FlightBookingController::class, 'csvExport'])->name('flight-booking.export.csv');
     Route::get('flight-booking/details/{id}', [FlightBookingController::class, 'show']);
     Route::get('flight-booking/{id}/edit', [FlightBookingController::class, 'edit']);
     Route::post('flight-booking/{id}/update', [FlightBookingController::class, 'update']);
     Route::get('flight-booking/status/change/{id}/{status}', [FlightBookingController::class, 'status']);
     Route::post('flight-booking/{id}/upload-ticket', [FlightBookingController::class, 'uploadTicket'])->name('flight.upload.ticket');
     
     Route::get('hotel-booking/list', [HotelBookingController::class, 'index']);
     Route::get('hotel-booking/export/csv', [HotelBookingController::class, 'csvExport'])->name('hotel-booking.export.csv');
     Route::get('hotel-booking/details/{id}', [HotelBookingController::class, 'show']);
     Route::get('hotel-booking/{id}/edit', [HotelBookingController::class, 'edit']);
     Route::post('hotel-booking/{id}/update', [HotelBookingController::class, 'update']);
     Route::get('hotel-booking/status/change/{id}/{status}', [HotelBookingController::class, 'status']);
     
     Route::get('edit-logs/list', [HotelBookingController::class, 'editLogs']);
     Route::get('edit-logs/export/csv', [HotelBookingController::class, 'editLogscsvExport'])->name('edit-logs.export.csv');
     
     Route::resource('matter-code', MatterCodeController::class);
     Route::get('downloadSampleCsv', [MatterCodeController::class, 'downloadSampleCsv'])->name('mattercode.sample');
     Route::post('uploadCsv', [MatterCodeController::class, 'uploadCsv'])->name('mattercode.uploadCsv');
     Route::get('cab-booking/matter-code/suggest', [MatterCodeController::class, 'suggestMatterCode'])->name('matter-code.suggest');

     Route::resource('properties', PropertyController::class);
     
     //cave
     Route::resource('vaults', CaveFormController::class);
     Route::get('vaults/export/csv', [CaveFormController::class, 'csvExport'])->name('vaults.export.csv');
      //unreturned book list
        Route::get('outside/vault/list', [CaveFormController::class, 'unreturnedVaultList'])->name('outside.vault.list');
        Route::get('outside/vault/export/csv', [CaveFormController::class, 'unreturnedVaultListcsvExport']);
     Route::get('vaults/{id}/delete', [CaveFormController::class, 'destroy'])->name('vaults.delete');
      Route::get('vaults/{userId}/takeout/list', [CaveFormController::class, 'takeoutList']);
      Route::get('vaults/takeout/list/export/csv/{id}', [CaveFormController::class, 'takeoutListcsvExport']);
      
     Route::resource('vaultlocations', CaveLocationController::class);
     Route::get('vaultlocations/{id}/delete', [CaveLocationController::class, 'destroy'])->name('vaultlocations.delete');
     
     Route::resource('vaultcategories', CaveCategoryController::class);
     Route::get('vaultcategories/{id}/delete', [CaveCategoryController::class, 'destroy'])->name('vaultcategories.delete');
     Route::get('room/list/locationwise/{id}', [CaveCategoryController::class, 'roomList'])->name('room.list');
     
     
     //station list upload
     Route::post('stationlist/upload/', [TrainBookingController::class, 'upload'])->name('stationlist.upload');
     
});

// ---------------- FRONT USER LOGIN (OTP) ----------------
Route::prefix('front')->name('front.')->group(function () {
    Route::get('/login', [FrontAuthController::class, 'showLoginForm'])->name('login')->middleware('guest.front');
    Route::post('/send-otp', [FrontAuthController::class, 'sendOtp'])->name('send.otp');
    Route::post('/verify-otp', [FrontAuthController::class, 'verifyOtp'])->name('verify.otp');
    Route::post('/logout', [FrontAuthController::class, 'logout'])->name('logout');

    Route::middleware('auth.front_user')->group(function () {
        Route::get('/dashboard', [FrontAuthController::class, 'dashboard'])->name('dashboard');
        Route::get('/travel/dashboard', [FrontAuthController::class, 'travel'])->name('travel.dashboard');
        Route::get('/library/dashboard', [FrontAuthController::class, 'library'])->name('library.dashboard');
        Route::get('/cavity/dashboard', [FrontAuthController::class, 'cavity'])->name('cavity.dashboard');
        

        Route::prefix('travel')->name('travel.')->group(function () {

            Route::get('/flight', [FlightController::class, 'index'])->name('flight.index');
            Route::post('/flight/store', [FlightController::class, 'store'])->name('flight.store');
            Route::get('/flight/searchAirports', [FlightController::class, 'searchAirports'])->name('flight.search');
            Route::get('/flight/history', [FlightController::class, 'history'])->name('flight.history');
            Route::post('/flight/cancelBooking',[FlightController::class,'cancelBooking'])->name('flight.cancel');
            Route::get('/flight/{id}/edit',[FlightController::class,'edit'])->name('flight.edit');
            Route::post('/flight/update',[FlightController::class,'update'])->name('flight.update');
        

            Route::get('/train', [TrainController::class, 'index'])->name('train.index');
            Route::post('/train/store', [TrainController::class, 'store'])->name('train.store');
            Route::get('/train/searchStation', [TrainController::class, 'searchStation'])->name('train.search');
            Route::get('/train/history', [TrainController::class, 'history'])->name('train.history');
            Route::post('/train/cancelBooking',[TrainController::class,'cancelBooking'])->name('train.cancel');
            Route::get('/train/{id}/edit',[TrainController::class,'edit'])->name('train.edit');
            Route::post('/train/update',[TrainController::class,'update'])->name('train.update');

            Route::get('/cab', [CabController::class, 'index'])->name('cab.index');
            Route::post('/cab/store', [CabController::class, 'store'])->name('cab.store');
            Route::get('/cab/history', [CabController::class, 'history'])->name('cab.history');
            Route::post('/cab/cancelBooking',[CabController::class,'cancelBooking'])->name('cab.cancel');
            Route::get('/cab/{id}/edit',[CabController::class,'edit'])->name('cab.edit');
            Route::post('/cab/update',[CabController::class,'update'])->name('cab.update');

            Route::get('/hotel', [HotelController::class, 'index'])->name('hotel.index');
            Route::post('/hotel/store', [HotelController::class, 'store'])->name('hotel.store');
            Route::get('/hotel/history', [HotelController::class, 'history'])->name('hotel.history');
            Route::post('/hotel/cancelBooking',[HotelController::class,'cancelBooking'])->name('hotel.cancel');
            Route::get('/hotel/{id}/edit',[HotelController::class,'edit'])->name('hotel.edit');
            Route::post('/hotel/update',[HotelController::class,'update'])->name('hotel.update');

            Route::get('/matter-code/search',[MatterCodeController::class,'suggestMatterCode'])->name('matter-code.suggest');
        });
       // Route::get('/erp/dashboard', [FrontAuthController::class, 'erp'])->name('erp.dashboard');
        // Route::get('/ildms/dashboard', [FrontAuthController::class, 'ildms'])->name('ildms.dashboard');

    });
});
