@extends('super-admin.layouts.app')

@section('title', 'Edit Employee')

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Edit Employee</h1>
            <p class="page-subtitle">{{ $employee->username }} ({{ $employee->emp_id }})</p>
        </div>
        <a href="{{ route('super-admin.employees') }}" class="btn btn-secondary">
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
            {{-- <li>{{$errors}}</li> --}}
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST" action="{{ route('super-admin.employees.update', $employee) }}">
    @csrf
    @method('PUT')
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        <!-- Employee Details -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Employee Information</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Employee ID</label>
                    <input type="text" class="form-control" value="{{ $employee->emp_id }}" readonly style="background: #f5f5f7;">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" 
                           value="{{ old('full_name', $employee->full_name) }}" required>
                    @error('full_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>  
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" 
                           value="{{ old('username', $employee->username) }}" required>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email', $employee->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Phone *</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                           value="{{ old('phone', $employee->phone) }}" required>
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">DOB</label>
                    <input type="date" name="dob" class="form-control" value="{{ old('dob', $employee->dob) }}" @error('dob') is-invalid @enderror">
                </div>

                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select class="form-control" name="role" required @error('role') is-invalid @enderror"> 
                        <option value="employee" {{ $employee->role == 'employee' ? 'selected' : '' }}>Employee</option>
                        <option value="admin" {{ $employee->role == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status" required @error('status') is-invalid @enderror">
                        <option value="active" {{ $employee->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $employee->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">City Category</label>
                    <select class="form-control" name="metro_city" required @error('metro_city') is-invalid @enderror">
                        <option value="0" {{ $employee->metro_city == 0 ? 'selected' : '' }}>Non-Metro City</option>
                        <option value="1" {{ $employee->metro_city == 1 ? 'selected' : '' }}>Metro City</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Additional Details -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Additional Information</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <select class="form-control" name="department_id" @error('department_id') is-invalid @enderror">
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ $employee->department_id == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Position</label>
                    <input type="text" name="position" class="form-control" value="{{ old('position', $employee->position) }}" @error('position') is-invalid @enderror">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Level</label>
                    <select name="senior_junior" class="form-control @error('senior_junior') is-invalid @enderror">
                        <option value="junior" {{ old('senior_junior', $employee->senior_junior) == 'junior' ? 'selected' : '' }}>Junior</option>
                        <option value="senior" {{ old('senior_junior', $employee->senior_junior) == 'senior' ? 'selected' : '' }}>Senior</option>
                    </select>
                    @error('senior_junior')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Hire Date</label>
                    <input type="date" name="hire_date" class="form-control"  value="{{ old('hire_date', $employee->hire_date ? $employee->hire_date->format('Y-m-d') : '') }}" required @error('hire_date') is-invalid @enderror">
                </div>
                
                <div class="form-group">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $employee->end_date) }}" @error('end_date') is-invalid @enderror">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Branch</label>
                    <select class="form-control" name="region_id" @error('region_id') is-invalid @enderror">
                        <option value="">Select Branch</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->id }}" {{ $employee->region_id == $region->id ? 'selected' : '' }}>
                                {{ $region->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
             
                <div class="form-group">
                    <label class="form-label">Referrance Admin</label>
                    <select class="form-control" name="referrance" @error('referrance') is-invalid @enderror">
                        <option value="">Select Admin</option>
                        
                        @foreach($admins as $admin)
                            <option value="{{ $admin->emp_id }}" {{ old('referrance', $employee->referrance) == $admin->emp_id ? 'selected' : '' }} >
                                {{ $admin->username }} --  {{ $admin->department?->name ?? 'No Department Assigned' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder='Optional' @error('password') is-invalid @enderror">
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control @error('address') is-invalid @enderror" 
                          rows="3">{{ old('address', $employee->address) }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

    <!-- Leave Count Section -->
    <div class="card" style="margin-top: 20px; border: 1px solid #e0e2e6; border-radius: 8px;">
        <div class="card-header" style="background: #f8f9fa; padding: 15px; border-bottom: 1px solid #e0e2e6;">
            <h4 style="margin: 0; font-size: 16px; color: #0f1111;">Leave Balance</h4>
            <small style="color: #565959;">Manage employee's available leave days</small>
        </div>
        <div class="card-body" style="padding: 20px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Casual Leave Days</label>
                    <input type="number" name="casual_leave" class="form-control @error('casual_leave') is-invalid @enderror" 
                           value="{{ old('casual_leave', $leaveCount->casual_leave ?? 0) }}" min="0" max="365">
                    <small style="color: #565959;">Available casual leave days for the employee</small>
                    @error('casual_leave')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Sick Leave Days</label>
                    <input type="number" name="sick_leave" class="form-control @error('sick_leave') is-invalid @enderror" 
                           value="{{ old('sick_leave', $leaveCount->sick_leave ?? 0) }}" min="0" max="365">
                    <small style="color: #565959;">Available sick leave days for the employee</small>
                    @error('sick_leave')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 30px; text-align: center;">
        <button type="submit" class="btn btn-primary" style="padding: 12px 40px;">
            <i class="fas fa-save"></i> Update Employee
        </button>
    </div>
</form>

@endsection