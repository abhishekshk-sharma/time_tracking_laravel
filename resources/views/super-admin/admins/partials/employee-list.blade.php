@if($assignedEmployees->count() > 0)
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px; margin-bottom: 20px;">
        @foreach($assignedEmployees as $employee)
            <div class="employee-card">
                <div class="employee-avatar">
                    {{ strtoupper(substr($employee->username, 0, 1)) }}
                </div>
                <div class="employee-info">
                    <div class="employee-name">{{ $employee->username }}</div>
                    <div class="employee-details">{{ $employee->emp_id }}</div>
                    <div class="employee-details">{{ $employee->department->name ?? 'No Department' }}</div>
                    <div class="employee-details">{{ $employee->designation ?? 'No Designation' }}</div>
                    <span class="badge {{ $employee->status == 'active' ? 'badge-success' : 'badge-danger' }} employee-status">
                        {{ ucfirst($employee->status) }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>
    
    <!-- Pagination -->
    <div style="display: flex; justify-content: center;">
        {{ $assignedEmployees->appends(request()->query())->links() }}
    </div>
@else
    <div class="empty-state">
        <i class="fas fa-users"></i>
        <p>{{ request('search') ? 'No employees found matching your search' : 'No employees assigned to this administrator' }}</p>
    </div>
@endif