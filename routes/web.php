<?php

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InterviewController as AdminInterviewController;
use App\Http\Controllers\Admin\ReservationDecisionController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\DashboardController;
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

    Route::middleware('role:tenant')->group(function () {
        Route::get('/apply', [ApplyController::class, 'showForm'])->name('tenant.apply.form');
        Route::post('/apply', [ApplyController::class, 'submit'])->name('tenant.apply.submit');
        Route::get('/apply/slots', [TenantInterviewController::class, 'listOpenSlots'])->name('tenant.apply.slots');
        Route::post('/apply/slots/{slot}/book', [TenantInterviewController::class, 'bookSlot'])->name('tenant.apply.slot.book');
        Route::get('/apply/status', [ApplyController::class, 'status'])->name('tenant.apply.status');
    });

    Route::prefix('tenant')->middleware(['role:tenant', 'tenant.approved'])->group(function () {
        Route::get('/dashboard', [TenantDashboardController::class, 'index'])->name('tenant.dashboard');
        Route::get('/availability', [ReservationController::class, 'availability'])->name('tenant.availability');
        Route::post('/reservations', [ReservationController::class, 'store'])->middleware('can:create,' . Reservation::class)->name('tenant.reservations.store');
        Route::post('/transfers', [TransferController::class, 'store'])->name('tenant.transfers.store');
        Route::get('/my-room', [ReservationController::class, 'myRoom'])->name('tenant.myroom');
        Route::get('/attendance', [TenantAttendanceController::class, 'index'])->name('tenant.attendance.index');
    });

    Route::middleware('can:viewAny,App\\Models\\Room')->group(function () {
        Route::resource('admin/rooms', RoomController::class)->names('admin.rooms');
        Route::get('/admin/interviews', [AdminInterviewController::class, 'index'])->name('admin.interviews.index');
        Route::patch('/admin/interviews/{interview}/result', [AdminInterviewController::class, 'result'])->name('admin.interviews.result');

        Route::get('/admin/reservations/pending', [ReservationDecisionController::class, 'index'])->name('admin.reservations.index');
        Route::patch('/admin/reservations/{reservation}/approve', [ReservationDecisionController::class, 'approve'])->name('admin.reservations.approve');
        Route::patch('/admin/reservations/{reservation}/decline', [ReservationDecisionController::class, 'decline'])->name('admin.reservations.decline');

        Route::get('/admin/attendance', [AdminAttendanceController::class, 'index'])->name('admin.attendance.index');
        Route::get('/admin/dashboard', AdminDashboardController::class)->name('admin.dashboard');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
