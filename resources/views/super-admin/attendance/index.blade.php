@extends('super-admin.layouts.app')

@section('title', 'Attendance')

@section('content')
<div class="page-header">
    <h1 class="page-title">Attendance Management</h1>
    <p class="page-subtitle">Monitor employee attendance and working hours</p>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h6 class="card-title mb-0"><i class="fas fa-filter me-2"></i>Filter Options</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="filter-grid">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-calendar me-2"></i> Date</label>
                    <input type="date" name="date" class="form-control" value="{{ request('date', $date) }}">
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-search me-2"></i> Search Employee</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by name or ID" value="{{ request('search') }}">
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-list me-2"></i> Per Page</label>
                    <select name="per_page" class="form-control">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filter
                </button>
                <a href="{{ route('super-admin.attendance') }}" class="btn btn-secondary">
                    <i class="fas fa-refresh"></i> Reset
                </a>
            </div>
        </form>
    </div>
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
        <div class="stat-number">{{ $employees->total() }}</div>
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
                        @forelse($employees as $employee)
                        @php
                            $hasImages = $employee->entryImages->where('entry_time', '>=', $date . ' 00:00:00')->where('entry_time', '<=', $date . ' 23:59:59')->count() > 0;
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
                                @if(isset($employee->workingHours) && $employee->workingHours && $employee->workingHours !== '0:00')
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
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-calendar-check fa-2x mb-2"></i><br>
                                    No employees found for the selected criteria
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3 px-3 pb-3">
                {{-- <div class="text-muted">
                    Showing {{ $employees->firstItem() ?? 0 }} to {{ $employees->lastItem() ?? 0 }} of {{ $employees->total() }} results
                </div> --}}
                {{ $employees->appends(request()->query())->links() }}
            </div>
        @else
            <div style="text-align: center; padding: 40px; color: #565959;">
                <i class="fas fa-calendar-check" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                <h3>No employees found</h3>
                <p>No active employees match the selected criteria</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<style>
/* Filter Form Styling */
.filter-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.filter-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-start;
}

.filter-form .form-group {
    margin-bottom: 0;
}

.filter-form .form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #374151;
    font-size: 14px;
}

.filter-form .form-control {
    height: 42px;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    background: #ffffff;
    transition: all 0.15s ease;
    box-sizing: border-box;
}

