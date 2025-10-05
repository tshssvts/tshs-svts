<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Prefect Controllers
use App\Http\Controllers\Prefect\PDashboardController;
use App\Http\Controllers\Prefect\PLogoutController;
use App\Http\Controllers\Prefect\PStudentController;
use App\Http\Controllers\Prefect\PAdviserController;
use App\Http\Controllers\Prefect\PParentController;
use App\Http\Controllers\Prefect\PViolationController;
use App\Http\Controllers\Prefect\PComplaintController;
use App\Http\Controllers\Prefect\POffenseSanctionController;
use App\Http\Controllers\Prefect\PReportController;
use App\Http\Controllers\Prefect\PViolationAnecdotalController;
use App\Http\Controllers\Prefect\ProfileController;
use App\Http\Controllers\Prefect\PComplaintAnecdotalController;



// Adviser Controllers
use App\Http\Controllers\Adviser\ADashboardController;
use App\Http\Controllers\Adviser\ALogoutController;
use App\Http\Controllers\Adviser\AStudentController;
use App\Http\Controllers\Adviser\AParentController;
use App\Http\Controllers\Adviser\AViolationController;
use App\Http\Controllers\Adviser\AComplaintController;
use App\Http\Controllers\Adviser\AOffenseSanctionController;
use App\Http\Controllers\Adviser\AReportController;
use App\Http\Controllers\PParentController as ControllersPParentController;

Route::get('/', function () {
    return view('login');
});


// ===================== Authentication Routes =====================
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/', [AuthController::class, 'login'])->name('login');


