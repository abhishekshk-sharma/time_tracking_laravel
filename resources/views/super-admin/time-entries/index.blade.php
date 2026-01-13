@extends('super-admin.layouts.app')

@section('title', 'Time Entries')

@section('content')
<div class="page-header">
    <h1 class="page-title">Time Entries</h1>
    <p class="page-subtitle">Manage and monitor all employee time tracking entries</p>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Filter Time Entries</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('super-admin.time-entries') }}" id="filterForm">
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 15px; align-items: end;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">Employee</label>
                    <select name="employee" id="employeeSelect" class="form-control">
                        <option value="">All Employees</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->emp_id }}" {{ request('employee') == $employee->emp_id ? 'selected' : '' }}>
                                {{ $employee->username }} ({{ $employee->emp_id }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" id="fromDate" class="form-control" value="{{ request('from_date') }}">
                </div>
                
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" id="toDate" class="form-control" value="{{ request('to_date') }}">
                </div>
                
                <div style="display: flex; gap: 8px;">
                    <button type="button" id="filterBtn" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('super-admin.time-entries') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Time Entries Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Time Entries ({{ $timeEntries->total() }} total)</h3>
    </div>
    <div class="card-body" style="padding: 0;" id="timeEntriesContainer">
        @if($timeEntries->count() > 0)
            <div class="table-responsive">
                <table class="table" style="margin: 0;" id="timeEntriesTable">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th>Employee</th>
                            <th>Entry Type</th>
                            <th>Date & Time</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="timeEntriesBody">
                        @foreach($timeEntries as $entry)
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center;">
                                        <div style="width: 32px; height: 32px; background: #ff6b35; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; margin-right: 10px; font-size: 12px;">
                                            {{ strtoupper(substr($entry->employee->username ?? 'N', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div style="font-weight: 500;">{{ $entry->employee->username ?? 'Unknown' }}</div>
                                            <div style="font-size: 12px; color: #6b7280;">{{ $entry->employee_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $badgeClass = match($entry->entry_type) {
                                            'punch_in' => 'success',
                                            'punch_out' => 'danger',
                                            'lunch_start' => 'warning',
                                            'lunch_end' => 'info',
                                            'holiday' => 'secondary',
                                            'sick_leave' => 'warning',
                                            'casual_leave' => 'warning',
                                            'week_off' => 'secondary',
                                            default => 'primary'
                                        };
                                    @endphp
                                    <span class="badge p-2 text-bg-{{ $badgeClass }}">
                                        {{ ucwords(str_replace('_', ' ', $entry->entry_type)) }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 500;">{{ $entry->entry_time->format('M d, Y') }}</div>
                                    <div style="font-size: 12px; color: #6b7280;">{{ $entry->entry_time->format('H:i:s') }}</div>
                                </td>
                                <td>
                                    <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $entry->notes }}">
                                        {{ $entry->notes ?: '-' }}
                                    </div>
                                </td>
                                <td>
                                    <button onclick="deleteEntry({{ $entry->id }})" class="btn btn-sm btn-danger" title="Delete Entry">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($timeEntries->hasPages())
                <div style="padding: 20px; border-top: 1px solid #e9ecef;" id="paginationContainer">
                    {{ $timeEntries->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <div style="text-align: center; padding: 60px; color: #6b7280;" id="noEntriesMessage">
                <i class="fas fa-clock" style="font-size: 64px; margin-bottom: 20px; opacity: 0.3;"></i>
                <h3>No time entries found</h3>
                <p>No time entries match your current filters.</p>
            </div>
        @endif
    </div>
</div>

<script>
$('#filterBtn').click(function() {
    const employee = $('#employeeSelect').val();
    const fromDate = $('#fromDate').val();
    const toDate = $('#toDate').val();
    
    if (employee && fromDate && toDate) {
        // AJAX request for specific employee and date range
        $.ajax({
            url: '{{ route("super-admin.time-entries") }}',
            method: 'GET',
            data: {
                employee: employee,
                from_date: fromDate,
                to_date: toDate
            },
            success: function(response) {
                displayFilteredEntries(response.entries, employee);
            },
            error: function() {
                alert('Error loading time entries');
            }
        });
    } else {
        // Regular form submission
        $('#filterForm').submit();
    }
});

function displayFilteredEntries(entries, employeeId) {
    const employeeName = $('#employeeSelect option:selected').text();
    let html = '';
    
    if (entries.length > 0) {
        html = `
            <div class="table-responsive">
                <table class="table" style="margin: 0;">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th>Employee</th>
                            <th>Entry Type</th>
                            <th>Date & Time</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>`;
        
        entries.forEach(function(entry) {
            const badgeClass = getBadgeClass(entry.entry_type);
            const entryDate = new Date(entry.entry_time);
            const formattedDate = entryDate.toLocaleDateString('en-US', { 
                month: 'short', day: 'numeric', year: 'numeric' 
            });
            const formattedTime = entryDate.toLocaleTimeString('en-US', { 
                hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' 
            });
            
            html += `
                <tr>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <div style="width: 32px; height: 32px; background: #ff6b35; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; margin-right: 10px; font-size: 12px;">
                                ${employeeName.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <div style="font-weight: 500;">${employeeName.split(' (')[0]}</div>
                                <div style="font-size: 12px; color: #6b7280;">${entry.employee_id}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge p-2 text-bg-${badgeClass}">
                            ${entry.entry_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight: 500;">${formattedDate}</div>
                        <div style="font-size: 12px; color: #6b7280;">${formattedTime}</div>
                    </td>
                    <td>
                        <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${entry.notes || ''}">
                            ${entry.notes || '-'}
                        </div>
                    </td>
                    <td>
                        <button onclick="deleteEntry(${entry.id})" class="btn btn-sm btn-danger" title="Delete Entry">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
        });
        
        html += `
                    </tbody>
                </table>
            </div>`;
    } else {
        html = `
            <div style="text-align: center; padding: 60px; color: #6b7280;">
                <i class="fas fa-clock" style="font-size: 64px; margin-bottom: 20px; opacity: 0.3;"></i>
                <h3>No time entries found</h3>
                <p>No time entries found for the selected filters.</p>
            </div>`;
    }
    
    $('#timeEntriesContainer').html(html);
    $('.card-title').text(`Time Entries (${entries.length} total)`);
}

function getBadgeClass(entryType) {
    const badgeMap = {
        'punch_in': 'success',
        'punch_out': 'danger', 
        'lunch_start': 'warning',
        'lunch_end': 'info',
        'holiday': 'secondary',
        'sick_leave': 'warning',
        'casual_leave': 'warning',
        'week_off': 'secondary'
    };
    return badgeMap[entryType] || 'primary';
}

function deleteEntry(entryId) {
    if (confirm('Are you sure you want to delete this time entry? This action cannot be undone.')) {
        fetch(`/super-admin/time-entries/${entryId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Check if we're in filtered view
                const employee = $('#employeeSelect').val();
                const fromDate = $('#fromDate').val();
                const toDate = $('#toDate').val();
                
                if (employee && fromDate && toDate) {
                    // Refresh filtered results
                    $.ajax({
                        url: '{{ route("super-admin.time-entries") }}',
                        method: 'GET',
                        data: {
                            employee: employee,
                            from_date: fromDate,
                            to_date: toDate
                        },
                        success: function(response) {
                            displayFilteredEntries(response.entries, employee);
                        }
                    });
                } else {
                    // Remove the row from table
                    $(`button[onclick="deleteEntry(${entryId})"]`).closest('tr').remove();
                    
                    // Update entry count
                    const currentCount = parseInt($('.card-title').text().match(/\d+/)[0]);
                    $('.card-title').text(`Time Entries (${currentCount - 1} total)`);
                    
                    // Check if table is empty
                    if ($('#timeEntriesBody tr').length === 0) {
                        $('#timeEntriesContainer').html(`
                            <div style="text-align: center; padding: 60px; color: #6b7280;">
                                <i class="fas fa-clock" style="font-size: 64px; margin-bottom: 20px; opacity: 0.3;"></i>
                                <h3>No time entries found</h3>
                                <p>No time entries match your current filters.</p>
                            </div>
                        `);
                    }
                }
            } else {
                alert('Error deleting entry');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting entry');
        });
    }
}
</script>

@endsection