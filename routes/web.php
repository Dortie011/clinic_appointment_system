<?php

use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// ================= GUEST ROUTES =================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

// ================= LOGOUT ROUTE =================
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ================= FALLBACK ROUTE =================
Route::get('/', function () {
    return redirect()->route('login');
});

// ================= PROTECTED CLINIC SYSTEM ROUTES =================
Route::middleware('auth')->prefix('admin')->group(function () {

    // 🎯 OPEN ACCESSIBILITY: Everyone (Admin, Receptionist, Doctor) has Dashboard Access 
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // 🟢 SHARED ACCESSIBILITY: Admin & Receptionist
    Route::middleware('role:Admin,Receptionist')->group(function () {
        // Patient Management (Manage & Register Patients) 
        Route::get('/patients', [PatientController::class, 'index'])->name('admin.patients');
        Route::post('/patients/store', [PatientController::class, 'store'])->name('admin.patients.store');
        Route::get('/patients/{id}/edit', [PatientController::class, 'edit'])->name('admin.patients.edit');
        Route::put('/patients/{id}/update', [PatientController::class, 'update'])->name('admin.patients.update');
        
        // Appointments Creation
        Route::post('/appointments/store', [AppointmentController::class, 'store'])->name('admin.appointments.store');
    });

    // 🟢 SHARED ACCESSIBILITY: Admin, Receptionist & Doctor
    Route::middleware('role:Admin,Receptionist,Doctor')->group(function () {
        // Appointments Workflow (View & Edit/Cancel) 
        Route::get('/appointments', [AppointmentController::class, 'index'])->name('admin.appointments');
        Route::get('/appointments/{id}/edit', [AppointmentController::class, 'edit'])->name('admin.appointments.edit');
        Route::put('/appointments/{id}/update', [AppointmentController::class, 'update'])->name('admin.appointments.update');
    });

    // 🟢 SHARED ACCESSIBILITY: Admin & Doctor
    Route::middleware('role:Admin,Doctor')->group(function () {
        // Schedules Workflow
        Route::get('/schedules', [ScheduleController::class, 'index'])->name('admin.schedules');
        Route::post('/schedules/store', [ScheduleController::class, 'store'])->name('admin.schedules.store');
        Route::get('/schedules/{id}/edit', [ScheduleController::class, 'edit'])->name('admin.schedules.edit');
        Route::put('/schedules/{id}/update', [ScheduleController::class, 'update'])->name('admin.schedules.update');
    });

    // 🔒 STRICT PRIVILEGES: Admin Only (System Settings & Accounts Management) 
    Route::middleware('role:Admin')->group(function () {
        // Administrative Form Alterations (Manage Doctor Accounts) 
        Route::get('/doctors', [DoctorController::class, 'index'])->name('admin.doctors');
        Route::post('/doctors/store', [DoctorController::class, 'store'])->name('admin.doctors.store');
        Route::get('/doctors/{id}/edit', [DoctorController::class, 'edit'])->name('admin.doctors.edit');
        Route::put('/doctors/{id}/update', [DoctorController::class, 'update'])->name('admin.doctors.update');
        Route::delete('/doctors/{id}/delete', [DoctorController::class, 'destroy'])->name('admin.doctors.delete');

        // Delete Schedule
        Route::delete('/schedules/{id}/delete', [ScheduleController::class, 'destroy'])->name('admin.schedules.delete');

        // Hard Deletions (Patients & Appointments) 
        Route::delete('/patients/{id}/delete', [PatientController::class, 'destroy'])->name('admin.patients.delete');
        Route::delete('/appointments/{id}/delete', [AppointmentController::class, 'destroy'])->name('admin.appointments.delete');

        // Users Management Panel (Manage Admin/Receptionist Accounts) 
        Route::get('/users', [UserController::class, 'index'])->name('admin.users');
        Route::post('/users/store', [UserController::class, 'store'])->name('admin.users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/users/{id}/update', [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{id}/delete', [UserController::class, 'destroy'])->name('admin.users.delete');
    });
});