<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\PermitController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\FacultyAdviserController;
// use App\Http\Controllers\StudentEventController;
// use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\BargoController;
use App\Http\Controllers\UserLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PermitTrackingController;
use App\Http\Controllers\SdsoheadController;
use App\Http\Controllers\SasController;
use App\Http\Controllers\Vp_sasController;
use App\Http\Controllers\AdviserCalendarController;
use App\Models\Permit;
use App\Models\User;

// ============================
// AUTH ROUTES
// ============================
Route::post('/reports/store', [PermitTrackingController::class, 'storeReport'])->name('reports.store');

Route::get('/', function () {
  return redirect('/login');
});
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile');
Route::get('/forgot-password', [LoginController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [LoginController::class, 'sendResetLink'])->name('password.forgot');
Route::get('/reset-password/{token}', [LoginController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [LoginController::class, 'resetPassword'])->name('password.update');


Route::get('/logout', function () {
  Auth::logout();
  session()->invalidate();
  session()->regenerateToken();
  return redirect('/login')->with('logout_success', true);
})->name('logout');


// ============================
// AUTHENTICATED ROUTES (COMMON ACCESS)
// Calendar endpoints are placed here to ensure they are defined for all logged-in users,
// fixing the "Route not defined" error caused by specific role middleware.
// ============================
Route::middleware(['auth'])->group(function () {
  // CALENDAR VIEWS (Main page for logged-in users)
  Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

  // CALENDAR API ENDPOINTS
  Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');

  // Permit Form/Submission for Calendar
  Route::get('/calendar/permit/form-content', [CalendarController::class, 'showPermitFormContent'])->name('calendar.permit.form');
  Route::post('/calendar/events/store', [CalendarController::class, 'storeEvent'])->name('calendar.store');

  // PROFILE
  Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
  Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});


// ============================
// ADMIN ROUTES (MERGED CLEAN VERSION)
// ============================
Route::middleware(['auth', 'role:admin'])
  ->prefix('admin')
  ->group(function () {

    // DASHBOARD
    Route::view('/dashboard', 'admin.dashboard')->name('admin.dashboard');
    // ======================
    // USER MANAGEMENT
    // ======================
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::post('/users/update', [UserController::class, 'update'])->name('users.update');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users/list', [UserController::class, 'index'])->name('users.list');
    Route::post('/users/store', [UserController::class, 'store'])->name('users.store');
    Route::post('/users/view', [UserController::class, 'view'])->name('users.view');
    Route::post('/users/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::post('/users/update', [UserController::class, 'update'])->name('users.update');
    Route::delete('users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('users/profile', [UserController::class, 'profile'])->name('admin.users.profile');
    Route::post('/users/signature/', [UserController::class, 'uploadSignature'])->name('admin.uploadSignature');
    Route::delete('/users/signature/', [UserController::class, 'removeSignature'])->name('admin.removeSignature');


    Route::delete(
      '/admin/logs/bulk-delete',
      [UserLogController::class, 'bulkDelete']
    )->name('logs.bulk-delete');

    // Route::get('/admin/users/search', [UserController::class, 'search'])->name('users.search');

        // LOGS
     Route::get('/logs-list', [UserLogController::class, 'index'])->name('users.logs-list');
    Route::get('/logs', [UserLogController::class, 'index'])->name('admin.logs');
        // // ======================
        // // PROFILE (ADMIN ACCOUNT)
        // // ======================
        // Route::get('/profile', [AdminProfileController::class, 'show'])->name('admin.profile.show');
        // Route::put('/profile/update', [AdminProfileController::class, 'update'])->name('admin.profile.update');

        // ======================
        // EVENT REQUEST VIEWS
        // ======================
        Route::view('/event-requests', 'admin.EventRequest.AllRequest');
        Route::view('/event-requests/pending', 'admin.EventRequest.PendingApproval');
        Route::view('/event-requests/approved-events', 'admin.EventRequest.ApprovedEvents');

        // APPROVALS
        Route::view('/approvals/pending', 'admin.approvals.pending');
        Route::view('/approvals/history', 'admin.approvals.history');

        // E-SIGNATURES
        Route::view('/esignatures/pending', 'admin.ESignature.pending');
        Route::view('/esignatures/completed', 'admin.ESignature.completed');

        // ======================
        // ORGANIZATIONS
        // ======================
        Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
        Route::get('/organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
        Route::post('/organizations/store', [OrganizationController::class, 'store'])->name('organizations.store');
        Route::put('/organizations/update', [OrganizationController::class, 'update'])->name('organizations.update');
        Route::post('/organizations/list', [OrganizationController::class, 'index'])->name('organizations.list');
        Route::post('/organizations/edit', [OrganizationController::class, 'edit'])->name('organizations.edit');
        Route::post('/organizations/add', [OrganizationController:: class, 'add'])->name('organizations.add-members');
        Route::post('/organizations/available-students', [OrganizationController::class, 'availableStudents'])->name('organizations.available-students');
        Route::delete('/organizations/{organization_id}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');
        Route::get('/organizations/{organization}', [OrganizationController::class, 'show'])->name('organizations.show');
        // Load add officer page
        Route::get('/organizations/{id}/add-officers', [OrganizationController::class, 'addOfficers'])->name('organizations.add.officers');
        Route::post('/organizations/save-officers', [OrganizationController::class, 'saveOfficers'])
        ->name('organizations.save-officers');

        // Route::delete('/organizations/{organization_id}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');
        // Route::get('/organizations/{organization}', [OrganizationController::class, 'show'])->name('organizations.show');

        // ======================
        // PROFILES (USER PROFILES)
        // ======================
        Route::get('/profiles', [ProfileController::class, 'index'])->name('profiles.index');
        Route::get('/profiles/create', [ProfileController::class, 'create'])->name('profiles.create');
        Route::post('/profiles/store', [ProfileController::class, 'store'])->name('profiles.store');
        Route::post('/profiles/update', [ProfileController::class, 'update'])->name('profiles.update');
        Route::post('/profiles/list', [ProfileController::class, 'index'])->name('profiles.list');
        Route::post('/profiles/edit', [ProfileController::class, 'edit'])->name('profiles.edit');
        Route::post('/profiles/view', [ProfileController::class, 'view'])->name('profiles.view');
        Route::delete('profiles/{profile_id}', [ProfileController::class, 'destroy'])->name('profiles.destroy');


    // Route::post('user/check-username', [UserController::class, 'checkUsername']);



    // Username / Email Availability
    // Route::post('/users/check-availability', [UserController::class, 'checkAvailability'])
    //   ->name('users.checkAvailability');




    // ======================
    // EVENT REQUEST VIEWS
    // ======================
    Route::view('/event-requests', 'admin.EventRequest.AllRequest');
    Route::view('/event-requests/pending', 'admin.EventRequest.PendingApproval');
    Route::view('/event-requests/approved-events', 'admin.EventRequest.ApprovedEvents');

    // APPROVALS
    Route::view('/approvals/pending', 'admin.approvals.pending');
    Route::view('/approvals/history', 'admin.approvals.history');

    // E-SIGNATURES
    Route::view('/esignatures/pending', 'admin.ESignature.pending');
    Route::view('/esignatures/completed', 'admin.ESignature.completed');



    // ======================
    // ORGANIZATIONS
    // ======================
    Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
    Route::get('/organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
    Route::post('/organizations/store', [OrganizationController::class, 'store'])->name('organizations.store');
    Route::put('/organizations/update', [OrganizationController::class, 'update'])->name('organizations.update');
    Route::post('/organizations/list', [OrganizationController::class, 'index'])->name('organizations.list');
    Route::post('/organizations/edit', [OrganizationController::class, 'edit'])->name('organizations.edit');
    Route::post('/organizations/add', [OrganizationController::class, 'add'])->name('organizations.add-members');
    Route::delete('/organizations/{organization_id}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');
    Route::get('/organizations/{organization}', [OrganizationController::class, 'show'])->name('organizations.show');
    // Load add officer page
    Route::get('/organizations/{id}/add-officers', [OrganizationController::class, 'addOfficers'])->name('organizations.add.officers');
    Route::post('/organizations/save-officers', [OrganizationController::class, 'saveOfficers'])
      ->name('organizations.save-officers');

    // ======================
    // PROFILES (USER PROFILES)
    // ======================
    Route::get('/profiles', [ProfileController::class, 'index'])->name('profiles.index');
    Route::get('/profiles/create', [ProfileController::class, 'create'])->name('profiles.create');
    Route::post('/profiles/store', [ProfileController::class, 'store'])->name('profiles.store');
    Route::post('/profiles/update', [ProfileController::class, 'update'])->name('profiles.update');
    Route::post('/profiles/list', [ProfileController::class, 'index'])->name('profiles.list');
    Route::post('/profiles/edit', [ProfileController::class, 'edit'])->name('profiles.edit');
    Route::post('/profiles/view', [ProfileController::class, 'view'])->name('profiles.view');
    Route::delete('profiles/{profile_id}', [ProfileController::class, 'destroy'])->name('profiles.destroy');


    // ACCOUNT SETTINGS
    Route::view('/account', 'admin.profile.account');

    // REPORTING
    Route::view('/reports/minutes', 'admin.reports.minutes');

    // ROLES
    Route::view('/roles', 'admin.users.roles');

    // HELP
    Route::view('/help', 'admin.help.help');
  });

// ============================
// STUDENT ORGANIZATION ROUTES
// ============================
Route::middleware(['auth', 'role:Student_Organization'])->prefix('student')->group(function () {
  Route::get('/dashboard', [PermitTrackingController::class, 'index'])->name('student.dashboard');
  Route::get('/permit/form', [PermitController::class, 'showForm'])->name('permit.form');
  Route::post('/permit/generate', [PermitController::class, 'generate'])->name('permit.generate');
  Route::get('/permit/tracking', [PermitController::class, 'track'])->name('student.permit.tracking');

  // Calendar view route (the main page) - This is now just a helper view route.
  // Display the calendar page
  Route::get('/calendar', function () {return view('student.calendardisplay');})->name('calendar.index');

  // API endpoint to fetch calendar events (permits)
  Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');  // Get the permit form (if you have a separate view for the form)
  Route::get('/calendar/permit-form', function () {return view('calendar.permit-form');})->name('student.permit.form');

  // Store new permit
  Route::post('/calendar/store', [CalendarController::class, 'store'])->name('calendar.store');
  Route::get('/student/permit/download/{hashed_id}', [PermitController::class, 'download'])->name('student.permit.download');
  Route::get('/permit/view/{hashed_id}', [PermitController::class, 'view'])->name('student.permit.view');
  Route::get('/permit/download/{hashedId}', [PermitController::class, 'download'])->name('permit.download');

  //profiles
  Route::put('/profile/contact', [UserController::class, 'updateContact'])->name('user.updateContact');
  Route::get('/profile', function () {return view('student.profile');})->name('user.profile');
  Route::post('/profile/signature/', [UserController::class, 'uploadSignature'])->name('student.uploadSignature');
  Route::delete('/profile/signature/', [UserController::class, 'removeSignature'])->name('student.removeSignature');
  // contact update Routeer')->group(function () {
  // routes/web.php

  Route::get('page/pending-permits', [PermitTrackingController::class, 'pendingPage'])->name('student.page.pending');
  Route::get('page/approved-permits', [PermitTrackingController::class, 'approvedPage'])->name('student.page.approved');
  Route::get('page/rejected-permits', [PermitTrackingController::class, 'rejectedPage'])->name('student.page.rejected');
  Route::get('/successful', [PermitTrackingController::class, 'successfulPage'])->name('successful');
  Route::post('/reports', [PermitTrackingController::class, 'storeReport'])->name('reports.store');
  Route::get('/ongoing', [PermitTrackingController::class, 'ongoingPage'])->name('student.ongoing');
  Route::get('/successful/{hashed_id}/reports', [PermitTrackingController::class, 'showReports'])->name('student.reports.show');
  Route::post('/ongoing/{permit}/complete', [PermitTrackingController::class, 'markAsCompleted'])->name('student.ongoing.complete');
  Route::post('/successful/submit/{hashed_id}', [PermitTrackingController::class, 'submitToSdso'])->name('student.permit.submit'); // View PDF securely (ensure hashed_id works)
  Route::post('/successful/{hashed_id}/submit-to-sdso', [PermitTrackingController::class, 'submitToSdso'])->name('student.permit.submit');
  Route::get('/adviser/permit/view/{hashed_id}', [FacultyAdviserController::class, 'viewPermitPdf'])->name('faculty.permit.view');
  Route::get('/student/page/submissions-history', [PermitTrackingController::class, 'submissionsHistory'])->name('student.submissions.history');

  // Approve & Reject
  Route::post('/permit/{approval_id}/approve', [FacultyAdviserController::class, 'approve'])->name('faculty.approve');
  Route::post('/permit/{approval_id}/reject', [FacultyAdviserController::class, 'reject'])->name('faculty.reject');



  // View PDF securely (ensure hashed_id works)
  Route::get('/adviser/permit/view/{hashed_id}', [FacultyAdviserController::class, 'viewPermitPdf'])->name('faculty.permit.view');


  // Approve & Reject
  Route::post('/permit/{approval_id}/approve', [FacultyAdviserController::class, 'approve'])->name('faculty.approve');
  Route::post('/permit/{approval_id}/reject', [FacultyAdviserController::class, 'reject'])->name('faculty.reject');
});


// ==================================================================
// FACULTY ADVISER ROUTES
// ==================================================================
Route::prefix('adviser')->name('adviser.')->middleware(['auth', 'role:Faculty_Adviser'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [FacultyAdviserController::class, 'dashboard'])->name('dashboard');
    // Pending Approvals List
    Route::get('/approvals', [FacultyAdviserController::class, 'approvals'])->name('approvals');
    Route::get('/history', [FacultyAdviserController::class, 'approvalHistory'])->name('history');


    // ————————————————————————————————————————
    // SECURE PERMIT REVIEW & ACTIONS (using hashed_id)
    // ————————————————————————————————————————
    Route::prefix('permit')->name('permit.')->group(function () {

        // Full Review Page (Beautiful & Secure)
    Route::get('/{hashedId}/review', [FacultyAdviserController::class, 'review'])->name('review');

        // Approve Permit
    Route::post('/{approval_id}/approve', [FacultyAdviserController::class, 'approve'])
            ->name('approve');

        // Reject Permit (with reason)
    Route::post('/{approval_id}/reject', [FacultyAdviserController::class, 'reject'])
            ->name('reject');

        // View Generated PDF
    Route::get('/{hashed_id}/pdf', [FacultyAdviserController::class, 'viewPermitPdf'])
            ->name('pdf');
    });

    // Legacy model-binding route (if you still use it somewhere)
    Route::get('/permits/{permit}', function (Permit $permit) {
        return app(FacultyAdviserController::class)->show($permit);
    })->name('permits.show');

     // ————————————————————————————————————————
    // Profile & Signature
    // ————————————————————————————————————————
      Route::post('/profile/signature/', [UserController::class, 'uploadSignature'])->name('uploadSignature');
      Route::delete('/profile/signature', [UserController::class, 'removeSignature'])->name('removeSignature');

      Route::prefix('profile')->name('profile.')->group(function () {
      Route::get('/', fn() => view('adviser.profile'))->name('index');
      Route::put('/contact', [UserController::class, 'updateContact'])->name('updateContact');


    });

    // ————————————————————————————————————————
    // Calendar
    // ————————————————————————————————————————
    Route::prefix('calendar')->name('calendar.')->group(function () {
        Route::get('/', [AdviserCalendarController::class, 'index'])->name('index');
        Route::get('/events', [AdviserCalendarController::class, 'getEvents'])->name('events');
    });

    // ————————————————————————————————————————
    // Notifications
    // ————————————————————————————————————————
    // Route::prefix('notifications')->name('notifications.')->group(function () {
    //     Route::get('/', [NotificationController::class, 'index'])->name('index');
    //     Route::get('/data', [PermitController::class, 'notificationsData'])->name('data');
    //     Route::get('/unread', [NotificationController::class, 'unread'])->name('unread');
    //     Route::post('/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
    // });

    // ————————————————————————————————————————
    // Approved Permits History (optional — add controller later)
    // ————————————————————————————————————————
    Route::get('/permits', [PermitController::class, 'index'])
        ->name('permits.index');
});





// ============================
// BARGO ROUTES — FULLY WORKING SIGNATURE UPLOAD
// ============================
Route::middleware(['auth', 'role:BARGO'])->prefix('bargo')->name('bargo.')->group(function () {

    // === MAIN PAGES ===
    Route::get('/dashboard', [BargoController::class, 'dashboard'])->name('dashboard');
    Route::get('/pending', [BargoController::class, 'pending'])->name('pending');
    Route::get('/approved', [BargoController::class, 'approved'])->name('approved');
    Route::get('/rejected', [BargoController::class, 'rejected'])->name('rejected');
    Route::get('/history', [BargoController::class, 'history'])->name('history');

    Route::get('/permit/{hashed_id}/pdf', [BargoController::class, 'viewPermitPdf'])->name('permit.pdf');
    Route::post('/approve/{approval_id}', [BargoController::class, 'approve'])->name('approve');
    Route::post('/reject/{approval_id}', [BargoController::class, 'reject'])->name('reject');

    // === BARGO PROFILE & SIGNATURE (FIXED & WORKING) ===
    Route::get('/profile', fn() => view('bargo.profile'))->name('profile');

    // These routes now match your Blade file exactly
    Route::post('/profile/signature/', [UserController::class, 'uploadSignature'])
        ->name('uploadSignature');

    Route::delete('/profile/signature', [UserController::class, 'removeSignature'])
        ->name('removeSignature');

    // Fixed: Correct view + proper nesting
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', fn() => view('bargo.profile'))->name('index'); // Changed from adviser.profile
        Route::put('/contact', [UserController::class, 'updateContact'])->name('updateContact');
    });

    Route::get('/calendar', [BargoController::class, 'calendar'])->name('bargo.calendar');
    Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');
    Route::post('/calendar/events', [BargoController::class, 'storeBargoEvent'])->name('calendar.store');
    Route::put('/calendar/events/{event}', [BargoController::class, 'updateBargoEvent'])->name('calendar.update');
    Route::delete('/calendar/events/{event}', [BargoController::class, 'deleteBargoEvent'])->name('calendar.delete');
});

// ============================
// OTHER ROLES
// ============================


//SDSO

Route::middleware(['auth', 'role:SDSO_Head'])->group(function () {
  Route::view('/sdso/dashboard', 'sdso.dashboard')->name('sdso.dashboard');
  Route::get('sdso/profile', [SdsoheadController::class, 'profile'])->name('sdso.profile');
  Route::get('/sdso/events/pending', [SdsoheadController::class, 'pending'])->name('sdso.events.pending');
  Route::get('/sdso/events/approved', [SdsoheadController::class, 'approved'])->name('sdso.events.approved');
  Route::get('/sdso/events/history', [SdsoheadController::class, 'history'])->name('sdso.events.history');
});



//VPSAS
Route::middleware(['auth', 'role:VP_SAS'])->group(function () {
  Route::view('/vpsas/dashboard', 'vp_sas.dashboard')->name('vpsas.dashboard');
  Route::get('vpsas/profile', [Vp_sasController::class, 'profile'])->name('vpsas.profile');
});


//SASDIRECTOR
Route::middleware(['auth', 'role:SAS_Director'])->group(function () {
  Route::view('/sas/dashboard', 'sas.dashboard')->name('sas.dashboard');
  Route::get('sas/profile', [SasController::class, 'profile'])->name('sas.profile');
});


Route::get('/adviser/temp/view/{hashed_id}', [FacultyAdviserController::class, 'viewTempPdf'])
  ->name('adviser.view.temp.pdf');