.filter-form .form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.filter-form .btn {
    height: 42px;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.filter-form .btn-primary {
    background: #3b82f6;
    color: white;
}

.filter-form .btn-primary:hover {
    background: #2563eb;
}

.filter-form .btn-secondary {
    background: #6b7280;
    color: white;
}

.filter-form .btn-secondary:hover {
    background: #4b5563;
}

.card-header.bg-light {
    background-color: #f8fafc !important;
    border-bottom: 1px solid #e5e7eb;
}

.card-title {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
}

/* Time Entries Modal Styling */
.time-entries-modal {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.modal-form-container {
    text-align: left;
    padding: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #374151;
    font-size: 14px;
}

.form-control {
    width: 100%;
    height: 42px;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    background: #ffffff;
    transition: all 0.15s ease;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-control:read-only {
    background-color: #f9fafb;
    color: #6b7280;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 2px solid #e5e7eb;
}

.existing-entries {
    margin-bottom: 30px;
}

.time-entry-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
    transition: all 0.2s ease;
}

.time-entry-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.add-entry-section {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    margin-top: 20px;
}

.btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-success:hover {
    background: #059669;
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}
</style>
<script>
function viewTimeEntries(empId, date) {
    // Fetch time entries via AJAX
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
                const time = new Date(entry.entry_time).toLocaleTimeString('en-IN', {
                    timeZone: 'Asia/Kolkata',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
                const type = entry.entry_type.replace('_', ' ').toUpperCase();
                const hasImage = data.images && data.images[entry.id];
                
                entriesHtml += `<div style="margin: 10px 0; padding: 8px; border-left: 3px solid #ff6b35; background: #f8f9fa;">
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
            confirmButtonColor: '#6366f1',
            customClass: {
                container: 'swal-high-z-index'
            }
        });
    })
    .catch(error => {
        Swal.fire({
            title: 'Error',
            text: 'Failed to load time entries. Please try again.',
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

function editTimeEntries(empId, date, empName) {
    // Fetch time entries for the employee and date
    fetch(`/super-admin/time-entries/employee/${empId}/${date}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showTimeEntriesModal(empId, date, empName, data.timeEntries);
            } else {
                Swal.fire('Error', 'Failed to load time entries', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Failed to load time entries', 'error');
        });
}

function showTimeEntriesModal(empId, date, empName, timeEntries) {
    let entriesHtml = '';
    
    if (timeEntries.length === 0) {
        entriesHtml = '<p class="text-muted text-center py-3">No time entries found for this date</p>';
    } else {
        entriesHtml = '<div class="time-entries-list">';
        timeEntries.forEach((entry, index) => {
            const time = new Date(entry.entry_time).toLocaleTimeString('en-US', {
                hour12: true,
                hour: '2-digit',
                minute: '2-digit'
            });
            
            entriesHtml += `
                <div class="time-entry-card">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <label class="form-label">${entry.entry_type.replace('_', ' ').toUpperCase()}</label>
                        </div>
                        <div class="col-md-6">
                            <input type="time" class="form-control" id="time_${entry.id}" value="${entry.entry_time.substring(11, 16)}">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteTimeEntry(${entry.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        entriesHtml += '</div>';
    }
    
    Swal.fire({
        title: `<i class="fas fa-clock"></i> Edit Time Entries - ${empName}`,
        html: `
            <div class="modal-form-container">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-calendar me-2"></i>Date</label>
                    <input type="text" class="form-control" value="${new Date(date).toLocaleDateString()}" readonly>
                </div>
                
                <div class="existing-entries">
                    <h6 class="section-title">Current Time Entries</h6>
                    ${entriesHtml}
                </div>
                
                <div class="add-entry-section">
                    <h6 class="section-title">Add New Entry</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Entry Type</label>
                                <select class="form-control" id="new_entry_type">
                                    <option value="">Select Type</option>
                                    <option value="punch_in">Punch In</option>
                                    <option value="punch_out">Punch Out</option>
                                    <option value="lunch_start">Lunch Start</option>
                                    <option value="lunch_end">Lunch End</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Time</label>
                                <input type="time" class="form-control" id="new_entry_time">
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-success btn-sm" onclick="addTimeEntry('${empId}', '${date}')">
                        <i class="fas fa-plus"></i> Add Entry
                    </button>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-save"></i> Save Changes',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        width: '700px',
        customClass: {
            popup: 'time-entries-modal',
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false,
        preConfirm: () => {
            const updates = [];
            timeEntries.forEach(entry => {
                const timeInput = document.getElementById(`time_${entry.id}`);
                if (timeInput && timeInput.value !== entry.entry_time.substring(11, 16)) {
                    updates.push({
                        id: entry.id,
                        time: timeInput.value
                    });
                }
            });
            return updates;
        }
    }).then((result) => {
        if (result.isConfirmed && result.value.length > 0) {
            updateTimeEntries(result.value);
        }
    });
}

function updateTimeEntries(updates) {
    fetch('/super-admin/time-entries/update', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ updates: updates })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success', 'Time entries updated successfully', 'success').then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Error', data.message || 'Failed to update time entries', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Failed to update time entries', 'error');
    });
}

function addTimeEntry(empId, date) {
    const type = document.getElementById('new_entry_type').value;
    const time = document.getElementById('new_entry_time').value;
    
    if (!type || !time) {
        Swal.fire('Error', 'Please select entry type and time', 'error');
        return;
    }
    
    fetch('/super-admin/time-entries/add', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            employee_id: empId,
            date: date,
            entry_type: type,
            time: time
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success', 'Time entry added successfully', 'success').then(() => {
                editTimeEntries(empId, date, document.querySelector(`[onclick*="${empId}"]`).closest('tr').querySelector('div > div').textContent);
            });
        } else {
            Swal.fire('Error', data.message || 'Failed to add time entry', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Failed to add time entry', 'error');
    });
}

function deleteTimeEntry(entryId) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This time entry will be deleted permanently',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/super-admin/time-entries/${entryId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`#time_${entryId}`).closest('.mb-3').remove();
                    Swal.fire('Deleted!', 'Time entry has been deleted.', 'success');
                } else {
                    Swal.fire('Error', 'Failed to delete time entry', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Failed to delete time entry', 'error');
            });
        }
    });
}
</script>
<style>
.swal-high-z-index {
    z-index: 10000 !important;
}
.swal2-container.swal-high-z-index {
    z-index: 10000 !important;
}
</style>
@endpush