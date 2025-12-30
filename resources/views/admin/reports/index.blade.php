@extends('admin.layouts.app')

@section('title', 'Reports')

@section('content')
<div class="page-header">
    <h1 class="page-title">Reports & Analytics</h1>
    <p class="page-subtitle">Generate comprehensive reports and insights</p>
</div>

<!-- Report Categories -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
    
    <!-- Attendance Reports -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-calendar-check" style="color: #ff9900; margin-right: 10px;"></i>
                Attendance Reports
            </h3>
        </div>
        <div class="card-body">
            <p style="color: #565959; margin-bottom: 20px;">Generate detailed attendance reports for employees and departments.</p>
            
            <div class="form-group">
                <label class="form-label">Report Type</label>
                <select id="attendanceReportType" class="form-control">
                    <option value="daily">Daily Attendance</option>
                    <option value="weekly">Weekly Summary</option>
                    <option value="monthly">Monthly Report</option>
                    <option value="custom">Custom Date Range</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Department</label>
                <select id="attendanceDepartment" class="form-control">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->name }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button class="btn btn-primary" onclick="generateAttendanceReport()">
                    <i class="fas fa-chart-line"></i> Generate
                </button>
                <button class="btn btn-secondary" onclick="exportAttendanceReport()">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>
    </div>

    <!-- Leave Reports -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-alt" style="color: #067d62; margin-right: 10px;"></i>
                Leave Reports
            </h3>
        </div>
        <div class="card-body">
            <p style="color: #565959; margin-bottom: 20px;">Analyze leave patterns and application statistics.</p>
            
            <div class="form-group">
                <label class="form-label">Report Period</label>
                <select id="leaveReportPeriod" class="form-control">
                    <option value="current_month">Current Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="quarter">This Quarter</option>
                    <option value="year">This Year</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Leave Type</label>
                <select id="leaveType" class="form-control">
                    <option value="">All Types</option>
                    <option value="casual_leave">Casual Leave</option>
                    <option value="sick_leave">Sick Leave</option>
                    <option value="half_day">Half Day</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button class="btn btn-primary" onclick="generateLeaveReport()">
                    <i class="fas fa-chart-pie"></i> Generate
                </button>
                <button class="btn btn-secondary" onclick="exportLeaveReport()">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>
    </div>

    <!-- Employee Performance -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-users" style="color: #c7511f; margin-right: 10px;"></i>
                Employee Performance
            </h3>
        </div>
        <div class="card-body">
            <p style="color: #565959; margin-bottom: 20px;">Track individual employee performance metrics.</p>
            
            <div class="form-group">
                <label class="form-label">Employee</label>
                <select id="performanceEmployee" class="form-control">
                    <option value="">All Employees</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->emp_id }}">{{ $employee->username }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Metrics</label>
                <select id="performanceMetrics" class="form-control">
                    <option value="attendance">Attendance Rate</option>
                    <option value="punctuality">Punctuality Score</option>
                    <option value="overtime">Overtime Hours</option>
                    <option value="comprehensive">Comprehensive Report</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button class="btn btn-primary" onclick="generatePerformanceReport()">
                    <i class="fas fa-chart-bar"></i> Generate
                </button>
                <button class="btn btn-secondary" onclick="exportPerformanceReport()">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>
    </div>

    <!-- System Analytics -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-analytics" style="color: #7c3aed; margin-right: 10px;"></i>
                System Analytics
            </h3>
        </div>
        <div class="card-body">
            <p style="color: #565959; margin-bottom: 20px;">Overall system usage and trend analysis.</p>
            
            <div class="form-group">
                <label class="form-label">Analytics Type</label>
                <select id="analyticsType" class="form-control">
                    <option value="usage">System Usage</option>
                    <option value="trends">Attendance Trends</option>
                    <option value="patterns">Work Patterns</option>
                    <option value="insights">Business Insights</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Time Range</label>
                <select id="analyticsRange" class="form-control">
                    <option value="7days">Last 7 Days</option>
                    <option value="30days">Last 30 Days</option>
                    <option value="90days">Last 90 Days</option>
                    <option value="1year">Last Year</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button class="btn btn-primary" onclick="generateAnalyticsReport()">
                    <i class="fas fa-chart-area"></i> Generate
                </button>
                <button class="btn btn-secondary" onclick="exportAnalyticsReport()">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Recent Reports -->
