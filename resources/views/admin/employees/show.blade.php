@extends('admin.layouts.app')

@section('title', 'Employee Details')

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Employee Details</h1>
            <p class="page-subtitle">{{ $employee->full_name }} ({{ $employee->emp_id }})</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Employee
            </a>
            <a href="{{ route('admin.employees.history', $employee) }}" class="btn btn-secondary">
                <i class="fas fa-history"></i> View History
            </a>
            <a href="{{ route('admin.employees') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>

<!-- Employee Information -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Employee Information</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div>
                <div class="form-group">
                    <label class="form-label">Employee ID</label>
                    <div style="padding: 10px; background: #f8f9fa; border-radius: 4px; font-weight: 500;">
                        {{ $employee->emp_id }}
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
                        {{ $employee->username }}
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
                        {{ $employee->email }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
                        {{ $employee->phone ?: 'Not provided' }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">DOB</label>
                    <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
                        {{ $employee->dob ? $employee->dob->format('M d, Y') : 'N/A' }}
                    </div>
                </div>
                
            </div>

            
            <div>
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
                        @if($employee->department && is_object($employee->department))
                            {{ $employee->department->name }}
                        @elseif($employee->department)
                            {{ $employee->department }}
                        @else
                            Not Assigned
                        @endif
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Position</label>
                    <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
                        {{ $employee->position }}
                    </div>
                </div>
                
                
                
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
                        @if($employee->status === 'active')
                            <span class="badge text-bg-success">Active</span>
                        @else
                            <span class="badge text-bg-danger">Inactive</span>
                        @endif
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Joined Date</label>
                    <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
                        {{ $employee->created_at ? $employee->created_at->format('M d, Y') : 'N/A' }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">End Date</label>
                    <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
                        {{ $employee->end_date ? $employee->end_date->format('M d, Y') : 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
        
        @if($employee->address)
        <div class="form-group" style="margin-top: 20px;">
            <label class="form-label">Address</label>
            <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
                {{ $employee->address }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Quick Stats -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quick Statistics</h3>
    </div>
    <div class="card-body">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">{{ $employee->timeEntries()->whereMonth('entry_time', now()->month)->where('entry_type', 'punch_in')->count() }}</div>
                <div class="stat-label">Days Present This Month</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $employee->applications()->where('status', 'pending')->count() }}</div>
                <div class="stat-label">Pending Applications</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $employee->applications()->whereMonth('created_at', now()->month)->count() }}</div>
                <div class="stat-label">Applications This Month</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $employee->timeEntries()->count() }}</div>
                <div class="stat-label">Total Time Entries</div>
            </div>
        </div>
    </div>
</div>
@endsection