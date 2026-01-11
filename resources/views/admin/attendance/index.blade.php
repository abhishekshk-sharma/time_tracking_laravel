@extends('admin.layouts.app')

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

<!-- Filters -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.attendance') }}" style="display: grid; grid-template-columns: 200px 200px 200px 120px 120px; gap: 15px; align-items: end;">
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
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Search Employee</label>
                <input type="text" name="search" class="form-control" placeholder="Name or ID" value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Filter
            </button>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Per Page</label>
                <select name="per_page" class="form-control" style="width: 45px;">
                    <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                </select>
            </div>
            
        </form>
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
                            $hasImages = $employee->entryImages->where('entry_time', '>=', $date . ' 00:00:00')->where('entry_time', '<=', $date . ' 23:59:59')->count() > 0;
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
                                    @if($hasImages)
                                        <i class="fas fa-camera" style="color: #28a745; margin-left: 8px;" title="Images captured"></i>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($employee->department && is_object($employee->department))
                                    <span class="badge p-2 text-bg-secondary">{{ $employee->department->name }}</span>
                                @elseif($employee->department)
                                    <span class="badge p-2 text-bg-secondary">{{ $employee->department }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($employee->attendanceStatus == 'half_day')
                                    <span class="badge p-2 text-bg-warning">Half Day</span>
                                @elseif($employee->attendanceStatus == 'present')
                                    <span class="badge p-2 text-bg-success">Present</span>
                                @elseif($isWeekend)
                                    <span class="badge p-2 text-bg-info">Week Off</span>
                                @else
                                    <span class="badge p-2 text-bg-danger">Absent</span>
                                @endif
                            </td>
                            <td>
                                @if($employee->firstPunchIn)
                                    <div style="font-weight: 500;">{{ $employee->firstPunchIn->entry_time->format('h:i A') }}</div>
                                    <div style="font-size: 12px; color: #565959;">{{ $employee->firstPunchIn->entry_time->format('M d') }}</div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($employee->lastPunchOut)
                                    <div style="font-weight: 500;">{{ $employee->lastPunchOut->entry_time->format('h:i A') }}</div>
                                    <div style="font-size: 12px; color: #565959;">{{ $employee->lastPunchOut->entry_time->format('M d') }}</div>
                                @else
                                    @if($employee->firstPunchIn)
                                        <span class="badge p-2 text-bg-warning">Still Working</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($employee->workingHours && $employee->workingHours !== '0:00')
                                    <span style="font-weight: 500;">{{ $employee->workingHours }}</span>
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
            
            <!-- Pagination -->
            <div style="padding: 20px;">
                {{ $employees->appends(request()->query())->links() }}
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
@endsection

@push('scripts')
<style>
.table tbody tr:hover {
    background-color: #f8f9fa;
}

.badge {
    font-size: 0.75em;
    padding: 0.375em 0.75em;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.2rem;
}

.image-link {
    color: #007bff;
    text-decoration: none;
    font-size: 0.8em;
    margin-left: 8px;
}

.image-link:hover {
    text-decoration: underline;
    color: #0056b3;
}
</style>
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
                const hasImage = data.images && data.images[entry.id];
                
                entriesHtml += `<div style="margin: 10px 0; padding: 8px; border-left: 3px solid #ff9900; background: #f8f9fa;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div><strong>${type}</strong> - ${time}</div>
                        ${hasImage ? `<button class="btn btn-sm btn-info" onclick="viewEntryImage('${hasImage.imageFile}', '${type}', '${time}')" title="View captured image" style="padding: 2px 8px; font-size: 11px;"><i class="fas fa-camera"></i> View Image</button>` : ''}
                    </div>
                    ${entry.notes ? `<small style="color: #666;">${entry.notes}</small>` : ''}
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