Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Employee Management
    Route::get('/employees', [AdminController::class, 'employees'])->name('employees');
    Route::get('/employees/create', [AdminController::class, 'createEmployee'])->name('employees.create');
    Route::post('/employees', [AdminController::class, 'storeEmployee'])->name('employees.store');
    Route::get('/employees/{employee}/edit', [AdminController::class, 'editEmployee'])->name('employees.edit');
    Route::put('/employees/{employee}', [AdminController::class, 'updateEmployee'])->name('employees.update');
    
    // Application Management
    Route::get('/applications', [AdminController::class, 'applications'])->name('applications');
    Route::post('/applications/{application}/status', [AdminController::class, 'updateApplicationStatus'])->name('applications.status');
    
    // Attendance Management
    Route::get('/attendance', [AdminController::class, 'attendance'])->name('attendance');
    
    // Reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    
    // Settings
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
});