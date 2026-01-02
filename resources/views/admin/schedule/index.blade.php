@extends('admin.layouts.app')

@section('title', 'Schedule Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0"><i class="fas fa-calendar-alt me-2"></i> &nbsp; Schedule Management</h3>
                        <form method="GET" class="d-flex align-items-center">
                            <select name="month" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $currentMonth == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endfor
                            </select>
                            <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                                @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                    <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Legend -->
                    {{-- <div class="mb-3 p-2 bg-light rounded">
                        <div class="d-flex flex-wrap gap-3">
                            <span><span class="legend-square bg-danger me-1"></span>Holiday</span>
                            <span><span class="legend-square bg-success me-1"></span>Working Day</span>
                            <span><span class="legend-square bg-warning me-1"></span>Weekend</span>
                            <span><span class="legend-square bg-primary me-1"></span>Today</span>
                        </div>
                    </div> --}}

                    <!-- Calendar -->
                    <div class="table-responsive">
                        <table class="table table-bordered calendar-table">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center">Sun</th>
                                    <th class="text-center">Mon</th>
                                    <th class="text-center">Tue</th>
                                    <th class="text-center">Wed</th>
                                    <th class="text-center">Thu</th>
                                    <th class="text-center">Fri</th>
                                    <th class="text-center">Sat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($calendar as $week)
                                    <tr>
                                        @foreach($week as $day)
                                            <td class="calendar-day {{ !$day['is_current_month'] ? 'text-muted bg-light' : '' }} 
                                                {{ $day['date']->isToday() ? 'today' : '' }}
                                                {{ $day['exception'] ? 'exception-' . $day['exception']->type : '' }}" 
                                                data-date="{{ $day['date']->format('Y-m-d') }}">
                                                
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <span class="day-number">{{ $day['date']->format('j') }}</span>
                                                    @if($day['exception'])
                                                        <span class="badge bg-{{ $day['exception']->type == 'holiday' ? 'danger' : ($day['exception']->type == 'working_day' ? 'success' : 'warning') }}">
                                                            {{ ucfirst(str_replace('_', ' ', $day['exception']->type)) }}
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                @if($day['exception'] && $day['exception']->description)
                                                    <small class="text-muted d-block mb-2">{{ Str::limit($day['exception']->description, 25) }}</small>
                                                @endif
                                                
                                                @if($day['is_current_month'])
                                                    <div class="btn-group-vertical w-100">
                                                        <button class="btn btn-sm btn-outline-primary mb-1" 
                                                                data-bs-toggle="modal" data-bs-target="#scheduleModal"
                                                                onclick="showScheduleModal('{{ $day['date']->format('Y-m-d') }}', '{{ $day['exception'] ? $day['exception']->type : '' }}', '{{ $day['exception'] ? addslashes($day['exception']->description) : '' }}')">
                                                            {{ $day['exception'] ? 'Edit' : 'Set' }}
                                                        </button>
                                                        @if($day['exception'])
                                                            <button class="btn btn-sm btn-outline-danger" 
                                                                    onclick="deleteScheduleException('{{ $day['date']->format('Y-m-d') }}')">
                                                                Delete
                                                            </button>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule Exception</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm">
                    <input type="hidden" id="scheduleDate" name="date">
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="text" class="form-control" id="scheduleDateDisplay" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-select" id="scheduleType" name="type" required>
                            <option value="">Select Type</option>
                            <option value="holiday">Holiday</option>
                            <option value="working_day">Working Day</option>
                            <option value="weekend">Weekend</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" id="scheduleDescription" name="description" placeholder="Optional description">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveScheduleException()">Save</button>
            </div>
        </div>
    </div>
</div>

<style>
.calendar-table {
    font-size: 0.9rem;
}

.calendar-day {
    height: 120px;
    vertical-align: top;
    position: relative;
    padding: 8px;
}

.calendar-day.today {
    background-color: #e3f2fd !important;
    border: 2px solid #2196f3;
}

.calendar-day.exception-holiday {
    background-color: #ffebee;
    border-left: 4px solid #f44336;
}

.calendar-day.exception-working_day {
    background-color: #e8f5e8;
    border-left: 4px solid #4caf50;
}

.calendar-day.exception-weekend {
    background-color: #fff3cd;
    border-left: 4px solid #ffc107;
}

.legend-square {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 2px;
    vertical-align: middle;
}

.day-number {
    font-weight: bold;
    font-size: 1.1rem;
}
</style>

<script>
function showScheduleModal(date, type, description) {
    document.getElementById('scheduleDate').value = date;
    document.getElementById('scheduleDateDisplay').value = new Date(date).toLocaleDateString();
    document.getElementById('scheduleType').value = type;
    document.getElementById('scheduleDescription').value = description || '';
}

function saveScheduleException() {
    const form = document.getElementById('scheduleForm');
    const formData = new FormData(form);
    
    fetch('{{ route("admin.schedule.exception.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            date: formData.get('date'),
            type: formData.get('type'),
            description: formData.get('description')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving schedule exception');
    });
}

function deleteScheduleException(date) {
    if (confirm('Are you sure you want to delete this schedule exception?')) {
        fetch('{{ route("admin.schedule.exception.delete") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ date: date })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting schedule exception');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting schedule exception');
        });
    }
}
</script>
@endsection