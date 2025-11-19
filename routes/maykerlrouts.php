<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\PermitController;
use App\Http\Controllers\StudentEventController;
use App\Http\Controllers\FacultyAdviserController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\BargoController;
use App\Http\Controllers\UserLogController;
use App\Http\Controllers\ProfileController;
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
// ADMIN ROUTES
// ============================
  Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
  Route::view('/dashboard', 'admin.dashboard')->name('admin.dashboard');

  Route::get('/users', [UserController::class, 'index'])->name('users.index');
  Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
  Route::post('/users', [UserController::class, 'store'])->name('users.store');
  Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
  Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
  Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
  Route::get('/logs', [UserLogController::class, 'index'])->name('admin.logs');
  Route::view('/calendar', 'admin.calendardisplay');
  Route::view('/event-requests', 'admin.EventRequest.AllRequest');
  Route::view('/event-requests/pending', 'admin.EventRequest.PendingApproval');
  Route::view('/event-requests/approved-events', 'admin.EventRequest.ApprovedEvents');
  Route::view('/approvals/pending', 'admin.approvals.pending');
  Route::view('/approvals/history', 'admin.approvals.history');
  Route::view('/esignatures/pending', 'admin.ESignature.pending');
  Route::view('/esignatures/completed', 'admin.ESignature.completed');

  //ADMIN ORGANIZATION ROUTES
  Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
  Route::post('/organizations/store', [OrganizationController::class, 'store'])
     ->name('organizations.store');
  Route::delete('/organizations/{organization_id}', [OrganizationController::class, 'destroy'])
    ->name('organizations.destroy');
  Route::get('organizations/{organization}', [OrganizationController::class, 'show'])->name('organizations.show');

  // admin user profile routes
  Route::get('/profiles', [ProfileController::class, 'index'])->name('profiles.index');
  Route::get('/profiles/create', [ProfileController::class, 'create'])->name('profiles.create');
  Route::post('/profiles/store', [ProfileController::class, 'store'])->name('profiles.store');



  Route::view('/account', 'admin.profile.account');

  Route::view('/reports/minutes', 'admin.reports.minutes');
  Route::view('/roles', 'admin.users.roles');
  Route::view('/help', 'admin.help.help');
});

// ============================
// STUDENT ORGANIZATION ROUTES
// ============================
Route::middleware(['auth', 'role:Student_Organization'])->prefix('student')->group(function () {
  Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
  Route::get('/permit/form', [PermitController::class, 'showForm'])->name('permit.form');
  Route::post('/permit/generate', [PermitController::class, 'generate'])->name('permit.generate');
  Route::get('/permit/tracking', [PermitController::class, 'track'])->name('student.permit.tracking');

  // hashed view route
  Route::get('/permit/view/{hashed_id}', [PermitController::class, 'view'])->name('student.permit.view');
});

// Adviser routes (keep role middleware)
Route::middleware(['auth', 'role:Faculty_Adviser'])->group(function () {
  Route::get('/adviser/dashboard', [FacultyAdviserController::class, 'dashboard'])->name('adviser.dashboard');

  // approve / reject actions
  Route::post('/adviser/permit/{approval_id}/approve', [FacultyAdviserController::class, 'approve'])
    ->name('faculty.approve');

  Route::post('/adviser/permit/{approval_id}/reject', [FacultyAdviserController::class, 'reject'])
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
  Route::get('/bargo/approvals', [BargoController::class, 'approvals'])->name('bargo.approvals');

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



// ============================
// CALENDAR API ROUTES
// ============================
// Route::prefix('admin')->group(function () {
//   Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
//   Route::post('/calendar/events', [CalendarController::class, 'store'])->name('calendar.store');
// });



//testing



Route::get('/adviser/temp/view/{hashed_id}', [FacultyAdviserController::class, 'viewTempPdf'])
  ->name('adviser.view.temp.pdf');
