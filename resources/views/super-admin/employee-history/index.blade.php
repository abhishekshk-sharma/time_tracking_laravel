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
    @php
        $hasImages = $employee->entryImages->where('entry_time', '>=', request('start_date', now()->startOfMonth()->format('Y-m-d')) . ' 00:00:00')
                                          ->where('entry_time', '<=', request('end_date', now()->endOfMonth()->format('Y-m-d')) . ' 23:59:59')
                                          ->count() > 0;
    @endphp
    <div class="card">
        <div class="card-header" style="background: linear-gradient(135deg, #eeedfa, #f8eaf0); color: black;">
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <div>
                    <h3 style="margin: 0; color: black;">{{ $employee->username }}</h3>
                    <p style="margin: 5px 0 0 0; opacity: 0.9;">{{ $employee->emp_id }} • {{ $employee->department->name ?? 'No Department' }}</p>
                    <p style="margin: 5px 0 0 0; opacity: 0.9;">• {{ $admin_name[$employee->referrance]->username ?? 'No Admin' }} •</p>
                </div>
                @if($hasImages)
                    <i class="fas fa-camera" style="color: green; margin-left: 8px; margin-right: 15px;" title="Images captured"></i>
                @endif
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
                                    $punchIns = $dayEntries->where('entry_type', 'punch_in')->sortBy('entry_time');
                                    $punchOuts = $dayEntries->where('entry_type', 'punch_out')->sortBy('entry_time');
                                    $lunchStart = $dayEntries->where('entry_type', 'lunch_start')->first();
                                    $lunchEnd = $dayEntries->where('entry_type', 'lunch_end')->first();
                                    $holiday = $dayEntries->where('entry_type', 'holiday')->first();
                                    $sickLeave = $dayEntries->where('entry_type', 'sick_leave')->first();
                                    $casualLeave = $dayEntries->where('entry_type', 'casual_leave')->first();
                                    $weekOff = $dayEntries->where('entry_type', 'week_off')->first();
                                    
                                    $firstPunchIn = $punchIns->first();
                                    $lastPunchOut = $punchOuts->last();
                                    
                                    // Check if this date has images
                                    $dateHasImages = $employee->entryImages->where('entry_time', '>=', $date . ' 00:00:00')
                                                                          ->where('entry_time', '<=', $date . ' 23:59:59')
                                                                          ->count() > 0;
                                    
                                    // Calculate total working hours by pairing punch-ins with punch-outs
                                    $totalWorkingMinutes = 0;
                                    $punchInArray = $punchIns->values()->toArray();
                                    $punchOutArray = $punchOuts->values()->toArray();
                                    
                                    for ($i = 0; $i < count($punchInArray); $i++) {
                                        if (isset($punchOutArray[$i])) {
                                            $sessionMinutes = \Carbon\Carbon::parse($punchInArray[$i]['entry_time'])->diffInMinutes(\Carbon\Carbon::parse($punchOutArray[$i]['entry_time']));
                                            $totalWorkingMinutes += $sessionMinutes;
                                        }
                                    }
                                    
                                    $workingHours = $totalWorkingMinutes > 0 ? round($totalWorkingMinutes / 60, 2) : 0;
                                    $status = 'Absent';
                                    $statusClass = 'danger';
                                    
                                    // Check for half-day application first
                                    $isHalfDay = in_array($date, $employee->halfDayApplications ?? []);
                                    
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
                                    } elseif ($firstPunchIn) {
                                        if ($isHalfDay) {
                                            $status = 'Half Day';
                                            $statusClass = 'warning';
                                        } else {
                                            $status = 'Present';
                                            $statusClass = 'success';
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td style="color: #495af2; cursor: pointer;" onclick="viewDayDetails('{{ $employee->emp_id }}', '{{ $date }}')">
                                        {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                                    </td>
                                    <td>{{ $firstPunchIn ? $firstPunchIn->entry_time->format('H:i') : '-' }}</td>
                                    <td>{{ $lunchStart ? $lunchStart->entry_time->format('H:i') : '-' }}</td>
                                    <td>{{ $lunchEnd ? $lunchEnd->entry_time->format('H:i') : '-' }}</td>
                                    <td>{{ $lastPunchOut ? $lastPunchOut->entry_time->format('H:i') : '-' }}</td>
                                    <td>{{ $workingHours > 0 ? $workingHours . 'h' : '-' }}</td>
                                    <td>
                                        <span class="badge p-2 text-bg-{{ $statusClass }}">{{ $status }}</span>
                                        @if($dateHasImages)
                                            <i class="fas fa-camera" style="color: #28a745; margin-left: 8px;" title="Images captured on this date"></i>
                                        @endif
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
function viewDayDetails(empId, date) {
    // Fetch detailed time entries for the specific day
    fetch(`/super-admin/time-entries?employee=${empId}&from_date=${date}&to_date=${date}`, {
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
                const time = new Date(entry.entry_time).toLocaleTimeString('en-IN', {
                    timeZone: 'Asia/Kolkata',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
                const type = entry.entry_type.replace('_', ' ').toUpperCase();
                const typeColors = {
                    'PUNCH IN': '#10b981',
                    'PUNCH OUT': '#ef4444',
                    'LUNCH START': '#f59e0b',
                    'LUNCH END': '#f59e0b'
                };
                const color = typeColors[type] || '#6b7280';
                const hasImage = data.images && data.images[entry.id];
                
                entriesHtml += `<div style="margin: 10px 0; padding: 12px; border-left: 4px solid ${color}; background: #f8f9fa; border-radius: 4px; z-index: 1201;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div><strong style="color: ${color};">${type}</strong></div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-weight: 600;">${time}</span>
                            ${hasImage ? `<button class="btn btn-sm btn-info" onclick="viewEntryImage('${hasImage.imageFile}', '${type}', '${time}')" title="View captured image" style="padding: 2px 8px; font-size: 11px;"><i class="fas fa-camera"></i> View Image</button>` : ''}
                        </div>
                    </div>
                    ${entry.notes ? `<div style="margin-top: 5px; font-size: 12px; color: #666;">${entry.notes}</div>` : ''}
                </div>`;
            });
        } else {
            entriesHtml = '<p style="text-align: center; color: #666; margin: 20px 0;">No time entries found for this date.</p>';
        }
        
        Swal.fire({
            title: `Day Details - ${empId}`,
            html: `<div style="text-align: left; z-index: 1201;">
                <h6 style="margin-bottom: 15px; color: #333; text-align: center; z-index: 1201;">${new Date(date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</h6>
                ${entriesHtml}
            </div>`,
            width: '500px',
            confirmButtonColor: '#6366f1',
            customClass: {
                container: 'swal-high-z-index'
            }
        });
    })
    .catch(error => {
        Swal.fire({
            title: 'Error',
            text: 'Failed to load day details. Please try again.',
            icon: 'error',
            confirmButtonColor: '#6366f1',
            customClass: {
                container: 'swal-high-z-index'
            }
        });
    });
}

function viewEntryImage(imageFile, entryType, time) {
    // Create fullscreen overlay
    const overlay = document.createElement('div');
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        z-index: 15000;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    `;
    
    // Create image container
    const container = document.createElement('div');
    container.style.cssText = `
        position: relative;
        width: 50vw;
        height: 50vh;
        display: flex;
        flex-direction: column;
        align-items: center;
    `;
    
    // Create close button
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '<i class="fas fa-times"></i>';
    closeBtn.style.cssText = `
        position: absolute;
        top: -40px;
        right: 0;
        background: #fff;
        border: none;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        font-size: 16px;
        cursor: pointer;
        z-index: 15001;
        box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    `;
    
    // Create image
    const img = document.createElement('img');
    img.src = `/entry_images/${imageFile}`;
    img.style.cssText = `
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.5);
    `;
    
    // Create title
    const title = document.createElement('div');
    title.textContent = `${entryType} - ${time}`;
    title.style.cssText = `
        color: white;
        font-size: 18px;
        font-weight: bold;
        margin-top: 15px;
        text-align: center;
    `;
    
    // Close function
    const closeImage = () => {
        document.body.removeChild(overlay);
        document.body.style.overflow = 'auto';
    };
    
    // Event listeners
    closeBtn.onclick = closeImage;
    overlay.onclick = (e) => {
        if (e.target === overlay) closeImage();
    };
    
    // Escape key to close
    const handleEscape = (e) => {
        if (e.key === 'Escape') {
            closeImage();
            document.removeEventListener('keydown', handleEscape);
        }
    };
    document.addEventListener('keydown', handleEscape);
    
    // Assemble and show
    container.appendChild(closeBtn);
    container.appendChild(img);
    container.appendChild(title);
    overlay.appendChild(container);
    
    
    document.body.style.overflow = 'hidden';
    document.body.appendChild(overlay);
}

$(document).ready(function(){
    $("#custom-dates").css('display', "none");

});

function toggleCustomDates() {
    const period = document.querySelector('select[name="period"]').value;
    const customDates = document.getElementById('custom-dates');
    customDates.style.display = period === 'custom' ? 'block' : 'none';
}
</script>

@endsection

<style>
.swal-high-z-index {
    z-index: 10000 !important;
}
.swal2-container.swal-high-z-index {
    z-index: 10000 !important;
}
</style>