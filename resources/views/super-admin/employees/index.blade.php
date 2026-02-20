@extends('super-admin.layouts.app')

@section('title', 'Employees')

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Employees</h1>
            <p class="page-subtitle">Manage employee records and information</p>
        </div>
        <div>
            <a href="{{ route('super-admin.employees.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Employee
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title">Search & Filter Employees</h3>
        <div style="display: flex; align-items: center; gap: 10px;">
            <label style="font-size: 12px; color: #666; margin: 0;">Per Page:</label>
            <select id="per-page-select" style="padding: 4px 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; align-items: end;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 5px;">Search Employee</label>
                <input type="text" id="employee-search" placeholder="Employee ID or Username" 
                       style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; width: 100%;" 
                       value="{{ request('search') }}">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 5px;">Filter by Admin</label>
                <select id="admin-filter" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; width: 100%;">
                    <option value="">All Admins</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->emp_id }}" {{ request('admin_filter') == $admin->emp_id ? 'selected' : '' }}>
                            {{ $admin->username }} ({{ $admin->emp_id }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 5px;">Filter by Department</label>
                <select id="department-filter" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; width: 100%;">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ request('department') == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 5px;">Status</label>
                <select id="status-filter" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; width: 100%;">
                    <option value="" {{ request('status') == '' ? 'selected' : '' }}>All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 5px;">Employee Grade</label>
                <select id="grade-filter" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; width: 100%;">
                    <option value="" {{ request('grade') == '' ? 'selected' : '' }}>All Grades</option>
                    <option value="senior" {{ request('grade') == 'senior' ? 'selected' : '' }}>Senior</option>
                    <option value="junior" {{ request('grade') == 'junior' ? 'selected' : '' }}>Junior</option>
                </select>
            </div>
            <div style="display: flex; gap: 8px;">
                <button onclick="applyFilters()" class="btn btn-primary" style="padding: 8px 20px; font-size: 14px;">
                    <i class="fas fa-search"></i> Search
                </button>
                <button onclick="clearFilters()" class="btn btn-secondary" style="padding: 8px 15px; font-size: 14px;">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>
        </div>
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
                            <th>Branch</th>
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
                                        <div style="font-weight: 500;">{{ $employee->username }}</div>
                                        <div style="font-size: 12px; color: #565959;">{{ $employee->emp_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($employee->department && is_object($employee->department))
                                    @if ($employee->status !== 'inactive')
                                
                                        <span class="badge p-2 text-bg-primary">{{ $employee->department->name }}</span>
                                    @else
                                        <span class="badge p-2 text-bg-secondary">{{ $employee->department->name }}</span>
                                    
                                    @endif
                                @elseif($employee->department)
                                    
                                    <span class="badge p-2 text-bg-secondary">{{ $employee->department }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $employee->email }}</td>
                            <td>{{ $employee->phone ?? '-' }}</td>
                            <td>
                                
                             
                                    @foreach ($admins as $admin)
                                        @if($admin->emp_id == $employee->referrance)


                                            @if ($employee->status !== 'inactive')
                                                @if (($employee->region->name ?? null) !== null)
                                                    <span class="badge p-2 text-bg-success">{{ $admin->region->name ?? 'Not Set' }}</span>
                                                @else

                                                <span class="badge p-2 text-bg-success">{{ $admin->region->name ?? 'Not Set' }}</span>
                                                @endif
                                            @else
                                                @if (($employee->region->name ?? null) !== null)
                                                    <span class="badge p-2 text-bg-secondary">{{ $admin->region->name ?? 'Not Set' }}</span>
                                                @else

                                                <span class="badge p-2 text-bg-secondary">{{ $admin->region->name ?? 'Not Set' }}</span>
                                                @endif
                                            
                                            @endif
                                           
                                            {{-- hello --}}
                                            @break;
                                        
                                        @endif
                                        
                                    @endforeach
                                

                                {{-- <span class="badge p-2 text-bg-info">{{ $employee->region ?? 'Not Set' }}</span> --}}
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
                                    <span class="badge p-2 text-bg-success">Active</span>
                                @else
                                    <span class="badge p-2 text-bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="{{ route('super-admin.employees.show', $employee) }}" class="btn btn-sm btn-secondary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('super-admin.employees.edit', $employee) }}" class="btn btn-sm btn-primary" title="Edit Employee">
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
                    {{ $employees->appends(request()->query())->links() }}
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

@push('scripts')
<script>
function applyFilters() {
    const search = document.getElementById('employee-search').value;
    const adminFilter = document.getElementById('admin-filter').value;
    const statusFilter = document.getElementById('status-filter').value;
    const gradeFilter = document.getElementById('grade-filter').value;
    const departmentFilter = document.getElementById('department-filter').value;

    // alert(statusFilter);
    const perPage = document.getElementById('per-page-select').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (adminFilter) params.append('admin_filter', adminFilter);
    if (statusFilter) params.append('status', statusFilter);
    if (gradeFilter) params.append('grade', gradeFilter);
    if (departmentFilter) params.append('department', departmentFilter);
    if (perPage) params.append('per_page', perPage);
    
    window.location.href = '{{ route("super-admin.employees") }}?' + params.toString();
}

function clearFilters() {
    document.getElementById('employee-search').value = '';
    document.getElementById('admin-filter').value = '';
    document.getElementById('status-filter').value = '';
    document.getElementById('per-page-select').value = '10';
    window.location.href = '{{ route("super-admin.employees") }}';
}

// Per page change handler
document.getElementById('per-page-select').addEventListener('change', function() {
    applyFilters();
});

// Enter key handler for search input
document.getElementById('employee-search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});
</script>
@endpush