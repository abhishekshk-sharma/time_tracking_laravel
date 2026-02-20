@extends('admin.layouts.app')

@section('title', 'Edit Employee')

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Edit Employee</h1>
            <p class="page-subtitle">Update employee information</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.employees.show', $employee) }}" class="btn btn-primary">
                <i class="fas fa-user"></i> Go to Profile
            </a>
            <a href="{{ route('admin.employees') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Employee Information</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.employees.update', $employee) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Employee ID</label>
                    <input type="text" class="form-control" value="{{ $employee->emp_id }}" disabled>
                    <small style="color: #565959;">Employee ID cannot be changed</small>
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
                    <label class="form-label">Department *</label>
                    <select name="department_id" class="form-control @error('department_id') is-invalid @enderror" required>
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" 
                                {{ old('department_id', $employee->department_id) == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('departments')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Position</label>
                    <input type="text" name="position" class="form-control @error('position') is-invalid @enderror" 
                           value="{{ old('position', $employee->position) }}">
                    @error('position')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Employee Grade</label>
                    <select name="senior_junior" class="form-control @error('senior_junior') is-invalid @enderror">
                        <option value="">Select Grade</option>
                        <option value="senior" {{ old('senior_junior', $employee->senior_junior) == 'senior' ? 'selected' : '' }}>Senior</option>
                        <option value="junior" {{ old('senior_junior', $employee->senior_junior) == 'junior' ? 'selected' : '' }}>Junior</option>
                    </select>
                    @error('senior_junior')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Is Metro City</label>
                    <select name="metro_city" class="form-control @error('metro_city') is-invalid @enderror">
                        <option value="">Select Metro Status</option>
                        <option value="1" {{ old('metro_city', $employee->metro_city) == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('metro_city', $employee->metro_city) == '0' ? 'selected' : '' }}>No</option>
                    </select>
                    @error('metro_city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                           value="{{ old('phone', $employee->phone) }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status', $employee->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $employee->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">DOB</label>
                    <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror" 
                           value="{{ old('dob', $employee->dob ? $employee->dob->format('Y-m-d') : '') }}">
                    <small style="color: #565959;">Date Of Birth</small>
                    @error('dob')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Hire Date</label>
                    <input type="date" name="hire_date" class="form-control @error('hire_date') is-invalid @enderror" 
                           value="{{ old('hire_date', $employee->hire_date ? $employee->hire_date->format('Y-m-d') : '') }}">
                    <small style="color: #565959;">Hire Date</small>
                    @error('hire_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" 
                           value="{{ old('end_date', $employee->end_date ? $employee->end_date->format('Y-m-d') : '') }}">
                    <small style="color: #565959;">Setting an end date will automatically make the employee inactive</small>
                    @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Leave blank to keep current password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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

            <div style="display: flex; gap: 15px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Employee
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Employee Stats -->
<div class="card" style="margin-top: 30px;">
    <div class="card-header">
        <h3 class="card-title">Employee Statistics</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 24px; font-weight: 600; color: #c7511f; margin-bottom: 5px;">
                    {{ $employee->created_at->format('M d, Y') }}
                </div>
                <div style="color: #565959; font-size: 14px;">Join Date</div>
            </div>
            <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 24px; font-weight: 600; color: #c7511f; margin-bottom: 5px;">
                    {{ $employee->applications()->count() }}
                </div>
                <div style="color: #565959; font-size: 14px;">Total Applications</div>
            </div>
            <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 24px; font-weight: 600; color: #c7511f; margin-bottom: 5px;">
                    {{ $employee->timeEntries()->whereDate('entry_time', '>=', now()->startOfMonth())->where('entry_type', 'punch_in')->count() }}
                </div>
                <div style="color: #565959; font-size: 14px;">Days Present This Month</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Automatically set status to inactive when end_date is selected
document.addEventListener('DOMContentLoaded', function() {
    const endDateInput = document.querySelector('input[name="end_date"]');
    const statusSelect = document.querySelector('select[name="status"]');
    
    if (endDateInput && statusSelect) {
        endDateInput.addEventListener('change', function() {
            if (this.value) {
                statusSelect.value = 'inactive';
                statusSelect.style.backgroundColor = '#fee2e2';
            } else {
                statusSelect.style.backgroundColor = '';
            }
        });
        
        // Set initial background color if end_date is already set
        if (endDateInput.value) {
            statusSelect.style.backgroundColor = '#fee2e2';
        }
    }
});
</script>
@endpush

@push('styles')
<style>
.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: #d13212;
}

.form-control.is-invalid {
    border-color: #d13212;
}

.form-control.is-invalid:focus {
    border-color: #d13212;
    box-shadow: 0 0 0 2px rgba(209, 50, 18, 0.2);
}
</style>
@endpush