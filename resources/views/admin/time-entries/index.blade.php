@extends('admin.layouts.app')

@section('title', 'Time Entries')

@section('content')
<div class="page-header">
    <h1 class="page-title">Time Entries</h1>
    <p class="page-subtitle">Detailed time tracking records</p>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-body">
        <form method="GET" style="display: grid; grid-template-columns: 200px 200px 200px 120px; gap: 15px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Employee</label>
                <select name="employee" class="form-control">
                    <option value="">All Employees</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->emp_id }}" {{ request('employee') == $employee->emp_id ? 'selected' : '' }}>
                            {{ $employee->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date', today()->subDays(7)->format('Y-m-d')) }}">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date', today()->format('Y-m-d')) }}">
            </div>
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Filter
            </button>
        </form>
    </div>
</div>

<!-- Time Entries Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Time Entries ({{ $timeEntries->total() }} total)</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        @if($timeEntries->count() > 0)
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Entry Type</th>
                            <th>Date & Time</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($timeEntries as $entry)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <div style="width: 32px; height: 32px; background: #ff9900; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; margin-right: 10px;">
                                        {{ strtoupper(substr($entry->employee->username ?? 'N', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 500;">{{ $entry->employee->username ?? 'Unknown' }}</div>
                                        <div style="font-size: 12px; color: #565959;">{{ $entry->employee_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $typeColors = [
                                        'punch_in' => 'success',
                                        'punch_out' => 'danger',
                                        'lunch_start' => 'warning',
                                        'lunch_end' => 'warning',
                                        'holiday' => 'secondary'
                                    ];
                                    $color = $typeColors[$entry->entry_type] ?? 'secondary';
                                @endphp
                                <span class="badge badge-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $entry->entry_type)) }}</span>
                            </td>
                            <td>
                                <div style="font-weight: 500;">{{ $entry->entry_time instanceof \Carbon\Carbon ? $entry->entry_time->format('M d, Y') : $entry->entry_time }}</div>
                                <div style="font-size: 12px; color: #565959;">{{ $entry->entry_time instanceof \Carbon\Carbon ? $entry->entry_time->format('h:i A') : '' }}</div>
                            </td>
                            <td>{{ $entry->notes ?: '-' }}</td>
                            <td>
                                <button class="btn btn-sm btn-danger" onclick="deleteEntry({{ $entry->id }})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($timeEntries->hasPages())
                <div style="padding: 20px; border-top: 1px solid #eee;">
                    {{ $timeEntries->links() }}
                </div>
            @endif
        @else
            <div style="text-align: center; padding: 40px; color: #565959;">
                <i class="fas fa-clock" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                <h3>No time entries found</h3>
                <p>Try adjusting your filter criteria</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteEntry(entryId) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will permanently delete this time entry.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d13212',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/time-entries/${entryId}`,
                method: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') }
            }).done(() => {
                Swal.fire('Deleted!', 'Time entry has been deleted.', 'success').then(() => {
                    location.reload();
                });
            }).fail(() => {
                Swal.fire('Error!', 'Failed to delete time entry.', 'error');
            });
        }
    });
}
</script>
@endpush