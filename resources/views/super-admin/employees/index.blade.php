@extends('super-admin.layouts.app')

@section('title', 'Employees')

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Employees</h1>
            <p class="page-subtitle">Manage employee records and information</p>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('super-admin.employees') }}" style="display: grid; grid-template-columns: 200px 120px; gap: 15px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="active" {{ request('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Filter
            </button>
        </form>
    </div>
</div>

<!-- Employees Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Employee List ({{ $employees->total() }} total)</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        @if($employees->count() > 0)
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Region</th>
                            <th>Current Salary</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <div style="width: 32px; height: 32px; background: #ff6b35; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; margin-right: 10px;">
                                        {{ strtoupper(substr($employee->username ?? 'N', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 500;">{{ $employee->name }}</div>
                                        <div style="font-size: 12px; color: #565959;">{{ $employee->emp_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($employee->department && is_object($employee->department))
                                    <span class="badge badge-secondary">{{ $employee->department->name }}</span>
                                @elseif($employee->department)
                                    <span class="badge badge-secondary">{{ $employee->department }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $employee->email }}</td>
                            <td>{{ $employee->phone ?: '-' }}</td>
                            <td>
                                <span class="badge badge-info">{{ $employee->region ?: 'Not Set' }}</span>
                            </td>
                            <td>
                                @if($employee->salary)
                                    <div>
                                        <strong>₹{{ number_format($employee->salary->gross_salary, 2) }}</strong>
                                        <div style="font-size: 11px; color: #86868b;">
                                            Basic: ₹{{ number_format($employee->salary->basic_salary, 0) }}
                                        </div>
                                    </div>
                                @else
                                    <span style="color: #ef4444; font-size: 12px;">No salary set</span>
                                @endif
                            </td>
                            <td>
                                @if($employee->status === 'active')
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="{{ route('super-admin.employees.show', $employee) }}" class="btn btn-sm btn-secondary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($employees->hasPages())
                <div style="padding: 20px; border-top: 1px solid #eee;">
                    {{ $employees->links() }}
                </div>
            @endif
        @else
            <div style="text-align: center; padding: 40px; color: #565959;">
                <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                <h3>No employees found</h3>
                <p>No employees available with the selected filters</p>
            </div>
        @endif
    </div>
</div>
@endsection