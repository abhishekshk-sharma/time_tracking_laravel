@extends('admin.layouts.app')

@section('title', 'Employee History')

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Employee Time History</h1>
            <p class="page-subtitle">{{ $employee->name }} ({{ $employee->emp_id }})</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.employees.show', $employee) }}" class="btn btn-secondary">
                <i class="fas fa-user"></i> Employee Details
            </a>
            <a href="{{ route('admin.employees') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-body">
        <form method="GET" style="display: grid; grid-template-columns: 200px 200px 200px 120px; gap: 15px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Time Period</label>
                <select name="filter" class="form-control" onchange="toggleCustomDates(this.value)">
                    <option value="this_month" {{ $filter == 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $filter == 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="custom" {{ $filter == 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;" id="start_date_group" {{ $filter != 'custom' ? 'style=display:none;' : '' }}>
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            
            <div class="form-group" style="margin-bottom: 0;" id="end_date_group" {{ $filter != 'custom' ? 'style=display:none;' : '' }}>
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Filter
            </button>
        </form>
    </div>
</div>

<!-- Time Entries -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Time Entries ({{ $paginatedEntries->total() }} days)</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        @if($paginatedEntries->count() > 0)
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Punch In</th>
                            <th>Lunch Start</th>
                            <th>Lunch End</th>
                            <th>Punch Out</th>
                            <th>Holiday/Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paginatedEntries as $date => $dayEntries)
                        <tr>
                            <td>
                                <div style="font-weight: 500;">
                                    {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                                </div>
                                <div style="font-size: 12px; color: #565959;">
                                    {{ \Carbon\Carbon::parse($date)->format('l') }}
                                </div>
                            </td>
                            <td>
                                @if($dayEntries->has('punch_in'))
                                    <span class="badge text-bg-success" style="cursor: pointer;" onclick="editTimeEntry('{{ $dayEntries['punch_in']->id }}', 'punch_in', '{{ $dayEntries['punch_in']->entry_time->format('H:i') }}', '{{ $date }}')">{{ $dayEntries['punch_in']->entry_time->format('h:i A') }}</span>
                                @else
                                    <span class="text-muted" style="cursor: pointer;" onclick="editTimeEntry('', 'punch_in', '', '{{ $date }}')">-</span>
                                @endif
                            </td>
                            <td>
                                @if($dayEntries->has('lunch_start'))
                                    <span class="badge text-bg-warning" style="cursor: pointer;" onclick="editTimeEntry('{{ $dayEntries['lunch_start']->id }}', 'lunch_start', '{{ $dayEntries['lunch_start']->entry_time->format('H:i') }}', '{{ $date }}')">{{ $dayEntries['lunch_start']->entry_time->format('h:i A') }}</span>
                                @else
                                    <span class="text-muted" style="cursor: pointer;" onclick="editTimeEntry('', 'lunch_start', '', '{{ $date }}')">-</span>
                                @endif
                            </td>
                            <td>
                                @if($dayEntries->has('lunch_end'))
                                    <span class="badge text-bg-warning" style="cursor: pointer;" onclick="editTimeEntry('{{ $dayEntries['lunch_end']->id }}', 'lunch_end', '{{ $dayEntries['lunch_end']->entry_time->format('H:i') }}', '{{ $date }}')">{{ $dayEntries['lunch_end']->entry_time->format('h:i A') }}</span>
                                @else
                                    <span class="text-muted" style="cursor: pointer;" onclick="editTimeEntry('', 'lunch_end', '', '{{ $date }}')">-</span>
                                @endif
                            </td>
                            <td>
                                @if($dayEntries->has('punch_out'))
                                    <span class="badge text-bg-danger" style="cursor: pointer;" onclick="editTimeEntry('{{ $dayEntries['punch_out']->id }}', 'punch_out', '{{ $dayEntries['punch_out']->entry_time->format('H:i') }}', '{{ $date }}')">{{ $dayEntries['punch_out']->entry_time->format('h:i A') }}</span>
                                @else
                                    @if($dayEntries->has('punch_in'))
                                        <span class="badge text-bg-warning" style="cursor: pointer;" onclick="editTimeEntry('', 'punch_out', '', '{{ $date }}')">Still Working</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($dayEntries->has('holiday'))
                                    <span class="badge text-bg-secondary">{{ $dayEntries['holiday']->notes }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($paginatedEntries->hasPages())
                <div style="padding: 20px; border-top: 1px solid #eee;">
                    {{ $paginatedEntries->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <div style="text-align: center; padding: 40px; color: #565959;">
                <i class="fas fa-clock" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                <h3>No time entries found</h3>
                <p>No time entries found for the selected period</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function editTimeEntry(entryId, entryType, currentTime, date) {
    const isNewEntry = !entryId || !currentTime;
    const typeLabels = {
        'punch_in': 'Punch In',
        'punch_out': 'Punch Out', 
        'lunch_start': 'Lunch Start',
        'lunch_end': 'Lunch End'
    };
    
    Swal.fire({
        title: isNewEntry ? `Add ${typeLabels[entryType]}` : `Edit ${typeLabels[entryType]}`,
        html: `
            <div style="text-align: left; margin: 20px 0;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Date: ${new Date(date).toLocaleDateString()}</label>
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Time:</label>
                <input type="time" id="edit-time" value="${currentTime}" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Update',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#ff9900',
        preConfirm: () => {
            const newTime = document.getElementById('edit-time').value;
            if (!newTime) {
                Swal.showValidationMessage('Please select a time');
                return false;
            }
            return newTime;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            if (isNewEntry) {
                addTimeEntry(entryId, entryType, result.value, date);
            } else {
                updateTimeEntry(entryId, result.value, date);
            }
        }
    });
}

function addTimeEntry(entryId, entryType, newTime, date) {
    fetch('/admin/time-entries/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            employee_id: '{{ $employee->emp_id }}',
            date: date,
            entry_type: entryType,
            time: newTime
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Added!',
                text: 'Time entry added successfully.',
                confirmButtonColor: '#ff9900'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message || 'Failed to add time entry.',
                confirmButtonColor: '#ff9900'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An error occurred while adding the time entry.',
            confirmButtonColor: '#ff9900'
        });
    });
}

function updateTimeEntry(entryId, newTime, date) {
    fetch('/admin/time-entries/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            entry_id: entryId,
            new_time: `${date} ${newTime}:00`
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: 'Time entry updated successfully.',
                confirmButtonColor: '#ff9900'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message || 'Failed to update time entry.',
                confirmButtonColor: '#ff9900'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An error occurred while updating the time entry.',
            confirmButtonColor: '#ff9900'
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
</script>
@endpush