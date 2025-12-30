@extends('admin.layouts.app')

@section('title', 'Employee History')

@section('content')
<div class="page-header">
    <h1 class="page-title">Employee Time Analysis</h1>
    <p class="page-subtitle">Detailed time tracking and attendance history</p>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-body">
        <form method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                <label class="form-label">Time Period</label>
                <select name="period" class="form-control" onchange="toggleCustomDates(this.value)">
                    <option value="current" {{ request('period', 'current') === 'current' ? 'selected' : '' }}>Current Month</option>
                    <option value="last" {{ request('period') === 'last' ? 'selected' : '' }}>Last Month</option>
                    <option value="custom" {{ request('period') === 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0; min-width: 150px;" id="start_date_group" {{ request('period') != 'custom' ? 'style=display:none;' : '' }}>
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
            </div>
            <div class="form-group" style="margin-bottom: 0; min-width: 150px;" id="end_date_group" {{ request('period') != 'custom' ? 'style=display:none;' : '' }}>
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date', now()->endOfMonth()->format('Y-m-d')) }}">
            </div>
            <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                <label class="form-label">Department</label>
                <select name="department" class="form-control">
                    <option value="all">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->name }}" {{ request('department') === $department->name ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Employee name or ID" value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-secondary" style="height: fit-content;">
                <i class="fas fa-search"></i> Filter
            </button>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="stats-grid">
    @php
        $totalEmployees = $employees->total();
        $presentDays = 0;
        $absentDays = 0;
        $lateArrivals = 0;
        
        foreach($employees as $employee) {
            foreach($employee->timeEntries as $entry) {
                if($entry->entry_type === 'punch_in') $presentDays++;
                if($entry->entry_type === 'punch_in' && $entry->entry_time->format('H:i:s') > '09:00:00') $lateArrivals++;
            }
        }
    @endphp
    
    <div class="stat-card">
        <div class="stat-number">{{ $totalEmployees }}</div>
        <div class="stat-label">Total Employees</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $presentDays }}</div>
        <div class="stat-label">Present Days</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $lateArrivals }}</div>
        <div class="stat-label">Late Arrivals</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">8.5h</div>
        <div class="stat-label">Avg Working Hours</div>
    </div>
</div>

