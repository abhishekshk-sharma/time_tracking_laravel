@extends('admin.layouts.app')

@section('title', 'Employees')

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Employees</h1>
            <p class="page-subtitle">Manage your workforce</p>
        </div>
        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Employee
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.employees') }}" style="display: grid; grid-template-columns: 1fr 200px 200px 120px; gap: 15px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search by name, ID, or email" value="{{ request('search') }}">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Department</label>
                <select name="department" class="form-control">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ request('department') == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>
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
                            <th>Joined</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <div style="width: 32px; height: 32px; background: #ff9900; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; margin-right: 10px;">
                                        {{ strtoupper(substr($employee->username ?? 'N', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 500;">{{ $employee->username ?? 'Unknown' }}</div>
                                        <div style="font-size: 12px; color: #565959;">{{ $employee->emp_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                
                                <div style="display: flex; align-items: center;">
                                    
                                    <div>
                                        <div style="font-weight: 500;">{{ $employee->name }}</div>
                                        <div style="font-size: 12px; color: #565959;">Joined {{ $employee->created_at instanceof \Carbon\Carbon ? $employee->created_at->format('M Y') : 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $employee->email }}</td>
                            <td>
                                @if($employee->status == 'inactive' && $employee->department && is_object($employee->department))
                                    <span class="badge text-bg-secondary p-2 " style="font-size: 0.8rem;">{{ $employee->department->name }}</span>
                                @elseif($employee->department && is_object($employee->department))
                                    <span class="badge text-bg-primary p-2 " style="font-size: 0.8rem;">{{ $employee->department->name }}</span>
                                @elseif($employee->department)
                                    <span class="badge text-bg-primary" style="font-size: 0.8rem;">{{ $employee->department }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $employee->phone ?: '-' }}</td>
                            <td>
                                @if($employee->status === 'active')
                                    <span class="badge text-bg-success" style="font-size: 0.8rem;">Active</span>
                                @else
                                    <span class="badge text-bg-danger" style="font-size: 0.8rem;">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="{{ route('admin.employees.show', $employee) }}" class="btn btn-sm btn-secondary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-sm btn-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
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
                <p>{{ request('search') ? 'Try adjusting your search criteria' : 'Get started by adding your first employee' }}</p>
                @if(!request('search'))
                    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary" style="margin-top: 15px;">
                        <i class="fas fa-plus"></i> Add Employee
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
@endpush