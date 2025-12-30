<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\TimeManagementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\TimeController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\FilterController;
use App\Http\Controllers\Api\NotificationController as ApiNotificationController;
use App\Http\Controllers\Api\ProfileController as ApiProfileController;

Route::get('/', [AuthController::class, 'showLogin'])->name('home');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// API Routes for AJAX calls
Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
    // Request/Application API routes
    Route::post('/requests/check', [RequestController::class, 'check'])->name('requests.check');
    Route::post('/requests/store', [RequestController::class, 'store'])->name('requests.store');
    Route::post('/requests/modal', [RequestController::class, 'modal'])->name('requests.modal');
    Route::post('/requests/leave-check', [RequestController::class, 'leaveCheck'])->name('requests.leave-check');
    
    // Time Management API routes
    Route::post('/time/action', [TimeController::class, 'handleTimeAction'])->name('time.action');
    Route::post('/time/details', [TimeController::class, 'getDetails'])->name('time.details');
    Route::post('/time/worked', [TimeController::class, 'timeWorked'])->name('time.worked');
    Route::post('/time/check-punch', [TimeController::class, 'checkFirstPunchIn'])->name('time.check-punch');
    Route::post('/time/details-by-id', [TimeController::class, 'detailsById'])->name('time.details-by-id');
    Route::post('/time/filter', [TimeController::class, 'filterTime'])->name('time.filter');
    
    // Session API routes
    Route::post('/session/check', [SessionController::class, 'check'])->name('session.check');
    
    // Filter API routes
    Route::post('/filter/requests', [FilterController::class, 'filterRequests'])->name('filter.requests');
    
    // Notification API routes
    Route::post('/notifications/handle', [ApiNotificationController::class, 'handle'])->name('notifications.handle');
    
    // Profile API routes
    Route::post('/profile/update', [ApiProfileController::class, 'updateProfile'])->name('profile.update');
    
    // Schedule API routes
    Route::post('/schedule/data', [DashboardController::class, 'getScheduleData'])->name('schedule.data');
    Route::post('/schedule/details', [DashboardController::class, 'getScheduleDetails'])->name('schedule.details');
});

