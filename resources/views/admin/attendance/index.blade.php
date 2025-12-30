@extends('admin.layouts.app')

@section('title', 'Attendance')

@section('content')
<div class="page-header">
    <h1 class="page-title">Attendance Management</h1>
    <p class="page-subtitle">Monitor employee attendance and working hours</p>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.attendance') }}" style="display: grid; grid-template-columns: 200px 200px 120px; gap: 15px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" value="{{ $date }}">
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
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Filter
            </button>
        </form>
    </div>
</div>

<!-- Attendance Summary -->
<div class="stats-grid">
    @php
        $presentCount = $employees->filter(function($emp) { return $emp->timeEntries->where('entry_type', 'punch_in')->count() > 0; })->count();
        $absentCount = $employees->count() - $presentCount;
        $lateCount = 0; // Calculate late employees based on your logic
        $attendanceRate = $employees->count() > 0 ? round(($presentCount / $employees->count()) * 100, 1) : 0;
    @endphp
    
    <div class="stat-card">
        <div class="stat-number">{{ $presentCount }}</div>
        <div class="stat-label">Present</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $absentCount }}</div>
        <div class="stat-label">Absent</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $lateCount }}</div>
        <div class="stat-label">Late Arrivals</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $attendanceRate }}%</div>
        <div class="stat-label">Attendance Rate</div>
    </div>
</div>

<!-- Attendance Table -->
<div class="card">
    <div class="card-header">
        @if($isWeekend)
            <h3 class="card-title">{{ \Carbon\Carbon::parse($date)->format('d-m-Y') }} is {{ $weekendType }}</h3>
        @else
            <h3 class="card-title">Attendance for {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</h3>
        @endif
    </div>
    <div class="card-body" style="padding: 0;">
        @if($employees->count() > 0)
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Punch In</th>
                            <th>Punch Out</th>
                            <th>Working Hours</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                        @php
                            $punchIn = $employee->timeEntries->where('entry_type', 'punch_in')->first();
                            $punchOut = $employee->timeEntries->where('entry_type', 'punch_out')->first();
                            $isPresent = $punchIn !== null;
                            $workingHours = 0;
                            
                            if ($punchIn && $punchOut) {
                                $workingMinutes = $punchIn->entry_time->diffInMinutes($punchOut->entry_time);
                                if ($workingMinutes > 0) {
                                    $workingHours = floor($workingMinutes / 60) . ':' . sprintf('%02d', $workingMinutes % 60);
                                }
                            }
                        @endphp
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <div style="width: 32px; height: 32px; background: #ff9900; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; margin-right: 10px;">
                                        {{ strtoupper(substr($employee->username, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 500;">{{ $employee->username }}</div>
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
                            <td>
                                @if($isPresent)
                                    <span class="badge badge-success">Present</span>
                                @elseif($isWeekend)
                                    <span class="badge badge-info">Week Off</span>
                                @else
                                    <span class="badge badge-danger">Absent</span>
                                @endif
                            </td>
                            <td>
                                @if($punchIn)
                                    <div style="font-weight: 500;">{{ $punchIn->entry_time->format('h:i A') }}</div>
                                    <div style="font-size: 12px; color: #565959;">{{ $punchIn->entry_time->format('M d') }}</div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($punchOut)
                                    <div style="font-weight: 500;">{{ $punchOut->entry_time->format('h:i A') }}</div>
                                    <div style="font-size: 12px; color: #565959;">{{ $punchOut->entry_time->format('M d') }}</div>
                                @else
                                    @if($isPresent)
                                        <span class="badge badge-warning">Still Working</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($workingHours && $workingHours !== '0:00')
                                    <span style="font-weight: 500;">{{ $workingHours }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <button class="btn btn-sm btn-secondary" onclick="viewTimeEntries('{{ $employee->emp_id }}', '{{ $date }}')" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; padding: 40px; color: #565959;">
                <i class="fas fa-calendar-check" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                <h3>No employees found</h3>
                <p>Try adjusting your filter criteria</p>
            </div>
        @endif
    </div>
</div>

<!-- Quick Actions -->
<div class="card" style="margin-top: 30px;">
    <div class="card-header">
        <h3 class="card-title">Quick Actions</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <button class="btn btn-primary" onclick="exportAttendance('{{ $date }}')">
                <i class="fas fa-download"></i> Export Today's Attendance
            </button>
                {{-- <button class="btn btn-secondary" onclick="sendReminders()">
                    <i class="fas fa-bell"></i> Send Attendance Reminders
                </button> --}}
            <a href="{{ route('admin.reports') }}" class="btn btn-secondary">
                <i class="fas fa-chart-bar"></i> Generate Reports
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewTimeEntries(empId, date) {
    // Fetch time entries via AJAX
    fetch(`/admin/time-entries?employee=${empId}&from_date=${date}&to_date=${date}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        let entriesHtml = '';
        if (data.entries && data.entries.length > 0) {
            data.entries.forEach(entry => {
                const time = new Date(entry.entry_time).toLocaleTimeString('en-IN', {
                    timeZone: 'Asia/Kolkata',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
                const type = entry.entry_type.replace('_', ' ').toUpperCase();
                entriesHtml += `<div style="margin: 10px 0; padding: 8px; border-left: 3px solid #ff9900; background: #f8f9fa;">
                    <strong>${type}</strong> - ${time}
                    ${entry.notes ? `<br><small style="color: #666;">${entry.notes}</small>` : ''}
                </div>`;
            });
        } else {
            entriesHtml = '<p style="text-align: center; color: #666; margin: 20px 0;">No time entries found for this date.</p>';
        }
        
        Swal.fire({
            title: `Time Entries - ${empId}`,
            html: `<div style="text-align: left;">
                <h6 style="margin-bottom: 15px; color: #333;">${new Date(date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</h6>
                ${entriesHtml}
            </div>`,
            width: '500px',
            confirmButtonColor: '#ff9900'
        });
    })
    .catch(error => {
        Swal.fire({
            title: 'Error',
            text: 'Failed to load time entries. Please try again.',
            icon: 'error',
            confirmButtonColor: '#ff9900'
        });
    });
}

function exportTimesheet(empId, date) {
    // Implementation for exporting individual timesheet
    window.open(`/admin/attendance/export/${empId}/${date}`, '_blank');
}

function exportAttendance(date) {
    window.open(`/admin/attendance/export/${date}`, '_blank');
}

function sendReminders() {
    Swal.fire({
        title: 'Send Reminders',
        text: 'Send attendance reminders to absent employees?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ff9900',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, send reminders!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implementation for sending reminders
            Swal.fire({
                title: 'Reminders Sent!',
                text: 'Attendance reminders have been sent to absent employees.',
                icon: 'success',
                confirmButtonColor: '#ff9900'
            });
        }
    });
}
</script>
@endpush