// ===================== Prefect Routes =====================
Route::prefix('prefect')->group(function () {
    // Logout
    Route::post('/logout', [PLogoutController::class, 'logout'])->name('prefect.logout');

    // Protected routes
    Route::middleware('auth:prefect')->group(function () {
        // Dashboard
        Route::get('/dashboard', [PDashboardController::class, 'dashboard'])->name('prefect.dashboard');

       // Profile routes
        Route::post('/send-verification-code', [ProfileController::class, 'sendVerificationCode'])->name('prefect.send-verification-code');
        Route::post('/change-password', [ProfileController::class, 'changePassword'])->name('prefect.change-password');
        Route::get('/profile-info', [ProfileController::class, 'getProfileInfo'])->name('prefect.profile-info');
        Route::post('/upload-profile-image', [ProfileController::class, 'uploadProfileImage'])->name('prefect.upload-profile-image');
        Route::post('/remove-profile-image', [ProfileController::class, 'removeProfileImage'])->name('prefect.remove-profile-image');

        // Management Routes
        Route::get('/studentmanagement', [PStudentController::class, 'studentmanagement'])->name('student.management');
        Route::get('/violation', [PViolationController::class, 'index'])->name('prefect.violation');
        Route::get('/parentlists', [PParentController::class, 'parentlists'])->name('parent.lists');
        Route::get('/adviser', [PAdviserController::class, 'index'])->name('prefect.adviser');
        Route::get('/offensesandsanctions', [POffenseSanctionController::class, 'index'])->name('offenses.sanctions');

        // Student Routes
        Route::get('/create/student', [PStudentController::class, 'createStudent'])->name('create.student');
        Route::post('/students/store', [PStudentController::class, 'store'])->name('students.store');
        Route::put('/students/update/{id}', [PStudentController::class, 'update'])->name('students.update');
        Route::get('/students/search', [PStudentController::class, 'search'])->name('students.search');
        // 🔍 Live search routes
        Route::post('/students/search-parents', [PStudentController::class, 'searchParents'])->name('students.search-parents');
        Route::post('/students/search-advisers', [PStudentController::class, 'searchAdvisers'])->name('students.search-advisers');
        // Archive Routes
        Route::post('/students/archive', [PStudentController::class, 'archive'])->name('students.archive');
        Route::get('/students/archived', [PStudentController::class, 'getArchived'])->name('students.getArchived');
        Route::post('/students/restore', [PStudentController::class, 'restore'])->name('students.restore');
        Route::post('/students/destroy-multiple', [PStudentController::class, 'destroyMultiple'])->name('students.destroyMultiple');

        // Adviser Routes
        Route::get('/create/adviser', [PAdviserController::class, 'createAdviser'])->name('create.adviser');
        Route::post('/advisers/store', [PAdviserController::class, 'store'])->name('advisers.store');
        Route::put('/advisers/update', [PAdviserController::class, 'update'])->name('advisers.update');

        // Parent Routes
        Route::get('/parentlists', [PParentController::class, 'parentlists'])->name('parent.lists');
        Route::get('/create/parent', [PParentController::class, 'createParent'])->name('create.parent');
        Route::post('/parents/store', [PParentController::class, 'parentStore'])->name('parents.store');
        Route::put('/parents/update/{id}', [PParentController::class, 'parentUpdate'])->name('parents.update');

        // Parent Archive Routes
        Route::post('/parents/archive', [PParentController::class, 'archiveParents'])->name('parents.archive');
        Route::get('/parents/archived', [PParentController::class, 'getArchivedParents'])->name('parents.archived');
        Route::post('/parents/restore', [PParentController::class, 'restoreParents'])->name('parents.restore');
        Route::post('/parents/destroy-permanent', [PParentController::class, 'destroyParentsPermanent'])->name('parents.destroy.permanent');
        Route::get('/parents/archived/count', [PParentController::class, 'getArchivedParentsCount'])->name('parents.archived.count');

        // Violation Routes
        Route::get('/violations', [PViolationController::class, 'index'])->name('violations.index');
        Route::get('/violations/create', [PViolationController::class, 'create'])->name('violations.create');
        Route::post('/violations/store', [PViolationController::class, 'store'])->name('violations.store');
        Route::put('/violations/update/{violationId}', [PViolationController::class, 'update'])->name('violations.update');
        // Violation Archive Routes
        Route::post('/violations/archive', [PViolationController::class, 'archive'])->name('violations.archive');
        Route::get('/violations/archived', [PViolationController::class, 'getArchived'])->name('violations.getArchived');
        Route::post('/violations/restore', [PViolationController::class, 'restore'])->name('violations.restore');
        Route::post('/violations/destroy-multiple', [PViolationController::class, 'destroyMultiple'])->name('violations.destroyMultiple');
        // Archive Routes for different types
        Route::post('/violation-appointments/archive', [PViolationController::class, 'archiveAppointments'])->name('violation.appointments.archive');
        Route::post('/violation-anecdotals/archive', [PViolationController::class, 'archiveAnecdotals'])->name('violation.anecdotals.archive');
        Route::get('/violation-appointments/archived', [PViolationController::class, 'getArchivedAppointments'])->name('violation.appointments.archived');
        Route::get('/violation-anecdotals/archived', [PViolationController::class, 'getArchivedAnecdotals'])->name('violation.anecdotals.archived');
        Route::post('/violations/restore-multiple', [PViolationController::class, 'restoreMultiple'])->name('violations.restore.multiple');
        Route::post('/violations/destroy-multiple-archived', [PViolationController::class, 'destroyMultipleArchived'])->name('violations.destroy.multiple.archived');

        // Violation Anecdotal Routes
        // Display the create anecdotal form
        Route::get('/violation-anecdotal/create', [PViolationAnecdotalController::class, 'createVAnecdotal'])
         ->name('violation-anecdotal.create');

        // Search for violation records (for live search)
        Route::post('/violation-anecdotal/search-violations', [PViolationAnecdotalController::class, 'searchViolations'])
         ->name('violation-anecdotal.search-violations');

        // Store anecdotal records
        Route::post('/violation-anecdotal', [PViolationAnecdotalController::class, 'store'])
         ->name('violation-anecdotal.store');


        // Violation AJAX Routes
        Route::post('/violations/search-students', [PViolationController::class, 'searchStudents'])->name('violations.search-students');
        Route::post('/violations/search-offenses', [PViolationController::class, 'searchOffenses'])->name('violations.search-offenses');

        // Complaint Routes
        Route::get('/complaints', [PComplaintController::class, 'index'])->name('prefect.complaints');
        Route::get('/complaints/create', [PComplaintController::class, 'create'])->name('complaints.create');
        Route::post('/complaints/store', [PComplaintController::class, 'store'])->name('complaints.store');
        Route::put('/complaints/{id}', [PComplaintController::class, 'update'])->name('complaints.update');

        // Complaint AJAX Routes
        Route::post('/complaints/search-students', [PComplaintController::class, 'searchStudents'])->name('complaints.search-students');
        Route::get('/complaints/search-students1', [PComplaintController::class, 'searchStudents'])->name('prefect.students.search');
        Route::post('/complaints/search-offenses', [PComplaintController::class, 'searchOffenses'])->name('complaints.search-offenses');
        Route::get('/complaints/search-offenses1', [PComplaintController::class, 'searchOffenses'])->name('prefect.offenses.search');
        Route::get('/complaints/get-sanction', [PComplaintController::class, 'getSanction'])->name('complaints.get-sanction');

         // Violation Anecdotal Routes
        // Display the create anecdotal form
    Route::get('/complaint-anecdotal/create', [PComplaintAnecdotalController::class, 'createCAnecdotal'])->name('complaint-anecdotal.create');
    Route::post('/complaint-anecdotal/store', [PComplaintAnecdotalController::class, 'store'])->name('complaint-anecdotal.store');

    // 🔍 Search Routes (GET)
    Route::get('/complaint-anecdotal/search-complaints', [PComplaintAnecdotalController::class, 'searchComplaints'])->name('complaint-anecdotal.search-complaints');
    Route::get('/complaint-anecdotal/search-respondents', [PComplaintAnecdotalController::class, 'searchRespondents'])->name('complaint-anecdotal.search-respondents');



        // Report Routes
        Route::get('/reportgenerate', [PReportController::class, 'reportgenerate'])->name('report.generate');
        Route::get('/reports/data/{reportId}', [PReportController::class, 'generateReportData'])->name('prefect.reports.data');
    });
});