// Employee Routes (Protected)
Route::middleware(['auth', 'employee'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Time Tracking AJAX Routes
    Route::post('/punch-in', [DashboardController::class, 'punchIn'])->name('punch.in');
    Route::post('/punch-out', [DashboardController::class, 'punchOut'])->name('punch.out');
    Route::post('/lunch-start', [DashboardController::class, 'lunchStart'])->name('lunch.start');
    Route::post('/lunch-end', [DashboardController::class, 'lunchEnd'])->name('lunch.end');
    Route::post('/time-data', [DashboardController::class, 'getTimeData'])->name('time.data');
    Route::post('/check-punch-in', [DashboardController::class, 'checkFirstPunchIn'])->name('check.punch.in');
    
    // Application Routes
    Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::post('/applications', [ApplicationController::class, 'store'])->name('applications.store');
    Route::get('/applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
    Route::get('/applications-history', [ApplicationController::class, 'history'])->name('applications.history');
    Route::get('/applications/{application}/download', [ApplicationController::class, 'downloadAttachment'])->name('applications.download');
    
    // History and Schedule Routes
    Route::get('/history', [ApplicationController::class, 'history'])->name('history');
    Route::get('/schedule', [DashboardController::class, 'schedule'])->name('schedule');
    
    // Time Management Routes
    Route::post('/time-management', [TimeManagementController::class, 'handleTimeAction'])->name('time.management');
    
    // Notification Routes
    Route::post('/notifications', [NotificationController::class, 'handleNotification'])->name('notifications');
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});

// Super Admin Routes (Protected)
Route::middleware(['auth:super_admin', 'super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    
    // Salary Management
    Route::get('/salaries', [SuperAdminController::class, 'salaries'])->name('salaries');
    Route::get('/salaries/create', [SuperAdminController::class, 'createSalary'])->name('salaries.create');
    Route::post('/salaries', [SuperAdminController::class, 'storeSalary'])->name('salaries.store');
    Route::get('/salaries/{salary}/edit', [SuperAdminController::class, 'editSalary'])->name('salaries.edit');
    Route::put('/salaries/{salary}', [SuperAdminController::class, 'updateSalary'])->name('salaries.update');
    
    // Employee Management (Copy from Admin)
    Route::get('/employees', [SuperAdminController::class, 'employees'])->name('employees');
    Route::get('/employees/{employee}', [SuperAdminController::class, 'showEmployee'])->name('employees.show');
    Route::get('/employees/{employee}/edit', [SuperAdminController::class, 'editEmployee'])->name('employees.edit');
    Route::put('/employees/{employee}', [SuperAdminController::class, 'updateEmployee'])->name('employees.update');
    
    // Application Management (Copy from Admin)
    Route::get('/applications', [SuperAdminController::class, 'applications'])->name('applications');
    
    // Attendance Management (Copy from Admin)
    Route::get('/attendance', [SuperAdminController::class, 'attendance'])->name('attendance');
    
    // Reports (Copy from Admin)
    Route::get('/reports', [SuperAdminController::class, 'reports'])->name('reports');
    
    // Settings Management
    Route::get('/settings', [SuperAdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [SuperAdminController::class, 'updateSettings'])->name('settings.update');
    
    // Admin Management
    Route::get('/admins', [SuperAdminController::class, 'admins'])->name('admins');
    Route::get('/admins/{admin}', [SuperAdminController::class, 'showAdmin'])->name('admins.show');
    Route::get('/admins/{admin}/edit', [SuperAdminController::class, 'editAdmin'])->name('admins.edit');
    Route::put('/admins/{admin}', [SuperAdminController::class, 'updateAdmin'])->name('admins.update');
});

// Super Admin Auth Routes
Route::get('/super-admin/login', [SuperAdminController::class, 'showLogin'])->name('super-admin.login');
Route::post('/super-admin/login', [SuperAdminController::class, 'login'])->name('super-admin.login.post');
Route::get('/super-admin/register', [SuperAdminController::class, 'showRegister'])->name('super-admin.register');
Route::post('/super-admin/register', [SuperAdminController::class, 'register'])->name('super-admin.register.post');
Route::post('/super-admin/logout', [SuperAdminController::class, 'logout'])->name('super-admin.logout');

// Admin Routes (Protected)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Employee Management
    Route::get('/employees', [AdminController::class, 'employees'])->name('employees');
    Route::get('/employees/create', [AdminController::class, 'createEmployee'])->name('employees.create');
    Route::post('/employees', [AdminController::class, 'storeEmployee'])->name('employees.store');
    Route::get('/employees/{employee}', [AdminController::class, 'showEmployee'])->name('employees.show');
    Route::get('/employees/{employee}/edit', [AdminController::class, 'editEmployee'])->name('employees.edit');
    Route::put('/employees/{employee}', [AdminController::class, 'updateEmployee'])->name('employees.update');
    Route::get('/employees/{employee}/history', [AdminController::class, 'employeeTimeHistory'])->name('employees.history');
    
    // Application Management
    Route::get('/applications', [AdminController::class, 'applications'])->name('applications');
    Route::get('/applications/{application}', [AdminController::class, 'showApplication'])->name('applications.show');
    Route::post('/applications/{application}/status', [AdminController::class, 'updateApplicationStatus'])->name('applications.status');
    
    // Attendance Management
    Route::get('/attendance', [AdminController::class, 'attendance'])->name('attendance');
    Route::get('/attendance/export/{date?}', [AdminController::class, 'exportAttendance'])->name('attendance.export');
    
    // Employee History
    Route::get('/employee-history', [AdminController::class, 'employeeHistory'])->name('employee-history');
    
    // Work From Home
    Route::get('/wfh', [AdminController::class, 'workFromHome'])->name('wfh');
    Route::post('/wfh/{wfh}/status', [AdminController::class, 'updateWfhStatus'])->name('wfh.status');
    
    // Time Entries
    Route::get('/time-entries', [AdminController::class, 'timeEntries'])->name('time-entries');
    Route::delete('/time-entries/{timeEntry}', [AdminController::class, 'deleteTimeEntry'])->name('time-entries.delete');
    
    // Departments
    Route::get('/departments', [AdminController::class, 'departments'])->name('departments');
    Route::post('/departments', [AdminController::class, 'storeDepartment'])->name('departments.store');
    Route::delete('/departments/{department}', [AdminController::class, 'deleteDepartment'])->name('departments.delete');
    
    // Reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::post('/reports/generate', [AdminController::class, 'reports'])->name('reports.generate');
    Route::get('/reports/export/{type}', [AdminController::class, 'exportReport'])->name('reports.export');
    
    // Schedule Management
    Route::get('/schedule', [AdminController::class, 'schedule'])->name('schedule');
    Route::post('/schedule', [AdminController::class, 'schedule'])->name('schedule.post');
    
    // Legacy Admin Routes (keeping for backward compatibility)
    Route::post('/dashboard/data', [AdminDashboardController::class, 'getEmployeeData'])->name('dashboard.data');
    Route::resource('employees-legacy', EmployeeController::class);
    Route::get('/settings-legacy', [\App\Http\Controllers\Admin\SystemSettingController::class, 'index'])->name('settings-legacy.index');
    Route::post('/settings-legacy', [\App\Http\Controllers\Admin\SystemSettingController::class, 'update'])->name('settings-legacy.update');
    Route::post('/applications/{application}/approve', [AdminDashboardController::class, 'approveApplication'])->name('applications.approve');
    Route::post('/applications/{application}/reject', [AdminDashboardController::class, 'rejectApplication'])->name('applications.reject');
});
