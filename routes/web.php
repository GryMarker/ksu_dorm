<?php

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InterviewController as AdminInterviewController;
use App\Http\Controllers\Admin\InterviewSlotController as AdminInterviewSlotController;
use App\Http\Controllers\Admin\ReservationDecisionController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\TenantController as AdminTenantController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\EmployeeCottageController as AdminEmployeeCottageController;
use App\Http\Controllers\Employee\EmployeeApplyController;
use App\Http\Controllers\Employee\EmployeeDashboardController;
use App\Http\Controllers\Employee\EmployeeCottageController;
use App\Http\Controllers\Employee\EmployeePaymentController;
use App\Http\Controllers\Employee\EmployeeStatusController;
use App\Http\Controllers\President\EmployeeApprovalController;
use App\Http\Controllers\President\EmployeePaymentApprovalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Tenant\ApplyController;
use App\Http\Controllers\Tenant\AttendanceController as TenantAttendanceController;
use App\Http\Controllers\Tenant\InterviewController as TenantInterviewController;
use App\Http\Controllers\Tenant\ReservationController;
use App\Http\Controllers\Tenant\TenantDashboardController;
use App\Http\Controllers\Tenant\TransferController;
use App\Models\Reservation;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/admin', function () {
        $user = auth()->user();
        abort_unless(in_array($user->role, ['dorm_master', 'student_director']), 403);

        return redirect()->route('admin.dashboard');
    })->name('admin.home');

    Route::middleware(['role:tenant', 'verified'])->group(function () {
        Route::get('/apply', [ApplyController::class, 'showForm'])->name('tenant.apply.form');
        Route::post('/apply', [ApplyController::class, 'submit'])->name('tenant.apply.submit');
        Route::get('/apply/slots', [TenantInterviewController::class, 'listOpenSlots'])->name('tenant.apply.slots');
        Route::post('/apply/slots/{slot}/book', [TenantInterviewController::class, 'bookSlot'])->name('tenant.apply.slot.book');
        Route::get('/apply/status', [ApplyController::class, 'status'])->name('tenant.apply.status');
    });

    Route::middleware('role:employee')->group(function () {
        Route::get('/employee/apply', [EmployeeApplyController::class, 'showForm'])->name('employee.apply.form');
        Route::post('/employee/apply', [EmployeeApplyController::class, 'submit'])->name('employee.apply.submit');
        Route::get('/employee/status', [EmployeeStatusController::class, 'show'])->name('employee.status');
    });

    Route::prefix('tenant')->middleware(['role:tenant', 'tenant.approved'])->group(function () {
        Route::get('/dashboard', [TenantDashboardController::class, 'index'])->name('tenant.dashboard');
        Route::get('/availability', [ReservationController::class, 'availability'])->name('tenant.availability');
        Route::post('/reservations', [ReservationController::class, 'store'])->middleware('can:create,' . Reservation::class)->name('tenant.reservations.store');
        Route::post('/transfers', [TransferController::class, 'store'])->name('tenant.transfers.store');
        Route::get('/my-room', [ReservationController::class, 'myRoom'])->name('tenant.myroom');
        Route::get('/attendance', [TenantAttendanceController::class, 'index'])->name('tenant.attendance.index');
    });

    Route::prefix('employee')->middleware(['role:employee', 'employee.approved'])->group(function () {
        Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');
        Route::get('/payments', [EmployeePaymentController::class, 'index'])->name('employee.payments.index');
        Route::post('/payments', [EmployeePaymentController::class, 'store'])->name('employee.payments.store');
        Route::get('/cottages', [EmployeeCottageController::class, 'index'])->name('employee.cottages.index');
        Route::post('/cottages/{cottage}/request', [EmployeeCottageController::class, 'request'])->name('employee.cottages.request');
    });

    Route::prefix('president')->middleware('role:president')->group(function () {
        Route::get('/approvals/employees', [EmployeeApprovalController::class, 'index'])->name('president.approvals.employees.index');
        Route::patch('/approvals/employees/{tenant}/approve', [EmployeeApprovalController::class, 'approve'])->name('president.approvals.employees.approve');
        Route::patch('/approvals/employees/{tenant}/reject', [EmployeeApprovalController::class, 'reject'])->name('president.approvals.employees.reject');
        Route::get('/payments', [EmployeePaymentApprovalController::class, 'index'])->name('president.payments.index');
        Route::patch('/payments/{payment}/approve', [EmployeePaymentApprovalController::class, 'approve'])->name('president.payments.approve');
        Route::patch('/payments/{payment}/reject', [EmployeePaymentApprovalController::class, 'reject'])->name('president.payments.reject');
    });

    Route::prefix('management')->middleware('role:president')->group(function () {
        Route::get('/cottages', [AdminEmployeeCottageController::class, 'index'])->name('management.cottages.index');
        Route::patch('/cottages/{cottage}/approve', [AdminEmployeeCottageController::class, 'approve'])->name('management.cottages.approve');
        Route::patch('/cottages/{cottage}/reject', [AdminEmployeeCottageController::class, 'reject'])->name('management.cottages.reject');
        Route::patch('/cottages/{cottage}/release', [AdminEmployeeCottageController::class, 'release'])->name('management.cottages.release');
    });

    Route::middleware('can:viewAny,App\\Models\\Room')->group(function () {
        Route::get('/admin/students', [AdminTenantController::class, 'index'])->name('admin.students.index');
        Route::get('/admin/students/{tenant}/history', [AdminTenantController::class, 'history'])->name('admin.students.history');
        Route::get('/admin/applications', [AdminApplicationController::class, 'index'])->name('admin.applications.index');
        Route::patch('/admin/applications/{tenant}/approve', [AdminApplicationController::class, 'approve'])->name('admin.applications.approve');
        Route::patch('/admin/applications/{tenant}/reject', [AdminApplicationController::class, 'reject'])->name('admin.applications.reject');
        Route::resource('admin/rooms', RoomController::class)->names('admin.rooms');
        Route::get('/admin/interviews', [AdminInterviewController::class, 'index'])->name('admin.interviews.index');
        Route::patch('/admin/interviews/{interview}/result', [AdminInterviewController::class, 'result'])->name('admin.interviews.result');
        Route::resource('/admin/interview-slots', AdminInterviewSlotController::class)
            ->except(['show'])
            ->names('admin.interview-slots');

        Route::get('/admin/reservations/pending', [ReservationDecisionController::class, 'index'])->name('admin.reservations.index');
        Route::patch('/admin/reservations/{reservation}/approve', [ReservationDecisionController::class, 'approve'])->name('admin.reservations.approve');
        Route::patch('/admin/reservations/{reservation}/decline', [ReservationDecisionController::class, 'decline'])->name('admin.reservations.decline');

        Route::get('/admin/attendance', [AdminAttendanceController::class, 'index'])->name('admin.attendance.index');
        Route::get('/admin/attendance/monthly', [AdminAttendanceController::class, 'monthly'])->name('admin.attendance.monthly');
        Route::post('/admin/attendance', [AdminAttendanceController::class, 'store'])->name('admin.attendance.store');
        Route::get('/admin/dashboard', AdminDashboardController::class)->name('admin.dashboard');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
