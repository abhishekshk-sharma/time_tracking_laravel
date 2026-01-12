@extends('super-admin.layouts.app')

@section('title', 'Edit Admin')

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Edit Administrator</h1>
            <p class="page-subtitle">{{ $admin->username }} ({{ $admin->emp_id }})</p>
        </div>
        <a href="{{ route('super-admin.admins') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST" action="{{ route('super-admin.admins.update', $admin) }}">
    @csrf
    @method('PUT')
    
    <div style="display: grid; grid-template-columns: 1fr; gap: 30px;">
        <!-- Admin Details -->
        <div class="card" >
            <div class="card-header">
                <h3 class="card-title">Admin Information</h3>
            </div>
            <div class="card-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <div class="form-group">
                    <label class="form-label">Employee ID</label>
                    <input type="text" class="form-control" value="{{ $admin->emp_id }}" readonly style="background: #f5f5f7;">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="{{ old('username', $admin->username) }}" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $admin->email) }}" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <select class="form-control" name="department_id">
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ $admin->department_id == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Branch</label>
                    <select class="form-control" name="region_id">
                        <option value="">Select Branch</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->id }}" {{ $admin->region_id == $region->id ? 'selected' : '' }}>
                                {{ $region->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Designation</label>
                    <input type="text" name="position" class="form-control" value="{{ old('position', $admin->position) }}">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status" required>
                        <option value="active" {{ $admin->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $admin->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Address:</label>
                    <textarea type="textarea" name="address" class="form-control" >{{$admin->address}}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Reference/Super Admin</label>
                    <input type="text" name="referrance" class="form-control" value="{{ old('referrance', $admin->referrance) }}">
                </div>
            </div>
        </div>
        
        <!-- Password Change Section -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Change Password</h3>
                <small class="text-muted">Leave blank to keep current password</small>
            </div>
            <div class="card-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter new password">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm new password">
                </div>
            </div>
        </div>

        <!-- Employee Assignment -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Employee Management</h3>
            </div>
            <div class="card-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <!-- Currently Assigned Employees -->
                <div class="form-group" style="margin-bottom: 30px;">
                    <label class="form-label">Currently Assigned Employees ({{ $assignedEmployees->count() }})</label>
                    <div style="margin-bottom: 10px;">
                        <input type="text" id="assignedSearch" class="form-control" placeholder="Search by username or employee ID" style="max-width: 300px;">
                    </div>
                    <div id="assignedEmployeesList" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 20px; background: #f8f9fa;">
                        @foreach($assignedEmployees as $employee)
                            <div class="form-check assigned-employee" data-search="{{ strtolower($employee->username . ' ' . $employee->emp_id) }}" style="margin-bottom: 8px; padding: 15px; background: white; border-radius: 6px; border: 1px solid #e9ecef;">
                                <input type="checkbox" 
                                       name="unassign_employees[]" 
                                       value="{{ $employee->emp_id }}" 
                                       id="unassign_{{ $employee->emp_id }}" 
                                       class="form-check-input" style="margin-left: -32px;">
                                <label class="form-check-label" for="unassign_{{ $employee->emp_id }}" style="display: flex; align-items: center; cursor: pointer;">
                                    <div style="width: 24px; height: 24px; background: #28a745; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px; font-weight: 600; margin-right: 8px;">
                                        {{ strtoupper(substr($employee->username, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 500; font-size: 14px;">{{ $employee->username }}</div>
                                        <div style="font-size: 11px; color: #86868b;">{{ $employee->emp_id }} - {{ $employee->department->name ?? 'No Department' }}</div>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                        
                        @if($assignedEmployees->count() == 0)
                            <div style="text-align: center; color: #86868b; padding: 20px;">
                                <i class="fas fa-user-slash" style="font-size: 24px; margin-bottom: 8px; opacity: 0.3;"></i>
                                <p>No employees currently assigned</p>
                            </div>
                        @endif
                    </div>
                    <small style="color: #86868b;">Check employees to unassign from this administrator</small>
                </div>
                
                <!-- Select Employees to Assign -->
                <div class="form-group">
                    <label class="form-label">Select Employees to Assign</label>
                    <div style="margin-bottom: 10px;">
                        <input type="text" id="unassignedSearch" class="form-control" placeholder="Search by username or employee ID" style="max-width: 300px;">
                    </div>
                    <div id="unassignedEmployeesList" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 12px;">
                        @foreach($unassignedEmployees as $employee)
                            <div class="form-check unassigned-employee" data-search="{{ strtolower($employee->username . ' ' . $employee->emp_id) }}" style="margin-bottom: 8px;">
                                <input type="checkbox" 
                                       name="assigned_employees[]" 
                                       value="{{ $employee->emp_id }}" 
                                       id="emp_{{ $employee->emp_id }}" 
                                       class="form-check-input"
                                       {{ $employee->referrance == $admin->emp_id ? 'checked' : '' }}>
                                <label class="form-check-label" for="emp_{{ $employee->emp_id }}" style="display: flex; align-items: center; cursor: pointer;">
                                    <div style="width: 24px; height: 24px; background: #ff6b35; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px; font-weight: 600; margin-right: 8px;">
                                        {{ strtoupper(substr($employee->username, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 500; font-size: 14px;">{{ $employee->username }}</div>
                                        <div style="font-size: 11px; color: #86868b;">{{ $employee->emp_id }} - {{ $employee->department->name ?? 'No Department' }}</div>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                        
                        @if($unassignedEmployees->count() == 0)
                            <div style="text-align: center; color: #86868b; padding: 20px;">
                                <i class="fas fa-users" style="font-size: 24px; margin-bottom: 8px; opacity: 0.3;"></i>
                                <p>No employees available for assignment</p>
                            </div>
                        @endif
                    </div>
                    <small style="color: #86868b;">Check the employees you want to assign to this administrator</small>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 30px; text-align: center;">
        <button type="submit" class="btn btn-primary" style="padding: 12px 40px;">
            <i class="fas fa-save"></i> Update Administrator
        </button>
    </div>
</form>

@endsection

@push('scripts')
<script>
// Search functionality for assigned employees
document.getElementById('assignedSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const employees = document.querySelectorAll('.assigned-employee');
    
    employees.forEach(function(employee) {
        const searchData = employee.getAttribute('data-search');
        if (searchData.includes(searchTerm)) {
            employee.style.display = 'block';
        } else {
            employee.style.display = 'none';
        }
    });
});

// Search functionality for unassigned employees
document.getElementById('unassignedSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const employees = document.querySelectorAll('.unassigned-employee');
    
    employees.forEach(function(employee) {
        const searchData = employee.getAttribute('data-search');
        if (searchData.includes(searchTerm)) {
            employee.style.display = 'block';
        } else {
            employee.style.display = 'none';
        }
    });
});
</script>
@endpush