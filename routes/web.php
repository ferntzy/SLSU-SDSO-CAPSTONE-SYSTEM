<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\PermitController;
use App\Http\Controllers\StudentEventController;
use App\Http\Controllers\FacultyAdviserController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\BargoController;
use App\Http\Controllers\UserLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\PermitTrackingController;

// ============================
// AUTH ROUTES
// ============================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

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
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
<<<<<<< Updated upstream
=======
        Route::post('/users/list', [UserController::class, 'index'])->name('users.list');
>>>>>>> Stashed changes
        Route::post('/users/store', [UserController::class, 'store'])->name('users.store');
        Route::post('/users/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::post('/users/update', [UserController::class, 'update'])->name('users.update');

        Route::get('/admin/users/search', [UserController::class, 'search'])->name('users.search');


<<<<<<< Updated upstream
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');



        // Route::post('user/check-username', [UserController::class, 'checkUsername']);



=======
>>>>>>> Stashed changes
        // Username / Email Availability
        Route::post('/users/check-availability', [UserController::class, 'checkAvailability'])
            ->name('users.checkAvailability');

        // LOGS
        Route::get('/logs', [UserLogController::class, 'index'])->name('admin.logs');

        // ======================
        // PROFILE (ADMIN ACCOUNT)
        // ======================
        Route::get('/profile', [AdminProfileController::class, 'show'])->name('admin.profile.show');
        Route::put('/profile/update', [AdminProfileController::class, 'update'])->name('admin.profile.update');

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
        Route::post('/organizations/store', [OrganizationController::class, 'store'])->name('organizations.store');
        Route::put('/organizations/{organization}', [OrganizationController::class, 'update'])->name('organizations.update');
        Route::delete('/organizations/{organization_id}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');
        Route::get('/organizations/{organization}', [OrganizationController::class, 'show'])->name('organizations.show');

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
    Route::view('/calendar', 'student.calendardisplay');

    Route::get('/permit/view/{hashed_id}', [PermitController::class, 'view'])->name('student.permit.view');


    //profiles
        Route::put('/profile/contact', [UserController::class, 'updateContact'])->name('user.updateContact');
    Route::get('/profile', function() { return view('student.profile'); })->name('user.profile');
    Route::post('/profile/signature/',[UserController::class, 'uploadSignature'])->name('user.uploadSignature');
    Route::delete('/profile/signature/',[UserController::class, 'removeSignature'])->name('user.removeSignature');
    // contact update Routeer')->group(function () {



    // View PDF securely (ensure hashed_id works)
    Route::get('/adviser/permit/view/{hashed_id}', [FacultyAdviserController::class, 'viewPermitPdf'])
        ->name('faculty.permit.view');


    // Approve & Reject
    Route::post('/permit/{approval_id}/approve', [FacultyAdviserController::class, 'approve'])
        ->name('faculty.approve');
    Route::post('/permit/{approval_id}/reject', [FacultyAdviserController::class, 'reject'])
        ->name('faculty.reject');
});



// ============================
// FACULTY ADVISER ROUTES
// ============================
Route::middleware(['auth', 'role:Faculty_Adviser'])->prefix('adviser')->group(function () {

    Route::get('/dashboard', [FacultyAdviserController::class, 'dashboard'])
        ->name('adviser.dashboard');

    Route::get('/approvals', [FacultyAdviserController::class, 'approvals'])
        ->name('adviser.approvals');

    // View PDF securely (ensure hashed_id works)
    Route::get('/adviser/permit/view/{hashed_id}', [FacultyAdviserController::class, 'viewPermitPdf'])
        ->name('faculty.permit.view');


    // Approve & Reject
    Route::post('/permit/{approval_id}/approve', [FacultyAdviserController::class, 'approve'])
        ->name('faculty.approve');
    Route::post('/permit/{approval_id}/reject', [FacultyAdviserController::class, 'reject'])
        ->name('faculty.reject');
});






// ============================
// BARGO ROUTES
// ============================
Route::middleware(['auth', 'role:BARGO'])->group(function () {

    // Dashboard
    Route::get('/bargo/dashboard', [BargoController::class, 'dashboard'])->name('bargo.dashboard');

    // View/Approve PDF
    Route::get('/bargo/permit/{hashed_id}', [BargoController::class, 'viewPermitPdf'])->name('bargo.view.pdf');

    // Approvals Page (this is the one missing)
    Route::get('/bargo/approval', [BargoController::class, 'approvals'])->name('bargo.approvals');

    // Approve / Reject actions
    Route::post('/bargo/approve/{approval_id}', [BargoController::class, 'approve'])->name('bargo.approve');
    Route::post('/bargo/reject/{approval_id}', [BargoController::class, 'reject'])->name('bargo.reject');

    // Event monitoring pages
    Route::get('/bargo/events/pending', [BargoController::class, 'pending'])->name('bargo.events.pending');
    Route::get('/bargo/events/approved', [BargoController::class, 'approved'])->name('bargo.events.approved');
    Route::get('/bargo/events/history', [BargoController::class, 'history'])->name('bargo.events.history');
});



// ============================
// OTHER ROLES
// ============================
Route::middleware(['auth', 'role:SDSO_Head'])->group(function () {
    Route::view('/sdso/dashboard', 'sdso.dashboard')->name('sdso.dashboard');
});

Route::middleware(['auth', 'role:VP_SAS'])->group(function () {
    Route::view('/vpsas/dashboard', 'vpsas.dashboard')->name('vpsas.dashboard');
});

Route::middleware(['auth', 'role:SAS_Director'])->group(function () {
    Route::view('/sas/dashboard', 'sas.dashboard')->name('sas.dashboard');
});


Route::get('/adviser/temp/view/{hashed_id}', [FacultyAdviserController::class, 'viewTempPdf'])
    ->name('adviser.view.temp.pdf');
