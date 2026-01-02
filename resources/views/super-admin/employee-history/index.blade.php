@extends('super-admin.layouts.app')

@section('title', 'Employee History')

@section('content')
<div class="page-header">
    <h1 class="page-title">Employee History</h1>
    <p class="page-subtitle">View detailed time tracking history for all employees</p>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Search & Filter</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('super-admin.employee-history') }}">
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 15px; align-items: end;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">Search Employee</label>
                    <input type="text" name="search" class="form-control" placeholder="Name or Employee ID" value="{{ request('search') }}">
                </div>
                
                <div class="form-group" style="margin: 0;">
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
                
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">Period</label>
                    <select name="period" class="form-control" onchange="toggleCustomDates()">
                        <option value="current" {{ request('period', 'current') == 'current' ? 'selected' : '' }}>Current Month</option>
                        <option value="last" {{ request('period') == 'last' ? 'selected' : '' }}>Last Month</option>
                        <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin: 0;" id="custom-dates" style="display: {{ request('period') == 'custom' ? 'block' : 'none' }};">
                    <label class="form-label">Date Range</label>
                    <div style="display: flex; gap: 5px;">
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" style="font-size: 12px;">
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" style="font-size: 12px;">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Employee History -->
@if($employees->count() > 0)
    @foreach($employees as $employee)
    <div class="card">
        <div class="card-header" style="background: linear-gradient(135deg, #ff6b35, #ff9900); color: white;">
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <div>
                    <h3 style="margin: 0; color: white;">{{ $employee->username }}</h3>
                    <p style="margin: 5px 0 0 0; opacity: 0.9;">{{ $employee->emp_id }} • {{ $employee->department->name ?? 'No Department' }}</p>
                    <p style="margin: 5px 0 0 0; opacity: 0.9;">• {{ $admin_name[$employee->referrance]->username ?? 'No Admin' }} •</p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 24px; font-weight: 600;">{{ $employee->timeEntries->groupBy(function($entry) { return $entry->entry_time->format('Y-m-d'); })->count() }}</div>
                    <div style="font-size: 12px; opacity: 0.9;">Days with entries</div>
                </div>
            </div>
        </div>
        
        <div class="card-body" style="padding: 0;">
            @php
                $groupedEntries = $employee->timeEntries->groupBy(function($entry) {
                    return $entry->entry_time->format('Y-m-d');
                })->sortKeysDesc();
            @endphp
            
            @if($groupedEntries->count() > 0)
                <div class="table-responsive">
                    <table class="table" style="margin: 0;">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th>Date</th>
                                <th>Punch In</th>
                                <th>Lunch Start</th>
                                <th>Lunch End</th>
                                <th>Punch Out</th>
                                <th>Working Hours</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupedEntries as $date => $dayEntries)
                                @php
                                    $punchIn = $dayEntries->where('entry_type', 'punch_in')->first();
                                    $lunchStart = $dayEntries->where('entry_type', 'lunch_start')->first();
                                    $lunchEnd = $dayEntries->where('entry_type', 'lunch_end')->first();
                                    $punchOut = $dayEntries->where('entry_type', 'punch_out')->first();
                                    $holiday = $dayEntries->where('entry_type', 'holiday')->first();
                                    $sickLeave = $dayEntries->where('entry_type', 'sick_leave')->first();
                                    $casualLeave = $dayEntries->where('entry_type', 'casual_leave')->first();
                                    $weekOff = $dayEntries->where('entry_type', 'week_off')->first();
                                    
                                    $workingHours = 0;
                                    $status = 'Absent';
                                    $statusClass = 'danger';
                                    
                                    if ($holiday) {
                                        $status = 'Holiday';
                                        $statusClass = 'info';
                                    } elseif ($sickLeave) {
                                        $status = 'Sick Leave';
                                        $statusClass = 'warning';
                                    } elseif ($casualLeave) {
                                        $status = 'Casual Leave';
                                        $statusClass = 'warning';
                                    } elseif ($weekOff) {
                                        $status = 'Week Off';
                                        $statusClass = 'secondary';
                                    } elseif ($punchIn) {
                                        $status = 'Present';
                                        $statusClass = 'success';
                                        
                                        if ($punchOut) {
                                            $totalMinutes = $punchIn->entry_time->diffInMinutes($punchOut->entry_time);
                                            if ($lunchStart && $lunchEnd) {
                                                $lunchMinutes = $lunchStart->entry_time->diffInMinutes($lunchEnd->entry_time);
                                                $totalMinutes -= $lunchMinutes;
                                            }
                                            $workingHours = round($totalMinutes / 60, 2);
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td style="font-weight: 600;">{{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</td>
                                    <td>{{ $punchIn ? $punchIn->entry_time->format('H:i') : '-' }}</td>
                                    <td>{{ $lunchStart ? $lunchStart->entry_time->format('H:i') : '-' }}</td>
                                    <td>{{ $lunchEnd ? $lunchEnd->entry_time->format('H:i') : '-' }}</td>
                                    <td>{{ $punchOut ? $punchOut->entry_time->format('H:i') : '-' }}</td>
                                    <td>{{ $workingHours > 0 ? $workingHours . 'h' : '-' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $statusClass }}">{{ $status }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                {{-- @if($groupedEntries->count() > 10)
                    <div style="padding: 15px; text-align: center; background: #f8f9fa; border-top: 1px solid #e9ecef;">
                        <small class="text-muted">Showing latest 10 days. Total: {{ $groupedEntries->count() }} days with entries.</small>
                    </div>
                @endif --}}
            @else
                <div style="text-align: center; padding: 40px; color: #6b7280;">
                    <i class="fas fa-clock" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                    <h4>No time entries found</h4>
                    <p>No time tracking data available for the selected period.</p>
                </div>
            @endif
        </div>
    </div>
    @endforeach
    
    <!-- Pagination -->
    <div style="display: flex; justify-content: center; margin-top: 30px;">
        {{ $employees->appends(request()->query())->links() }}
    </div>
@else
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 60px;">
            <i class="fas fa-users" style="font-size: 64px; color: #e5e7eb; margin-bottom: 20px;"></i>
            <h3>No employees found</h3>
            <p style="color: #6b7280;">No employees match your search criteria.</p>
        </div>
    </div>
@endif

<script>
function toggleCustomDates() {
    const period = document.querySelector('select[name="period"]').value;
    const customDates = document.getElementById('custom-dates');
    customDates.style.display = period === 'custom' ? 'block' : 'none';
}
</script>

@endsection