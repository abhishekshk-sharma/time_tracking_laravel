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
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="active" {{ request('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All</option>
                </select>
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
        $totalWorkingMinutes = 0;
        $workingDaysCount = 0;
        
        foreach($employees as $employee) {
            $timeEntriesByDate = $employee->timeEntries->groupBy(function($entry) {
                return $entry->entry_time->format('Y-m-d');
            });
            
            foreach($timeEntriesByDate as $date => $entries) {
                $punchIn = $entries->where('entry_type', 'punch_in')->first();
                $punchOut = $entries->where('entry_type', 'punch_out')->first();
                
                if($punchIn) {
                    $presentDays++;
                    if($punchIn->entry_time->format('H:i:s') > '09:15:00') $lateArrivals++;
                    
                    if($punchOut) {
                        $workingMinutes = $punchIn->entry_time->diffInMinutes($punchOut->entry_time);
                        
                        if($workingMinutes > 0) {
                            $totalWorkingMinutes += $workingMinutes;
                            $workingDaysCount++;
                        }
                    }
                } else {
                    $absentDays++;
                }
            }
        }
        
        $avgWorkingHours = $workingDaysCount > 0 ? round($totalWorkingMinutes / $workingDaysCount / 60, 1) : 0;
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
        <div class="stat-number">{{ $avgWorkingHours }}h</div>
        <div class="stat-label">Avg Working Hours</div>
    </div>
</div>

@if($employees->count() > 0)
    @foreach($employees as $employee)
    @php
        $hasImages = $employee->entryImages->where('entry_time', '>=', request('start_date', now()->startOfMonth()->format('Y-m-d')) . ' 00:00:00')
                                          ->where('entry_time', '<=', request('end_date', now()->endOfMonth()->format('Y-m-d')) . ' 23:59:59')
                                          ->count() > 0;
    @endphp
    <!-- Employee Header -->
    <div class="card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="width: 60px; height: 60px; background: #ff9900; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold;">
                    {{ strtoupper(substr($employee->username ?? 'N', 0, 1)) }}
                </div>
                <div>
                    <a href="{{ route('admin.employees.show', $employee->id) }}" style="margin: 0; color: #3b82f6; text-decoration: none;"><h3 style="margin: 0; color: #3b82f6;">{{ $employee->username ?? 'Unknown' }}</h3></a>
                    <p style="margin: 5px 0 0; color: #565959;">
                        ID: {{ $employee->emp_id }} | Department: {{ $employee->department->name ?? 'N/A' }} | Position: {{ $employee->position ?? 'N/A' }}
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
                            <th>Lunch Start</th>
                            <th>Lunch End</th>
                            <th>Punch Out</th>
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
                            $punchIns = $entries->where('entry_type', 'punch_in')->sortBy('entry_time');
                            $punchOuts = $entries->where('entry_type', 'punch_out')->sortBy('entry_time');
                            $lunchStart = $entries->where('entry_type', 'lunch_start')->first();
                            $lunchEnd = $entries->where('entry_type', 'lunch_end')->first();
                            
                            $firstPunchIn = $punchIns->first();
                            $lastPunchOut = $punchOuts->last();
                            
                            // Calculate total working minutes by pairing punch-ins with punch-outs
                            $totalWorkingMinutes = 0;
                            $punchInArray = $punchIns->values()->toArray();
                            $punchOutArray = $punchOuts->values()->toArray();
                            
                            for ($i = 0; $i < count($punchInArray); $i++) {
                                if (isset($punchOutArray[$i])) {
                                    $sessionMinutes = \Carbon\Carbon::parse($punchInArray[$i]['entry_time'])->diffInMinutes(\Carbon\Carbon::parse($punchOutArray[$i]['entry_time']));
                                    $totalWorkingMinutes += $sessionMinutes;
                                }
                            }
                            
                            $lunchMinutes = 0;
                            if ($lunchStart && $lunchEnd) {
                                $lunchMinutes = $lunchStart->entry_time->diffInMinutes($lunchEnd->entry_time);
                            }
                            
                            $netHours = $totalWorkingMinutes > 0 ? floor($totalWorkingMinutes / 60) . ':' . sprintf('%02d', $totalWorkingMinutes % 60) : '-';
                            $lunchDuration = $lunchMinutes > 0 ? floor($lunchMinutes / 60) . ':' . sprintf('%02d', $lunchMinutes % 60). ':' . (($lunchMinutes * 60) % 60) : '-';
                            
                            // Check if this date has images
                            $dateHasImages = $employee->entryImages->where('entry_time', '>=', $date . ' 00:00:00')
                                                                  ->where('entry_time', '<=', $date . ' 23:59:59')
                                                                  ->count() > 0;
                            
                            // Determine all applicable statuses
                            $statuses = [];
                            $isLate = false;
                            $isHalfDay = false;
                            $isLongLunch = false;
                            
                            // Check for half-day application first
                            $isHalfDay = in_array($date, $employee->halfDayApplications ?? []);
                            
                            if (!$firstPunchIn) {
                                // Check if it's a weekend
                                $dateCarbon = \Carbon\Carbon::parse($date);
                                if ($dateCarbon->isSunday() || ($dateCarbon->isSaturday() && in_array(ceil($dateCarbon->day / 7), [2, 4]))) {
                                    $statuses[] = ['text' => 'Week Off', 'color' => '#17a2b8', 'bg' => '#d1ecf1'];
                                } else {
                                    $statuses[] = ['text' => 'Absent', 'color' => '#dc3545', 'bg' => '#f8d7da'];
                                }
                            } else {
                                if ($isHalfDay) {
                                    $statuses[] = ['text' => 'Half Day', 'color' => '#fd7e14', 'bg' => '#fff3cd'];
                                } else {
                                    $statuses[] = ['text' => 'Present', 'color' => '#28a745', 'bg' => '#d4edda'];
                                }
                                
                                if ($firstPunchIn->entry_time->format('H:i') > '09:15') {
                                    $statuses[] = ['text' => 'Late', 'color' => '#ffc107', 'bg' => '#fff3cd'];
                                    $isLate = true;
                                }
                            }
                            
                            if ($lunchMinutes > 60) {
                                $statuses[] = ['text' => 'Long Lunch', 'color' => '#fd7e14', 'bg' => '#ffeaa7'];
                                $isLongLunch = true;
                            }
                        @endphp
                        <tr>
                            <td style="color: #495af2; cursor: pointer;" onclick="viewDayDetails('{{ $employee->emp_id }}', '{{ $date }}')">
                                {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                            </td>
                            <td style="cursor: pointer; color: #3b82f6;" onclick="editTime('{{ $employee->emp_id }}', '{{ $date }}', 'punch_in', '{{ $firstPunchIn->id ?? '' }}', '{{ $firstPunchIn ? $firstPunchIn->entry_time->format('H:i') : '' }}')">{{ $firstPunchIn ? $firstPunchIn->entry_time->format('h:i A') : '-' }}</td>
                            <td style="cursor: pointer; color: #3b82f6;" onclick="editTime('{{ $employee->emp_id }}', '{{ $date }}', 'lunch_start', '{{ $lunchStart->id ?? '' }}', '{{ $lunchStart ? $lunchStart->entry_time->format('H:i') : '' }}')">{{ $lunchStart ? $lunchStart->entry_time->format('h:i A') : '-' }}</td>
                            <td style="cursor: pointer; color: #3b82f6;" onclick="editTime('{{ $employee->emp_id }}', '{{ $date }}', 'lunch_end', '{{ $lunchEnd->id ?? '' }}', '{{ $lunchEnd ? $lunchEnd->entry_time->format('H:i') : '' }}')">{{ $lunchEnd ? $lunchEnd->entry_time->format('h:i A') : '-' }}</td>
                            <td style="cursor: pointer; color: #3b82f6;" onclick="editTime('{{ $employee->emp_id }}', '{{ $date }}', 'punch_out', '{{ $lastPunchOut->id ?? '' }}', '{{ $lastPunchOut ? $lastPunchOut->entry_time->format('H:i') : '' }}')">{{ $lastPunchOut ? $lastPunchOut->entry_time->format('h:i A') : '-' }}</td>
                            <td>{{ $netHours }}</td>
                            <td>
                                @foreach($statuses as $status)
                                    <span style="display: inline-block; padding: 2px 8px; margin: 1px; border-radius: 12px; font-size: 11px; font-weight: 600; color: {{ $status['color'] }}; background-color: {{ $status['bg'] }}; border: 1px solid {{ $status['color'] }};">{{ $status['text'] }}</span>
                                @endforeach
                                @if($dateHasImages)
                                    <i class="fas fa-camera" style="color: #28a745; margin-left: 8px;" title="Images captured on this date"></i>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: #565959;">No time entries found for selected period</td>
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
function editTime(empId, date, entryType, entryId, currentTime) {
    const isNewEntry = !entryId || !currentTime;
    
    Swal.fire({
        title: isNewEntry ? `Add ${entryType.replace('_', ' ').toUpperCase()}` : `Edit ${entryType.replace('_', ' ').toUpperCase()}`,
        html: `
            <div style="text-align: left; padding: 20px;">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-user me-2"></i>Employee ID</label>
                    <input type="text" class="form-control" value="${empId}" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-calendar me-2"></i>Date</label>
                    <input type="text" class="form-control" value="${new Date(date).toLocaleDateString()}" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-clock me-2"></i>Time</label>
                    <input type="time" class="form-control" id="edit_time_input" value="${currentTime}">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-save"></i> Save',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        width: '500px',
        customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false,
        preConfirm: () => {
            const newTime = document.getElementById('edit_time_input').value;
            if (!newTime) {
                Swal.showValidationMessage('Please enter a time');
                return false;
            }
            return newTime;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            if (isNewEntry) {
                addNewTimeEntry(empId, date, entryType, result.value);
            } else {
                updateSingleTime(entryId, date, result.value);
            }
        }
    });
}

function addNewTimeEntry(empId, date, entryType, time) {
    fetch('/admin/time-entries/add', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ 
            employee_id: empId,
            date: date,
            entry_type: entryType,
            time: time
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Success',
                text: 'Time entry added successfully',
                icon: 'success',
                confirmButtonColor: '#3b82f6'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: data.message || 'Failed to add time entry',
                icon: 'error',
                confirmButtonColor: '#3b82f6'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error',
            text: 'Failed to add time entry',
            icon: 'error',
            confirmButtonColor: '#3b82f6'
        });
    });
}

function updateSingleTime(entryId, date, newTime) {
    fetch('/admin/time-entries/update', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ 
            entry_id: entryId,
            new_time: date + ' ' + newTime + ':00'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Success',
                text: 'Time updated successfully',
                icon: 'success',
                confirmButtonColor: '#3b82f6'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: data.message || 'Failed to update time',
                icon: 'error',
                confirmButtonColor: '#3b82f6'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error',
            text: 'Failed to update time',
            icon: 'error',
            confirmButtonColor: '#3b82f6'
        });
    });
}

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
                const hasImage = data.images && data.images[entry.id];
                
                entriesHtml += `<div style="margin: 10px 0; padding: 12px; border-left: 4px solid ${color}; background: #f8f9fa; border-radius: 4px;">
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
        z-index: 9999;
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
        z-index: 10000;
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