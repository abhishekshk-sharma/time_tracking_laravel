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
                                    <span class="badge badge-success">{{ $dayEntries['punch_in']->entry_time->format('h:i A') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($dayEntries->has('lunch_start'))
                                    <span class="badge badge-warning">{{ $dayEntries['lunch_start']->entry_time->format('h:i A') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($dayEntries->has('lunch_end'))
                                    <span class="badge badge-warning">{{ $dayEntries['lunch_end']->entry_time->format('h:i A') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($dayEntries->has('punch_out'))
                                    <span class="badge badge-danger">{{ $dayEntries['punch_out']->entry_time->format('h:i A') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($dayEntries->has('holiday'))
                                    <span class="badge badge-secondary">{{ $dayEntries['holiday']->notes }}</span>
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