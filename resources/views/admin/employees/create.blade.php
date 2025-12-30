@extends('admin.layouts.app')

@section('title', 'Add Employee')

@section('content')
<div class="page-header">
    <h1 class="page-title">Add Employee</h1>
    <p class="page-subtitle">Create a new employee account</p>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Employee Information</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.employees.store') }}" method="POST">
            @csrf
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Employee ID *</label>
                    <input type="text" name="emp_id" class="form-control @error('emp_id') is-invalid @enderror" 
                           value="{{ old('emp_id') }}" placeholder="e.g., EMP001" required>
                    @error('emp_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name') }}" placeholder="Enter full name" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email') }}" placeholder="employee@company.com" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Minimum 6 characters" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Department *</label>
                    <select name="department_id" class="form-control @error('department_id') is-invalid @enderror" required>
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                           value="{{ old('phone') }}" placeholder="Enter phone number">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control @error('address') is-invalid @enderror" 
                          rows="3" placeholder="Enter complete address">{{ old('address') }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; gap: 15px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Employee
                </button>
                <a href="{{ route('admin.employees') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

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