@if($employees->count() > 0)
    @foreach($employees as $employee)
    <!-- Employee Header -->
    <div class="card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="width: 60px; height: 60px; background: #ff9900; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold;">
                    {{ strtoupper(substr($employee->username ?? 'N', 0, 1)) }}
                </div>
                <div>
                    <h3 style="margin: 0; color: #0f1111;">{{ $employee->username ?? 'Unknown' }}</h3>
                    <p style="margin: 5px 0 0; color: #565959;">
                        ID: {{ $employee->emp_id }} | Department: {{ $employee->department ?? 'N/A' }} | Position: {{ $employee->position ?? 'N/A' }}
                    </p>
                </div>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Punch In</th>
                            <th>Punch Out</th>
                            <th>Lunch Duration</th>
                            <th>Net Hours</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $timeEntriesByDate = $employee->timeEntries->groupBy(function($entry) {
                                return $entry->entry_time->format('Y-m-d');
                            });
                        @endphp
                        
                        @forelse($timeEntriesByDate as $date => $entries)
                        @php
                            $punchIn = $entries->where('entry_type', 'punch_in')->first();
                            $punchOut = $entries->where('entry_type', 'punch_out')->first();
                            $lunchStart = $entries->where('entry_type', 'lunch_start')->first();
                            $lunchEnd = $entries->where('entry_type', 'lunch_end')->first();
                            
                            $workingMinutes = 0;
                            $lunchMinutes = 0;
                            
                            if ($punchIn && $punchOut) {
                                $workingMinutes = $punchIn->entry_time->diffInMinutes($punchOut->entry_time);
                            }
                            
                            if ($lunchStart && $lunchEnd) {
                                $lunchMinutes = $lunchStart->entry_time->diffInMinutes($lunchEnd->entry_time);
                                $workingMinutes -= $lunchMinutes;

                               
                            }
                            
                            $netHours = $workingMinutes > 0 ? floor($workingMinutes / 60) . ':' . sprintf('%02d', $workingMinutes % 60) : '-';
                            $lunchDuration = $lunchMinutes > 0 ? floor($lunchMinutes / 60) . ':' . sprintf('%02d', $lunchMinutes % 60). ':' . (($lunchMinutes * 60) % 60) : '-';
                            
                            $status = 'Present';
                            $statusClass = 'success';
                            
                            if (!$punchIn) {
                                // Check if it's a weekend
                                $dateCarbon = \Carbon\Carbon::parse($date);
                                if ($dateCarbon->isSunday() || ($dateCarbon->isSaturday() && in_array(ceil($dateCarbon->day / 7), [2, 4]))) {
                                    $status = 'Week Off';
                                    $statusClass = 'info';
                                } else {
                                    $status = 'Absent';
                                    $statusClass = 'danger';
                                }
                            } elseif ($punchIn->entry_time->format('H:i') > '09:15') {
                                $status = 'Late';
                                $statusClass = 'warning';
                            }
                        @endphp
                        <tr>
                            <td style="color: #495af2; cursor: pointer;" onclick="viewDayDetails('{{ $employee->emp_id }}', '{{ $date }}')">
                                {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                            </td>
                            <td>{{ $punchIn ? $punchIn->entry_time->format('h:i A') : '-' }}</td>
                            <td>{{ $punchOut ? $punchOut->entry_time->format('h:i A') : '-' }}</td>
                            <td>{{ $lunchDuration }}Min.</td>
                            <td>{{ $netHours }}</td>
                            <td>
                                <span class="badge badge-{{ $statusClass }}">{{ $status }}</span>
                                @if($lunchMinutes > 60)
                                    <span class="badge badge-warning">Long Lunch</span>
                                @endif
                                @if($workingMinutes < 480 && $workingMinutes > 0)
                                    <span class="badge badge-warning">Short Hours</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #565959;">No time entries found for selected period</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach
    
    <!-- Pagination -->
    @if($employees->hasPages())
        <div style="margin-top: 20px;">
            {{ $employees->links() }}
        </div>
    @endif
@else
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 40px; color: #565959;">
            <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
            <h3>No employees found</h3>
            <p>Try adjusting your filter criteria</p>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
function viewDayDetails(empId, date) {
    // Fetch detailed time entries for the specific day
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
                // Time is already in IST from backend
                const entryDate = new Date(entry.entry_time);
                const time = entryDate.toISOString().split('T')[1].split('.')[0];
                const type = entry.entry_type.replace('_', ' ').toUpperCase();
                const typeColors = {
                    'PUNCH IN': '#10b981',
                    'PUNCH OUT': '#ef4444',
                    'LUNCH START': '#f59e0b',
                    'LUNCH END': '#f59e0b'
                };
                const color = typeColors[type] || '#6b7280';
                
                entriesHtml += `<div style="margin: 10px 0; padding: 12px; border-left: 4px solid ${color}; background: #f8f9fa; border-radius: 4px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <strong style="color: ${color};">${type}</strong>
                        <span style="font-weight: 600;">${time}</span>
                    </div>
                    ${entry.notes ? `<div style="margin-top: 5px; font-size: 12px; color: #666;">${entry.notes}</div>` : ''}
                </div>`;
            });
        } else {
            entriesHtml = '<p style="text-align: center; color: #666; margin: 20px 0;">No time entries found for this date.</p>';
        }
        
        Swal.fire({
            title: `Day Details - ${empId}`,
            html: `<div style="text-align: left;">
                <h6 style="margin-bottom: 15px; color: #333; text-align: center;">${new Date(date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</h6>
                ${entriesHtml}
            </div>`,
            width: '500px',
            confirmButtonColor: '#6366f1'
        });
    })
    .catch(error => {
        Swal.fire({
            title: 'Error',
            text: 'Failed to load day details. Please try again.',
            icon: 'error',
            confirmButtonColor: '#6366f1'
        });
    });
}

function toggleCustomDates(value) {
    const startDateGroup = document.getElementById('start_date_group');
    const endDateGroup = document.getElementById('end_date_group');
    
    if (value === 'custom') {
        startDateGroup.style.display = 'block';
        endDateGroup.style.display = 'block';
    } else {
        startDateGroup.style.display = 'none';
        endDateGroup.style.display = 'none';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const periodSelect = document.querySelector('select[name="period"]');
    if (periodSelect) {
        toggleCustomDates(periodSelect.value);
    }
});
</script>
@endpush