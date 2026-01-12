@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">Overview of your time tracking system</p>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number">{{ $stats['total_employees'] }}</div>
        <div class="stat-label">Total Employees</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style='color:#0f9703;'>{{ $stats['active_employees'] }}</div>
        <div class="stat-label">Active Employees</div>   
    </div>
    <div class="stat-card">
        <div class="stat-number " style="color:rgb(171, 6, 6);">{{ $stats['inactive_employees'] }}</div>
        <div class="stat-label">Inactive Employees</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $stats['present_today'] }}</div>
        <div class="stat-label">Present Today</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $stats['pending_applications'] }}</div>
        <div class="stat-label">Pending Applications</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $stats['total_departments'] }}</div>
        <div class="stat-label">Departments</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px;">
    <!-- Recent Applications -->
    <div class="card">
        <div class="card-header" >
            <h3 class="card-title" >Recent Applications</h3>
        </div>
        <div class="card-body">
            @if($recentApplications->count() > 0)
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentApplications as $application)
                            <tr>
                                <td>{{ $application->employee->username }}</td>
                                <td>
                                    <span class="badge p-2 text-bg-secondary">{{ ucfirst(str_replace('_', ' ', $application->req_type)) }}</span>
                                </td>
                                <td>{{ $application->start_date->format('M d, Y') }}</td>
                                <td>
                                    @if($application->status === 'pending')
                                        <span class="badge p-2 text-bg-warning">Pending</span>
                                    @elseif($application->status === 'approved')
                                        <span class="badge p-2 text-bg-success" >Approved</span>
                                    @else
                                        <span class="badge p-2 text-bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>
                                    @if($application->status === 'pending')
                                        <button class="btn btn-sm btn-success" onclick="updateApplicationStatus({{ $application->id }}, 'approved')" style="margin-bottom: 5px;">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="updateApplicationStatus({{ $application->id }}, 'rejected')" style="margin-bottom: 5px;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">No recent applications</p>
            @endif
        </div>
    </div>

    <!-- Today's Attendance -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Today's Attendance</h3>
        </div>
        <div class="card-body">
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="font-size: 48px; font-weight: 600; color: #c7511f; margin-bottom: 10px;">
                    {{ $todayAttendance['percentage'] }}%
                </div>
                <div style="color: #565959; font-size: 14px;">Attendance Rate</div>
            </div>
            
            <div style="margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span style="font-size: 14px; color: #0f1111;">Present</span>
                    <span style="font-size: 14px; font-weight: 500;">{{ $todayAttendance['present'] }}</span>
                </div>
                <div style="background: #e2e3e5; height: 8px; border-radius: 4px; overflow: hidden;">
                    <div style="background: #067d62; height: 100%; width: {{ $todayAttendance['percentage'] }}%; transition: width 0.3s;"></div>
                </div>
            </div>
            
            <div style="margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span style="font-size: 14px; color: #0f1111;">Absent</span>
                    <span style="font-size: 14px; font-weight: 500;">{{ $todayAttendance['absent'] }}</span>
                </div>
                <div style="background: #e2e3e5; height: 8px; border-radius: 4px; overflow: hidden;">
                    <div style="background: #d13212; height: 100%; width: {{ 100 - $todayAttendance['percentage'] }}%; transition: width 0.3s;"></div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="{{ route('admin.attendance') }}" class="btn btn-primary" 
                style="    
                    position: absolute;
                    
                    bottom: 20px;
                    left: 50%;
                    transform: translateX(-50%);
                ">
                    View Full Attendance
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card" style="margin-top: 30px;">
    <div class="card-header">
        <h3 class="card-title">Quick Actions</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Add Employee
            </a>
            <a href="{{ route('admin.applications') }}?status=pending" class="btn btn-secondary">
                <i class="fas fa-clock"></i> Pending Applications
            </a>
            <a href="{{ route('admin.attendance') }}" class="btn btn-secondary">
                <i class="fas fa-calendar-check"></i> Today's Attendance
            </a>
            <a href="{{ route('admin.reports') }}" class="btn btn-secondary">
                <i class="fas fa-chart-bar"></i> Generate Reports
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateApplicationStatus(applicationId, status) {
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to ${status} this application?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: status === 'approved' ? '#067d62' : '#d13212',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Yes, ${status} it!`
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/applications/${applicationId}/status`,
                method: 'POST',
                data: {
                    status: status,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#ff9900'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Something went wrong. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#d13212'
                    });
                }
            });
        }
    });
}
</script>
@endpush