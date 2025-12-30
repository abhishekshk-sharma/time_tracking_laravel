@extends('super-admin.layouts.app')

@section('title', 'Edit Employee')

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Employee</h1>
    <p class="page-subtitle">Update employee information and details</p>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Employee Information</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('super-admin.employees.update', $employee) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control" 
                           value="{{ old('name', $employee->name) }}" required>
                    @error('name')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" 
                           value="{{ old('username', $employee->username) }}" required>
                    @error('username')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" 
                           value="{{ old('email', $employee->email) }}" required>
                    @error('email')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" name="phone" id="phone" class="form-control" 
                           value="{{ old('phone', $employee->phone) }}">
                    @error('phone')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="department" class="form-label">Department</label>
                    <input type="text" name="department" id="department" class="form-control" 
                           value="{{ old('department', $employee->department) }}">
                    @error('department')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="position" class="form-label">Position</label>
                    <input type="text" name="position" id="position" class="form-control" 
                           value="{{ old('position', $employee->position) }}">
                    @error('position')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="region" class="form-label">Region</label>
                    <select name="region" id="region" class="form-control">
                        <option value="">Select Region</option>
                        <option value="North" {{ old('region', $employee->region) == 'North' ? 'selected' : '' }}>North</option>
                        <option value="South" {{ old('region', $employee->region) == 'South' ? 'selected' : '' }}>South</option>
                        <option value="East" {{ old('region', $employee->region) == 'East' ? 'selected' : '' }}>East</option>
                        <option value="West" {{ old('region', $employee->region) == 'West' ? 'selected' : '' }}>West</option>
                        <option value="Central" {{ old('region', $employee->region) == 'Central' ? 'selected' : '' }}>Central</option>
                    </select>
                    @error('region')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="hire_date" class="form-label">Hire Date</label>
                    <input type="date" name="hire_date" id="hire_date" class="form-control" 
                           value="{{ old('hire_date', $employee->hire_date ? $employee->hire_date->format('Y-m-d') : '') }}">
                    @error('hire_date')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" 
                           value="{{ old('end_date', $employee->end_date ? $employee->end_date->format('Y-m-d') : '') }}">
                    @error('end_date')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="dob" class="form-label">Date of Birth</label>
                    <input type="date" name="dob" id="dob" class="form-control" 
                           value="{{ old('dob', $employee->dob ? $employee->dob->format('Y-m-d') : '') }}">
                    @error('dob')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="address" class="form-label">Address</label>
                <textarea name="address" id="address" class="form-control" rows="3">{{ old('address', $employee->address) }}</textarea>
                @error('address')
                    <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; gap: 12px; margin-top: 32px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Employee
                </button>
                <a href="{{ route('super-admin.employees.show', $employee) }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection