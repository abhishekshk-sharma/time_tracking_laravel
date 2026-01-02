@extends('super-admin.layouts.app')

@section('title', 'Schedule Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0"><i class="fas fa-calendar-alt me-2"></i> &nbsp; Schedule Management</h3>
                        <form method="GET" class="d-flex align-items-center gap-2">
                            <select name="month" class="filter-select" onchange="this.form.submit()">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $currentMonth == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endfor
                            </select>
                            <select name="year" class="filter-select" onchange="this.form.submit()">
                                @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                    <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Legend -->
                    <div class="mb-3 p-2 bg-light rounded">
                        <div class="d-flex flex-wrap gap-3">
                            <span><span class="legend-square bg-danger me-1"></span>Holiday</span>
                            <span><span class="legend-square bg-success me-1"></span>Working Day</span>
                            <span><span class="legend-square bg-warning me-1"></span>Weekend</span>
                            <span><span class="legend-square bg-primary me-1"></span>Today</span>
                        </div>
                    </div>

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
                                                
                                                <div class="day-header">
                                                    <span class="day-number">{{ $day['date']->format('j') }}</span>
                                                    @if($day['is_current_month'])
                                                        <div class="day-actions">
                                                            <button class="btn-edit" 
                                                                    onclick="showScheduleAlert('{{ $day['date']->format('Y-m-d') }}', '{{ $day['exception'] ? $day['exception']->type : '' }}', '{{ $day['exception'] ? addslashes($day['exception']->description) : '' }}')" 
                                                                    title="{{ $day['exception'] ? 'Edit' : 'Add' }} Schedule">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            @if($day['exception'])
                                                                <button class="btn-delete" 
                                                                        onclick="deleteScheduleException('{{ $day['date']->format('Y-m-d') }}')" 
                                                                        title="Delete Schedule">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                @if($day['exception'])
                                                    <div class="day-content">
                                                        <span class="exception-badge exception-{{ $day['exception']->type }}">
                                                            {{ ucfirst(str_replace('_', ' ', $day['exception']->type)) }}
                                                        </span>
                                                        @if($day['exception']->description)
                                                            <small class="exception-desc">{{ Str::limit($day['exception']->description, 20) }}</small>
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
    
    <!-- Schedule Exceptions List -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-list me-2"></i> &nbsp; All Schedule Exceptions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($scheduleExceptions as $exception)
                                    <tr>
                                        <td>{{ Carbon\Carbon::parse($exception->exception_date)->format('M d, Y') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $exception->type == 'holiday' ? 'danger' : ($exception->type == 'working_day' ? 'success' : 'warning') }}">
                                                {{ ucfirst(str_replace('_', ' ', $exception->type)) }}
                                            </span>
                                        </td>
                                        <td>{{ $exception->description ?: '-' }}</td>
                                        <td>
                                            @if($exception->superadmin_id)
                                                <span class="badge bg-primary">Super Admin</span>
                                            @elseif($exception->admin_id)
                                                <span class="badge bg-info">Admin</span>
                                            @else
                                                <span class="badge bg-secondary">System</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1" 
                                                    onclick="showScheduleAlert('{{ $exception->exception_date }}', '{{ $exception->type }}', '{{ addslashes($exception->description) }}')">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteScheduleException('{{ $exception->exception_date }}')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-calendar-times fa-2x mb-2"></i><br>
                                            No schedule exceptions found for {{ date('F Y', mktime(0, 0, 0, $currentMonth, 1, $currentYear)) }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<style>
.calendar-table {
    font-size: 0.9rem;
}

.calendar-day {
    height: 100px;
    vertical-align: top;
    position: relative;
    padding: 6px;
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

.day-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.day-number {
    font-weight: bold;
    font-size: 1.1rem;
    color: #333;
}

.day-actions {
    display: flex;
    gap: 2px;
}

.btn-edit, .btn-delete {
    width: 20px;
    height: 20px;
    border: none;
    border-radius: 3px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-edit {
    background: #007bff;
    color: white;
}

.btn-edit:hover {
    background: #0056b3;
    transform: scale(1.1);
}

.btn-delete {
    background: #dc3545;
    color: white;
}

.btn-delete:hover {
    background: #c82333;
    transform: scale(1.1);
}

.day-content {
    margin-top: 4px;
}

.exception-badge {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

.exception-holiday {
    background: #dc3545;
    color: white;
}

.exception-working_day {
    background: #28a745;
    color: white;
}

.exception-weekend {
    background: #ffc107;
    color: #212529;
}

.exception-desc {
    display: block;
    color: #666;
    font-size: 9px;
    line-height: 1.2;
    margin-top: 2px;
}

/* Filter Form Styling */
.filter-select {
    height: 36px;
    padding: 6px 12px;
    border: 1px solid rgba(19, 19, 19, 0.3);
    border-radius: 6px;
    background: rgba(249, 248, 248, 0.1);
    color: rgb(0, 0, 0);
    font-size: 14px;
    min-width: 120px;
    margin-top: 10px;
    appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 8px center;
    background-repeat: no-repeat;
    background-size: 16px 12px;
    padding-right: 32px;
    transition: all 0.2s ease;
}

.filter-select:focus {
    outline: none;
    border-color: rgba(255, 255, 255, 0.6);
    background: rgba(255, 255, 255, 0.15);
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.1);
}

.filter-select option {
    background: #333;
    color: white;
}

/* SweetAlert2 Custom Styling */
.swal2-popup {
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.swal2-title {
    color: #2c3e50;
    font-weight: 600;
}

.swal2-html-container .form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #495057;
    text-align: left;
}

.swal2-html-container .form-control,
.swal2-html-container .form-select {
    width: 100%;
    height: 42px;
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 14px;
    background: #ffffff;
    transition: all 0.15s ease;
    box-sizing: border-box;
}

.swal2-html-container .form-control:focus,
.swal2-html-container .form-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.swal2-html-container .form-control::placeholder {
    color: #9ca3af;
    opacity: 1;
}

.swal2-html-container .form-select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 8px center;
    background-repeat: no-repeat;
    background-size: 16px 12px;
    padding-right: 32px;
    appearance: none;
}

.swal2-html-container .mb-3 {
    margin-bottom: 20px;
    text-align: left;
}

.swal2-confirm {
    background-color: #007bff !important;
    border-radius: 8px !important;
    padding: 10px 25px !important;
    font-weight: 500 !important;
}

.swal2-cancel {
    background-color: #6c757d !important;
    border-radius: 8px !important;
    padding: 10px 25px !important;
    font-weight: 500 !important;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function showScheduleAlert(date, type, description) {
    Swal.fire({
        title: '<i class="fas fa-calendar-alt"></i> Schedule Exception',
        html: `
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-calendar me-2"></i> Date</label>
                <input type="text" class="form-control" value="${new Date(date).toLocaleDateString()}" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-tags me-2"></i> Type</label>
                <select class="form-select" id="swal-type" required>
                    <option value="">Select Type</option>
                    <option value="holiday" ${type === 'holiday' ? 'selected' : ''}>🏖️ Holiday</option>
                    <option value="working_day" ${type === 'working_day' ? 'selected' : ''}>💼 Working Day</option>
                    <option value="weekend" ${type === 'weekend' ? 'selected' : ''}>🏠 Weekend</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-comment me-2"></i> Description</label>
                <input type="text" class="form-control" id="swal-description" placeholder="Optional description" value="${description || ''}">
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-save me-2"></i>Save',
        cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
        buttonsStyling: false,
        customClass: {
            confirmButton: 'swal2-confirm',
            cancelButton: 'swal2-cancel'
        },
        width: '450px',
        preConfirm: () => {
            const type = document.getElementById('swal-type').value;
            const description = document.getElementById('swal-description').value;
            
            if (!type) {
                Swal.showValidationMessage('<i class="fas fa-exclamation-triangle"></i> Please select a type');
                return false;
            }
            
            return { date, type, description };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            saveScheduleException(result.value);
        }
    });
}

function saveScheduleException(data) {
    fetch('{{ route("super-admin.schedule.exception.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success!', 'Schedule exception saved successfully.', 'success').then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Error!', data.error || 'Unknown error', 'error');
        }
    })
    .catch(error => {
        Swal.fire('Error!', 'Error saving schedule exception', 'error');
    });
}

function deleteScheduleException(date) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You want to delete this schedule exception?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('{{ route("super-admin.schedule.exception.delete") }}', {
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
                    Swal.fire('Deleted!', 'Schedule exception has been deleted.', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', 'Error deleting schedule exception', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Error deleting schedule exception', 'error');
            });
        }
    });
}
</script>
@endsection