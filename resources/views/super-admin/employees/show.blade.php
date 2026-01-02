@extends('super-admin.layouts.app')

@section('title', 'Employee Details')

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Employee Details</h1>
            <p class="page-subtitle">Complete information for {{ $employee->name }}</p>
        </div>
        <a href="{{ route('super-admin.employees.edit', $employee) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit Employee
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
    <!-- Personal Information -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Personal Information</h3>
        </div>
        <div class="card-body">
            <div style="display: grid; gap: 16px;">
                <div>
                    <label class="form-label">Full Name</label>
                    <div style="font-weight: 500;">{{ $employee->name ?: 'Not Set' }}</div>
                </div>
                <div>
                    <label class="form-label">Employee ID</label>
                    <div style="font-weight: 500;">{{ $employee->emp_id }}</div>
                </div>
                <div>
                    <label class="form-label">Username</label>
                    <div style="font-weight: 500;">{{ $employee->username ?: 'Not Set' }}</div>
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <div style="font-weight: 500;">{{ $employee->email ?: 'Not Set' }}</div>
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <div style="font-weight: 500;">{{ $employee->phone ?: 'Not Set' }}</div>
                </div>
                <div>
                    <label class="form-label">Date of Birth</label>
                    <div style="font-weight: 500;">{{ $employee->dob ? $employee->dob->format('d M Y') : 'Not Set' }}</div>
                </div>
                <div>
                    <label class="form-label">Address</label>
                    <div style="font-weight: 500;">{{ $employee->address ?: 'Not Set' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Work Information -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Work Information</h3>
        </div>
        <div class="card-body">
            <div style="display: grid; gap: 16px;">
                <div>
                    <label class="form-label">Department</label>
                    <div style="font-weight: 500;">{{ $employee->department->name ?: 'Not Set' }}</div>
                </div>
                <div>
                    <label class="form-label">Position</label>
                    <div style="font-weight: 500;">{{ $employee->position ?: 'Not Set' }}</div>
                </div>
                <div>
                    <label class="form-label">Region</label>
                    <div style="font-weight: 500;">
                        <span class="badge badge-info">{{ $employee->region ?: 'Not Set' }}</span>
                    </div>
                </div>
                <div>
                    <label class="form-label">Hire Date</label>
                    <div style="font-weight: 500;">{{ $employee->hire_date ? $employee->hire_date->format('d M Y') : 'Not Set' }}</div>
                </div>
                <div>
                    <label class="form-label">End Date</label>
                    <div style="font-weight: 500;">{{ $employee->end_date ? $employee->end_date->format('d M Y') : 'Not Set' }}</div>
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <div style="font-weight: 500;">
                        @if($employee->status === 'active')
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </div>
                </div>
                <div>
                    <label class="form-label">Role</label>
                    <div style="font-weight: 500;">
                        <span class="badge badge-secondary">{{ ucfirst($employee->role) }}</span>
                    </div>
                </div>
                <div>
                    <label class="form-label">Reference/Admin</label>
                    <div style="font-weight: 500;">
                        <span class="badge badge-secondary">{{ $employee->referrance }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Salary Information -->
<div class="card" style="margin-top: 30px;">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">Salary Information</h3>
            @if($employee->salary)
                <a href="{{ route('super-admin.salaries.edit', $employee->salary) }}" class="btn btn-secondary">
                    <i class="fas fa-edit"></i> Edit Salary
                </a>
            @else
                <a href="{{ route('super-admin.salaries.create') }}?emp_id={{ $employee->emp_id }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Salary
                </a>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if($employee->salary)
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div>
                    <label class="form-label">Basic Salary</label>
                    <div style="font-weight: 500; font-size: 18px;">₹{{ number_format($employee->salary->basic_salary, 2) }}</div>
                </div>
                <div>
                    <label class="form-label">HRA</label>
                    <div style="font-weight: 500; font-size: 18px;">₹{{ number_format($employee->salary->hra, 2) }}</div>
                </div>
                <div>
                    <label class="form-label">Travel Allowance</label>
                    <div style="font-weight: 500; font-size: 18px;">₹{{ number_format($employee->salary->ta, 2) }}</div>
                </div>
                <div>
                    <label class="form-label">Conveyance Allowance</label>
                    <div style="font-weight: 500; font-size: 18px;">₹{{ number_format($employee->salary->conveyance_allowance, 2) }}</div>
                </div>
                <div>
                    <label class="form-label">PF (Provident Fund)</label>
                    <div style="font-weight: 500; font-size: 18px; color: #ef4444;">-₹{{ number_format($employee->salary->pf, 2) }}</div>
                </div>
                <div>
                    <label class="form-label">PT (Professional Tax)</label>
                    <div style="font-weight: 500; font-size: 18px; color: #ef4444;">-₹{{ number_format($employee->salary->pt, 2) }}</div>
                </div>
            </div>
            <div style="margin-top: 20px; padding: 20px; background: #f0fdf4; border-radius: 8px; text-align: center;">
                <label class="form-label">Gross Salary</label>
                <div style="font-weight: 700; font-size: 24px; color: #059669;">₹{{ number_format($employee->salary->gross_salary, 2) }}</div>
                <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                    Effective from {{ $employee->salary->effective_from->format('d M Y') }}
                </div>
            </div>
        @else
            <div style="text-align: center; padding: 40px; color: #6b7280;">
                <i class="fas fa-money-bill-wave fa-3x" style="margin-bottom: 16px; opacity: 0.3;"></i>
                <div>No salary information available</div>
                <div style="margin-top: 8px;">
                    <a href="{{ route('super-admin.salaries.create') }}?emp_id={{ $employee->emp_id }}" class="btn btn-primary">Add Salary Details</a>
                </div>
            </div>
        @endif
    </div>
</div>

<div style="margin-top: 30px;">
    <a href="{{ route('super-admin.employees') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Employees
    </a>
</div>
@endsection