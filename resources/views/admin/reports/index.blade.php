@extends('admin.layouts.app')

@section('title', 'Reports')

@section('content')
<div class="page-header">
    <h1 class="page-title">Attendance Reports</h1>
    <p class="page-subtitle">Generate attendance reports for your assigned employees</p>
</div>

<!-- Report Categories -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px;">
    
    <!-- Attendance Reports -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-calendar-check" style="color: #10b981; margin-right: 10px;"></i>
                Attendance Reports
            </h3>
        </div>
        <div class="card-body">
            <p style="color: #565959; margin-bottom: 20px;">Generate detailed attendance reports for your assigned employees.</p>
            
            <form action="{{ route('admin.reports.generate') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-control" required>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-control" required>
                        @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Generate Attendance Report
                </button>
            </form>
        </div>
    </div>

    @if(isset($salaryReports) && $salaryReports->count() > 0)
    <!-- Generated Salary Reports -->
    <div class="card mt-4" style="grid-column: 1 / -1;">
        <div class="card-header">
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <h3 class="card-title">
                    <i class="fas fa-table" style="color: #10b981; margin-right: 10px;"></i>
                    Generated Salary Reports (Allotted Employees)
                </h3>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <label style="font-size: 12px; color: #666; margin: 0;">Per Page:</label>
                    <select id="per-page-select" style="padding: 4px 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Search Filters -->
        <div class="card-body" style="border-bottom: 1px solid #e9ecef; padding-bottom: 15px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 15px; align-items: end;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-size: 12px; margin-bottom: 5px;">Search Employee</label>
                    <input type="text" id="employee-search" placeholder="Employee ID or Name" 
                           style="padding: 6px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px; width: 100%;" 
                           value="{{ request('search') }}">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-size: 12px; margin-bottom: 5px;">Month</label>
                    <select id="month-filter" style="padding: 6px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px; width: 100%;">
                        <option value="">All Months</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-size: 12px; margin-bottom: 5px;">Year</label>
                    <select id="year-filter" style="padding: 6px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px; width: 100%;">
                        <option value="">All Years</option>
                        @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button onclick="applyFilters()" class="btn btn-primary" style="padding: 6px 15px; font-size: 12px;">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <button onclick="clearFilters()" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Month/Year</th>
                            <th>Net Salary</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salaryReports as $report)
                            <tr>
                                <td>
                                    <strong>{{ $report->emp_name }}</strong><br>
                                    <small class="text-muted">{{ $report->emp_id }}</small>
                                </td>
                                <td>{{ $report->department }}</td>
                                <td>{{ date('F Y', mktime(0, 0, 0, $report->month, 1, $report->year)) }}</td>
                                <td>
                                    <span class="{{ $report->net_salary < 0 ? 'text-danger' : 'text-success' }}">
                                        ₹{{ number_format($report->net_salary, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $report->status == 'generated' ? 'warning' : ($report->status == 'reviewed' ? 'info' : 'success') }}">
                                        {{ ucfirst($report->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                        <a href="{{ route('admin.salary-reports.view', $report->id) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> 
                                        </a>
                                        <a href="{{ route('admin.salary-reports.edit', $report->id) }}" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> 
                                        </a>
                                        <a href="{{ route('admin.salary-reports.download', $report->id) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-download"></i> 
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{ $salaryReports->appends(request()->query())->links() }}
        </div>
    </div>
    @endif


</div>
@endsection

@push('scripts')
<script>
// Salary Reports Search Functions
function applyFilters() {
    const search = document.getElementById('employee-search').value;
    const month = document.getElementById('month-filter').value;
    const year = document.getElementById('year-filter').value;
    const perPage = document.getElementById('per-page-select').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (month) params.append('month', month);
    if (year) params.append('year', year);
    if (perPage) params.append('per_page', perPage);
    
    window.location.href = '{{ route("admin.reports") }}?' + params.toString();
}

function clearFilters() {
    document.getElementById('employee-search').value = '';
    document.getElementById('month-filter').value = '';
    document.getElementById('year-filter').value = '';
    document.getElementById('per-page-select').value = '10';
    window.location.href = '{{ route("admin.reports") }}';
}

// Per page change handler
document.getElementById('per-page-select')?.addEventListener('change', function() {
    applyFilters();
});

// Enter key handler for search input
document.getElementById('employee-search')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});

// Salary Slip Preview Function
function previewSalarySlip(reportId) {
    fetch(`/admin/salary-reports/${reportId}/preview`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                Swal.fire('Error', data.error, 'error');
                return;
            }
            
            const monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June',
                              'July', 'August', 'September', 'October', 'November', 'December'];
            
            Swal.fire({
                title: `Salary Slip - ${monthNames[data.month]} ${data.year}`,
                html: `
                    <div style="text-align: left; font-size: 14px;">
                        <div style="margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                            <strong>Employee:</strong> ${data.emp_name} (${data.emp_id})<br>
                            <strong>Department:</strong> ${data.department}<br>
                            <strong>Designation:</strong> ${data.designation}
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                            <div>
                                <h6 style="margin-bottom: 10px; color: #495057;">Attendance Details</h6>
                                <div style="font-size: 13px;">
                                    Present Days: <strong>${data.present_days}</strong><br>
                                    Absent Days: <strong>${data.absent_days}</strong><br>
                                    Half Days: <strong>${data.half_days}</strong><br>
                                    Sick Leave: <strong>${data.sick_leave}</strong><br>
                                    Casual Leave: <strong>${data.casual_leave}</strong><br>
                                    Holidays: <strong>${data.holidays}</strong><br>
                                    Payable Days: <strong>${data.payable_days}</strong>
                                </div>
                            </div>
                            
                            <div>
                                <h6 style="margin-bottom: 10px; color: #495057;">Salary Breakdown</h6>
                                <div style="font-size: 13px;">
                                    Basic Salary: <strong>₹${parseFloat(data.basic_salary).toLocaleString()}</strong><br>
                                    HRA: <strong>₹${parseFloat(data.hra).toLocaleString()}</strong><br>
                                    Conveyance: <strong>₹${parseFloat(data.conveyance_allowance).toLocaleString()}</strong><br>
                                    PF: <strong>₹${parseFloat(data.pf).toLocaleString()}</strong><br>
                                    PT: <strong>₹${parseFloat(data.pt).toLocaleString()}</strong>
                                </div>
                            </div>
                        </div>
                        
                        <div style="padding: 10px; background: ${data.net_salary >= 0 ? '#d4edda' : '#f8d7da'}; border-radius: 5px; text-align: center;">
                            <strong style="font-size: 16px; color: ${data.net_salary >= 0 ? '#155724' : '#721c24'};">Net Salary: ₹${parseFloat(data.net_salary).toLocaleString()}</strong>
                        </div>
                    </div>
                `,
                width: '600px',
                showCloseButton: true,
                showConfirmButton: false,
                customClass: {
                    popup: 'swal-wide'
                }
            });
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Failed to load salary slip preview', 'error');
        });
}
</script>
@endpush

