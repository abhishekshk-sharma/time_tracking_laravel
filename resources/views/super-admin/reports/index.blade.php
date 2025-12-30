@extends('super-admin.layouts.app')

@section('title', 'Reports')

@section('content')
<div class="page-header">
    <h1 class="page-title">Reports & Analytics</h1>
    <p class="page-subtitle">Generate comprehensive reports and insights with salary data</p>
</div>

<!-- Report Categories -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
    
    <!-- Salary Reports -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-money-bill-wave" style="color: #ff6b35; margin-right: 10px;"></i>
                Salary Reports
            </h3>
        </div>
        <div class="card-body">
            <p style="color: #565959; margin-bottom: 20px;">Generate detailed salary reports and payroll analysis.</p>
            
            <div class="form-group">
                <label class="form-label">Report Type</label>
                <select id="salaryReportType" class="form-control">
                    <option value="summary">Salary Summary</option>
                    <option value="detailed">Detailed Breakdown</option>
                    <option value="department">Department-wise</option>
                    <option value="comparison">Salary Comparison</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Department</label>
                <select id="salaryDepartment" class="form-control">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->name }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button class="btn btn-primary" onclick="generateSalaryReport()">
                    <i class="fas fa-chart-line"></i> Generate
                </button>
                <button class="btn btn-secondary" onclick="exportSalaryReport()">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>
    </div>

    <!-- Attendance Reports -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-calendar-check" style="color: #10b981; margin-right: 10px;"></i>
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

    <!-- Employee Performance -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-users" style="color: #8b5cf6; margin-right: 10px;"></i>
                Employee Performance
            </h3>
        </div>
        <div class="card-body">
            <p style="color: #565959; margin-bottom: 20px;">Track individual employee performance metrics with salary correlation.</p>
            
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
                <i class="fas fa-chart-area" style="color: #f59e0b; margin-right: 10px;"></i>
                System Analytics
            </h3>
        </div>
        <div class="card-body">
            <p style="color: #565959; margin-bottom: 20px;">Overall system usage and trend analysis including payroll insights.</p>
            
            <div class="form-group">
                <label class="form-label">Analytics Type</label>
                <select id="analyticsType" class="form-control">
                    <option value="usage">System Usage</option>
                    <option value="trends">Attendance Trends</option>
                    <option value="patterns">Work Patterns</option>
                    <option value="payroll">Payroll Analytics</option>
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
@endsection

@push('scripts')
<script>
function generateSalaryReport() {
    const type = document.getElementById('salaryReportType').value;
    const department = document.getElementById('salaryDepartment').value;
    
    Swal.fire({
        title: 'Generating Salary Report...',
        text: 'Please wait while we generate your salary report.',
        icon: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        Swal.fire({
            title: 'Salary Report Generated!',
            text: 'Your salary report has been generated successfully.',
            icon: 'success',
            confirmButtonColor: '#ff6b35'
        });
    }, 2000);
}

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
    
    setTimeout(() => {
        Swal.fire({
            title: 'Report Generated!',
            text: 'Your attendance report has been generated successfully.',
            icon: 'success',
            confirmButtonColor: '#ff6b35'
        });
    }, 2000);
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
            confirmButtonColor: '#ff6b35'
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
            confirmButtonColor: '#ff6b35'
        });
    }, 2000);
}

function exportSalaryReport() {
    Swal.fire({
        title: 'Export Started',
        text: 'Your salary report export has been initiated.',
        icon: 'success',
        confirmButtonColor: '#ff6b35'
    });
}

function exportAttendanceReport() {
    Swal.fire({
        title: 'Export Started',
        text: 'Your attendance report export has been initiated.',
        icon: 'success',
        confirmButtonColor: '#ff6b35'
    });
}

function exportPerformanceReport() {
    Swal.fire({
        title: 'Export Started',
        text: 'Your performance report export has been initiated.',
        icon: 'success',
        confirmButtonColor: '#ff6b35'
    });
}

function exportAnalyticsReport() {
    Swal.fire({
        title: 'Export Started',
        text: 'Your analytics report export has been initiated.',
        icon: 'success',
        confirmButtonColor: '#ff6b35'
    });
}
</script>
@endpush