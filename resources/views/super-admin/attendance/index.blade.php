@extends('super-admin.layouts.app')

@section('title', 'Attendance')

@section('content')
<div class="page-header">
    <h1 class="page-title">Attendance Management</h1>
    <p class="page-subtitle">Monitor employee attendance and working hours</p>
</div>

<!-- Attendance Summary -->
<div class="stats-grid">
    @php
        $presentCount = $employees->filter(function($emp) { return $emp->timeEntries->where('entry_type', 'punch_in')->count() > 0; })->count();
        $absentCount = $employees->count() - $presentCount;
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
        <div class="stat-number">{{ $attendanceRate }}%</div>
        <div class="stat-label">Attendance Rate</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $employees->count() }}</div>
        <div class="stat-label">Total Employees</div>
    </div>
</div>

<!-- Attendance Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Attendance for {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</h3>
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
                                    <div style="width: 32px; height: 32px; background: #ff6b35; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; margin-right: 10px;">
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
                <p>No active employees in the system</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewTimeEntries(empId, date) {
    Swal.fire({
        title: `Time Entries - ${empId}`,
        text: 'Time entry details for the selected employee',
        icon: 'info'
    });
}
</script>
@endpush