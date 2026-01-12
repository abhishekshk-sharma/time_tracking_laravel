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
use App\Http\Controllers\SalarySlipController;
use App\Http\Controllers\PayslipController;

Route::get('/', [AuthController::class, 'showLogin'])->name('home');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

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
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    
    // Schedule API routes
    Route::post('/schedule/data', [DashboardController::class, 'getScheduleData'])->name('schedule.data');
    Route::post('/schedule/details', [DashboardController::class, 'getScheduleDetails'])->name('schedule.details');
});

// Employee Routes (Protected)
Route::middleware(['auth', 'employee'])->group(function () {

    Route::post('/api.time.details-by-id', [TimeController::class, 'detailsById'])->name('api.time.details-by-id');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/applications-history', [DashboardController::class, 'employeehistory'])->name('applications.history');
    
    // Notification routes
    Route::get('/notifications', function() {
        $notifications = \App\Models\AppNotification::where('notify_to', Auth::user()->emp_id)
            ->with(['application', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        return response()->json($notifications);
    })->name('notifications');
    
    Route::post('/notifications/{id}/read', function($id) {
        \App\Models\AppNotification::where('id', $id)
            ->where('notify_to', Auth::user()->emp_id)
            ->update(['status' => 'checked']);
        return response()->json(['success' => true]);
    })->name('notifications.read');
    // Route::get('/employeehistory', [DashboardController::class, 'employeehistory'])->name('employeehistory');
    // Time Tracking AJAX Routes
    Route::post('/punch-in', [DashboardController::class, 'punchIn'])->name('punch.in');
    Route::post('/punch-out', [DashboardController::class, 'punchOut'])->name('punch.out');
    Route::post('/lunch-start', [DashboardController::class, 'lunchStart'])->name('lunch.start');
    Route::post('/lunch-end', [DashboardController::class, 'lunchEnd'])->name('lunch.end');
    Route::post('/capture-image', [DashboardController::class, 'captureImage'])->name('capture.image');
    
    Route::post('/time-data', [DashboardController::class, 'getTimeData'])->name('time.data');
    Route::post('/check-punch-in', [DashboardController::class, 'checkFirstPunchIn'])->name('check.punch.in');
    
    // Application Routes
    Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::post('/applications', [ApplicationController::class, 'store'])->name('applications.store');
    Route::get('/applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
    
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
    
    // Payslip Routes
    Route::get('/payslips', [PayslipController::class, 'index'])->name('payslips.index');
    Route::get('/payslips/{id}/download', [PayslipController::class, 'download'])->name('payslips.download');
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
    Route::get('/salaries/pending-employees', [SuperAdminController::class, 'getPendingEmployees'])->name('salaries.pending-employees');
    
    // Employee Management (Copy from Admin)
    Route::get('/employees', [SuperAdminController::class, 'employees'])->name('employees');
    Route::get('/employees/create', [SuperAdminController::class, 'createEmployee'])->name('employees.create');
    Route::post('/employees', [SuperAdminController::class, 'storeEmployee'])->name('employees.store');
    Route::get('/employees/{employee}', [SuperAdminController::class, 'showEmployee'])->name('employees.show');
    Route::get('/employees/{employee}/edit', [SuperAdminController::class, 'editEmployee'])->name('employees.edit');
    Route::put('/employees/{employee}', [SuperAdminController::class, 'updateEmployee'])->name('employees.update');
    Route::get('/employees/{employee}/history', [SuperAdminController::class, 'employeeTimeHistory'])->name('employees.history');
    Route::get('/employee-history', [SuperAdminController::class, 'employeeHistory'])->name('employee-history');
    // Time Entries Management
    Route::get('/time-entries', [SuperAdminController::class, 'timeEntries'])->name('time-entries');
    Route::get('/time-entry-images', [SuperAdminController::class, 'timeEntryImages'])->name('time-entry-images');
    Route::post('/time-entry-images/download', [SuperAdminController::class, 'downloadImages'])->name('time-entry-images.download');
    Route::post('/time-entry-images/delete', [SuperAdminController::class, 'deleteImages'])->name('time-entry-images.delete');
    Route::get('/time-entries/employee/{empId}/{date}', [SuperAdminController::class, 'getEmployeeTimeEntries'])->name('time-entries.employee');
    Route::post('/time-entries/update', [SuperAdminController::class, 'updateTimeEntry'])->name('time-entries.update');
    Route::post('/time-entries/add', [SuperAdminController::class, 'addTimeEntry'])->name('time-entries.add');
    Route::delete('/time-entries/{timeEntry}', [SuperAdminController::class, 'deleteTimeEntry'])->name('time-entries.delete');
    
    // Application Management
    Route::get('/applications', [SuperAdminController::class, 'applications'])->name('applications');
    Route::get('/applications/{application}', [SuperAdminController::class, 'showApplication'])->name('applications.show');
    Route::post('/applications/{application}/status', [SuperAdminController::class, 'updateApplicationStatus'])->name('applications.status');
    
    // Attendance Management (Copy from Admin)
    Route::get('/attendance', [SuperAdminController::class, 'attendance'])->name('attendance');
    
    // Reports (Copy from Admin)
    Route::get('/reports', [SuperAdminController::class, 'reports'])->name('reports');
    Route::post('/reports/generate', [SuperAdminController::class, 'generateReport'])->name('reports.generate');
    Route::post('/reports/export', [SuperAdminController::class, 'exportReport'])->name('reports.export');
    
    // Salary Reports
    Route::post('/salary-reports/generate', [SuperAdminController::class, 'generateSalaryReports'])->name('salary-reports.generate');
    Route::post('/salary-reports/check', [SuperAdminController::class, 'checkSalaryReports'])->name('salary-reports.check');
    Route::post('/salary-reports/release', [SuperAdminController::class, 'releaseSalaryReports'])->name('salary-reports.release');
    Route::get('/salary-reports/{id}/download', [SuperAdminController::class, 'downloadSalaryReport'])->name('salary-reports.download');
    Route::get('/salary-reports/{id}/edit', [SuperAdminController::class, 'editSalaryReport'])->name('salary-reports.edit');
    Route::put('/salary-reports/{id}', [SuperAdminController::class, 'updateSalaryReport'])->name('salary-reports.update');
    Route::get('/salary-reports/{id}/preview', [SuperAdminController::class, 'getSalarySlipPreview'])->name('salary-reports.preview');
    Route::get('/salary-reports/{id}/view', [SuperAdminController::class, 'showSalarySlipPreview'])->name('salary-reports.view');
    
    // Attendance Reports
    Route::post('/attendance-reports/generate', [SuperAdminController::class, 'generateAttendanceReports'])->name('attendance-reports.generate');
    
    // Salary Report Show Page
    Route::get('/salary-report', [SuperAdminController::class, 'showSalaryReport'])->name('salary-report.show');
    
    // Salary Slip Generation
    Route::post('/salary-slip/generate', [SalarySlipController::class, 'generate'])->name('salary-slip.generate');
    
    // Settings Management
    Route::get('/settings', [SuperAdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [SuperAdminController::class, 'updateSettings'])->name('settings.update');
    
    // Location Settings
    Route::get('/location-settings', [\App\Http\Controllers\SuperAdmin\LocationSettingsController::class, 'index'])->name('location-settings.index');
    Route::post('/location-settings', [\App\Http\Controllers\SuperAdmin\LocationSettingsController::class, 'update'])->name('location-settings.update');
    Route::get('/location-settings/{empId}/edit', [\App\Http\Controllers\SuperAdmin\LocationSettingsController::class, 'edit'])->name('location-settings.edit');
    
    // Admin Management
    Route::get('/admins', [SuperAdminController::class, 'admins'])->name('admins');
    Route::get('/admins/create', [SuperAdminController::class, 'createAdmin'])->name('admins.create');
    Route::post('/admins', [SuperAdminController::class, 'storeAdmin'])->name('admins.store');
    Route::get('/admins/{admin}', [SuperAdminController::class, 'showAdmin'])->name('admins.show');
    Route::get('/admins/{admin}/edit', [SuperAdminController::class, 'editAdmin'])->name('admins.edit');
    Route::put('/admins/{admin}', [SuperAdminController::class, 'updateAdmin'])->name('admins.update');
    
    // Regions Management
    Route::get('/regions', [SuperAdminController::class, 'regions'])->name('regions');
    Route::post('/regions', [SuperAdminController::class, 'storeRegion'])->name('regions.store');
    Route::put('/regions/{region}', [SuperAdminController::class, 'updateRegion'])->name('regions.update');
    Route::delete('/regions/{region}', [SuperAdminController::class, 'destroyRegion'])->name('regions.destroy');
    
    // Departments Management
    Route::get('/departments', [SuperAdminController::class, 'departments'])->name('departments');
    Route::post('/departments', [SuperAdminController::class, 'storeDepartment'])->name('departments.store');
    Route::get('/departments/{department}/edit', [SuperAdminController::class, 'editDepartment'])->name('departments.edit');
    Route::put('/departments/{department}', [SuperAdminController::class, 'updateDepartment'])->name('departments.update');
    Route::delete('/departments/{department}', [SuperAdminController::class, 'deleteDepartment'])->name('departments.delete');
    
    // Schedule Management
    Route::get('/schedule', [SuperAdminController::class, 'schedule'])->name('schedule');
    Route::post('/schedule/exception', [SuperAdminController::class, 'storeScheduleException'])->name('schedule.exception.store');
    Route::delete('/schedule/exception', [SuperAdminController::class, 'deleteScheduleException'])->name('schedule.exception.delete');
    
    // Profile Management
    Route::get('/profile', [SuperAdminController::class, 'profile'])->name('profile');
    Route::put('/profile', [SuperAdminController::class, 'updateProfile'])->name('profile.update');
    
    // Notification routes
    Route::get('/notifications', function() {
        $superAdminId = auth('super_admin')->user()->id;
        $notifications = \App\Models\AppNotification::where('notify_to', $superAdminId)
            ->with(['application', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        if (request()->expectsJson()) {
            return response()->json($notifications);
        }
        
        return view('super-admin.notifications.index');
    })->name('notifications');
    
    Route::post('/notifications/{id}/read', function($id) {
        \App\Models\AppNotification::where('id', $id)
            ->update(['status' => 'checked']);
        
        return redirect()->route('super-admin.applications');
    })->name('notifications.read');
    
    Route::delete('/notifications/{id}/read', function($id) {
        \App\Models\AppNotification::where('id', $id)->delete();
        return response()->json(['success' => true]);
    });
    
    Route::delete('/notifications/clear-all', function() {
        $superAdminId = auth('super_admin')->user()->id;
        \App\Models\AppNotification::where('notify_to', $superAdminId)->delete();
        return response()->json(['success' => true]);
    });
});

// Super Admin Auth Routes (No Middleware)
Route::get('/super-admin/login', [SuperAdminController::class, 'showLogin'])->name('super-admin.login');
Route::post('/super-admin/login', [SuperAdminController::class, 'login'])->name('super-admin.login.post');
Route::get('/super-admin/register', [SuperAdminController::class, 'showRegister'])->name('super-admin.register');
Route::post('/super-admin/register', [SuperAdminController::class, 'register'])->name('super-admin.register.post');
Route::post('/super-admin/logout', [SuperAdminController::class, 'logout'])->name('super-admin.logout');

// Super Admin Password Reset Routes
Route::get('/super-admin/forgot-password', [SuperAdminController::class, 'showForgotPassword'])->name('super-admin.password.request');
Route::post('/super-admin/forgot-password', [SuperAdminController::class, 'sendResetLink'])->name('super-admin.password.email');
Route::get('/super-admin/reset-password/{token}', [SuperAdminController::class, 'showResetPassword'])->name('super-admin.password.reset');
Route::post('/super-admin/reset-password', [SuperAdminController::class, 'resetPassword'])->name('super-admin.password.update');

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
    Route::get('/attendance/filter', [AdminController::class, 'attendanceFilter'])->name('attendance.filter');
    Route::get('/time-entries', [AdminController::class, 'timeEntries'])->name('time-entries');
    Route::get('/entry-images', [AdminController::class, 'entryImages'])->name('entry-images');
    Route::get('/attendance/export/{date?}', [AdminController::class, 'exportAttendance'])->name('attendance.export');
    
    // Employee History
    Route::get('/employee-history', [AdminController::class, 'employeeHistory'])->name('employee-history');
    
    // Time Entries
    Route::post('/time-entries/update', [AdminController::class, 'updateTimeEntry'])->name('time-entries.update');
    
    // Reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::post('/reports/generate', [AdminController::class, 'reports'])->name('reports.generate');
    Route::get('/reports/export/{type}', [AdminController::class, 'exportReport'])->name('reports.export');
    
    // Salary Reports Download
    Route::get('/salary-reports/{id}/download', [AdminController::class, 'downloadSalaryReport'])->name('salary-reports.download');
    Route::get('/salary-reports/{id}/preview', [AdminController::class, 'getSalarySlipPreview'])->name('salary-reports.preview');
    Route::get('/salary-reports/{id}/view', [AdminController::class, 'showSalarySlipPreview'])->name('salary-reports.view');
    Route::get('/salary-reports/{id}/edit', [AdminController::class, 'editSalaryReport'])->name('salary-reports.edit');
    Route::put('/salary-reports/{id}', [AdminController::class, 'updateSalaryReport'])->name('salary-reports.update');
    
    // Schedule Management
    Route::get('/schedule', [AdminController::class, 'schedule'])->name('schedule');
    Route::post('/schedule/exception', [AdminController::class, 'storeScheduleException'])->name('schedule.exception.store');
    Route::delete('/schedule/exception', [AdminController::class, 'deleteScheduleException'])->name('schedule.exception.delete');
    
    // Salary Management
    Route::get('/salaries', [AdminController::class, 'salaries'])->name('salaries');
    Route::get('/salaries/create', [AdminController::class, 'createSalary'])->name('salaries.create');
    Route::post('/salaries', [AdminController::class, 'storeSalary'])->name('salaries.store');
    Route::get('/salaries/{salary}/edit', [AdminController::class, 'editSalary'])->name('salaries.edit');
    Route::put('/salaries/{salary}', [AdminController::class, 'updateSalary'])->name('salaries.update');
    Route::get('/salaries/pending-employees', [AdminController::class, 'getPendingEmployees'])->name('salaries.pending-employees');
    
    // Profile Management
    Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
    Route::put('/profile', [AdminController::class, 'updateProfile'])->name('profile.update');
    
    // Notification routes
    Route::get('/notifications', function() {
        $adminEmpId = Auth::user()->emp_id;
        $notifications = \App\Models\AppNotification::where('notify_to', $adminEmpId)
            ->whereHas('createdBy', function($query) use ($adminEmpId) {
                $query->where('referrance', $adminEmpId);
            })
            ->with(['application', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        if (request()->expectsJson()) {
            return response()->json($notifications);
        }
        
        return view('admin.notifications.index');
    })->name('notifications');
    
    Route::post('/notifications/{id}/read', function($id) {
        \App\Models\AppNotification::where('id', $id)
            ->where('notify_to', Auth::user()->emp_id)
            ->update(['status' => 'checked']);
        return redirect()->route('admin.applications');
    })->name('notifications.read');
    
    Route::delete('/notifications/{id}/read', function($id) {
        \App\Models\AppNotification::where('id', $id)->delete();
        return response()->json(['success' => true]);
    });
    
    Route::delete('/notifications/clear-all', function() {
        $adminEmpId = Auth::user()->emp_id;
        \App\Models\AppNotification::where('notify_to', $adminEmpId)
            ->whereHas('createdBy', function($query) use ($adminEmpId) {
                $query->where('referrance', $adminEmpId);
            })
            ->delete();
        return response()->json(['success' => true]);
    });
    
    // Location Settings
    Route::get('/location-settings', [\App\Http\Controllers\Admin\LocationSettingsController::class, 'index'])->name('location-settings.index');
    Route::post('/location-settings', [\App\Http\Controllers\Admin\LocationSettingsController::class, 'update'])->name('location-settings.update');
    
    // Legacy Admin Routes (keeping for backward compatibility)
    Route::post('/dashboard/data', [AdminDashboardController::class, 'getEmployeeData'])->name('dashboard.data');
    Route::resource('employees-legacy', EmployeeController::class);
    Route::get('/settings-legacy', [\App\Http\Controllers\Admin\SystemSettingController::class, 'index'])->name('settings-legacy.index');
    Route::post('/settings-legacy', [\App\Http\Controllers\Admin\SystemSettingController::class, 'update'])->name('settings-legacy.update');
    Route::post('/applications/{application}/approve', [AdminDashboardController::class, 'approveApplication'])->name('applications.approve');
    Route::post('/applications/{application}/reject', [AdminDashboardController::class, 'rejectApplication'])->name('applications.reject');
});
