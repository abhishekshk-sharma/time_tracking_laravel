<?php

use Illuminate\Http\Request;
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
use App\Http\Controllers\Api\EmployeeApiController;

// Employee Mobile API Routes
Route::prefix('employee')->group(function () {
    // Public routes
    Route::post('/login', [EmployeeApiController::class, 'login']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Authentication
        Route::post('/logout', [EmployeeApiController::class, 'logout']);
        Route::get('/profile', [EmployeeApiController::class, 'profile']);
        
        // Time Tracking
        Route::post('/clock-in', [EmployeeApiController::class, 'clockIn']);
        Route::post('/clock-out', [EmployeeApiController::class, 'clockOut']);
        Route::post('/lunch-start', [EmployeeApiController::class, 'lunchStart']);
        Route::post('/lunch-end', [EmployeeApiController::class, 'lunchEnd']);
        Route::get('/today-status', [EmployeeApiController::class, 'todayStatus']);
        Route::get('/attendance-history', [EmployeeApiController::class, 'attendanceHistory']);
        
        // Leave Management
        Route::post('/apply-leave', [EmployeeApiController::class, 'applyLeave']);
        Route::get('/leave-applications', [EmployeeApiController::class, 'leaveApplications']);
        Route::get('/leave-balance', [EmployeeApiController::class, 'leaveBalance']);
        
        // WFH Management
        Route::post('/apply-wfh', [EmployeeApiController::class, 'applyWfh']);
        Route::get('/wfh-requests', [EmployeeApiController::class, 'wfhRequests']);
        
        // System
        Route::get('/system-settings', [EmployeeApiController::class, 'systemSettings']);
    });
});