// ===================== Adviser Routes =====================
Route::prefix('adviser')->group(function () {
    // Logout
    Route::post('/logout', [ALogoutController::class, 'logout'])->name('adviser.logout');

    // Protected routes
    Route::middleware('auth:adviser')->group(function () {
        // Dashboard
        Route::get('/dashboard', [ADashboardController::class, 'dashboard'])->name('adviser.dashboard');

        // Management Routes
        Route::get('/violationrecord', [AViolationController::class, 'violationrecord'])->name('violation.record');
        Route::get('/studentlist', [AStudentController::class, 'studentlist'])->name('student.list');
        Route::get('/parentlist', [AParentController::class, 'parentlist'])->name('parent.list');
        Route::get('/offensesanction', [AOffenseSanctionController::class, 'offensesanction'])->name('offense.sanction');
        Route::get('/complaintsall', [AComplaintController::class, 'complaintsall'])->name('complaints.all');

        // Student Routes
        Route::put('/students/update/{id}', [PStudentController::class, 'update'])->name('students.update');

        // Violation Routes
        Route::get('/violations/create', [AViolationController::class, 'Acreate'])->name('Aviolations.create');
        Route::post('/violations', [AViolationController::class, 'Astore'])->name('Aviolations.store');

        // Violation AJAX Routes
        Route::post('/violations/search-students', [AViolationController::class, 'AsearchStudents'])->name('Aviolations.search-students');
        Route::post('/violations/search-offenses', [AViolationController::class, 'AsearchOffenses'])->name('Aviolations.search-offenses');
        Route::get('/violations/get-sanction', [AViolationController::class, 'AgetSanction'])->name('Aviolations.get-sanction');

        // Report Routes
        Route::get('/adviserreports', [AReportController::class, 'reports'])->name('adviser.reports');
        Route::get('/reports/data/{reportId}', [AReportController::class, 'getReportData'])->name('adviser.reports.data');
    });
});