<div class="card" style="margin-top: 30px;">
    <div class="card-header">
        <h3 class="card-title">Recent Reports</h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Report Name</th>
                        <th>Type</th>
                        <th>Generated By</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentReports as $report)
                    <tr>
                        <td>{{ $report['name'] }}</td>
                        <td><span class="badge badge-secondary">{{ $report['type'] }}</span></td>
                        <td>{{ $report['generated_by'] }}</td>
                        <td>{{ $report['date'] }}</td>
                        <td><span class="badge badge-success">{{ $report['status'] }}</span></td>
                        <td>
                            <button class="btn btn-sm btn-secondary" title="Download">
                                <i class="fas fa-download"></i>
                            </button>
                            <button class="btn btn-sm btn-secondary" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function generateAttendanceReport() {
    const type = document.getElementById('attendanceReportType').value;
    const department = document.getElementById('attendanceDepartment').value;
    
    Swal.fire({
        title: 'Generating Report...',
        text: 'Please wait while we generate your attendance report.',
        icon: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '/admin/reports/generate',
        method: 'POST',
        data: {
            report_type: 'attendance',
            type: type,
            department: department,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            Swal.fire({
                title: 'Report Generated!',
                html: `<div style="text-align: left;">
                    <p><strong>Total Employees:</strong> ${response.summary.total_employees}</p>
                    <p><strong>Average Attendance Rate:</strong> ${response.summary.avg_attendance_rate}%</p>
                    <p><strong>Total Hours:</strong> ${response.summary.total_hours}</p>
                </div>`,
                icon: 'success',
                confirmButtonColor: '#3b82f6'
            });
        },
        error: function() {
            Swal.fire({
                title: 'Error!',
                text: 'Failed to generate report. Please try again.',
                icon: 'error',
                confirmButtonColor: '#ef4444'
            });
        }
    });
}

function generateLeaveReport() {
    const period = document.getElementById('leaveReportPeriod').value;
    const type = document.getElementById('leaveType').value;
    
    Swal.fire({
        title: 'Generating Report...',
        text: 'Please wait while we generate your leave report.',
        icon: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '/admin/reports/generate',
        method: 'POST',
        data: {
            report_type: 'leave',
            period: period,
            leave_type: type,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            Swal.fire({
                title: 'Leave Report Generated!',
                html: `<div style="text-align: left;">
                    <p><strong>Total Applications:</strong> ${response.data.total_applications}</p>
                    <p><strong>Approved:</strong> ${response.data.approved}</p>
                    <p><strong>Rejected:</strong> ${response.data.rejected}</p>
                    <p><strong>Pending:</strong> ${response.data.pending}</p>
                </div>`,
                icon: 'success',
                confirmButtonColor: '#3b82f6'
            });
        },
        error: function() {
            Swal.fire({
                title: 'Error!',
                text: 'Failed to generate report. Please try again.',
                icon: 'error',
                confirmButtonColor: '#ef4444'
            });
        }
    });
}

function generatePerformanceReport() {
    const employee = document.getElementById('performanceEmployee').value;
    const metrics = document.getElementById('performanceMetrics').value;
    
    Swal.fire({
        title: 'Generating Report...',
        text: 'Please wait while we generate your performance report.',
        icon: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        Swal.fire({
            title: 'Report Generated!',
            text: 'Your performance report has been generated successfully.',
            icon: 'success',
            confirmButtonColor: '#ff9900'
        });
    }, 2000);
}

function generateAnalyticsReport() {
    const type = document.getElementById('analyticsType').value;
    const range = document.getElementById('analyticsRange').value;
    
    Swal.fire({
        title: 'Generating Analytics...',
        text: 'Please wait while we generate your analytics report.',
        icon: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        Swal.fire({
            title: 'Analytics Generated!',
            text: 'Your analytics report has been generated successfully.',
            icon: 'success',
            confirmButtonColor: '#ff9900'
        });
    }, 2000);
}

function exportAttendanceReport() {
    const type = document.getElementById('attendanceReportType').value;
    const department = document.getElementById('attendanceDepartment').value;
    
    const params = new URLSearchParams({
        type: type,
        department: department
    });
    
    window.open(`/admin/reports/export/attendance?${params.toString()}`, '_blank');
}

function exportLeaveReport() {
    const period = document.getElementById('leaveReportPeriod').value;
    const leaveType = document.getElementById('leaveType').value;
    
    const params = new URLSearchParams({
        period: period,
        leave_type: leaveType
    });
    
    window.open(`/admin/reports/export/leave?${params.toString()}`, '_blank');
}

function exportPerformanceReport() {
    const employee = document.getElementById('performanceEmployee').value;
    const metrics = document.getElementById('performanceMetrics').value;
    
    const params = new URLSearchParams({
        employee: employee,
        metrics: metrics
    });
    
    window.open(`/admin/reports/export/performance?${params.toString()}`, '_blank');
}

function exportAnalyticsReport() {
    const type = document.getElementById('analyticsType').value;
    const range = document.getElementById('analyticsRange').value;
    
    const params = new URLSearchParams({
        type: type,
        range: range
    });
    
    window.open(`/admin/reports/export/analytics?${params.toString()}`, '_blank');
}
</script>
@endpush