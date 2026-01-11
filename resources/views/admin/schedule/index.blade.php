@extends('admin.layouts.app')

@section('title', 'Schedule')

@section('content')
<div class="calendar-container">
    
    <div class="calendar-toolbar">
        <div class="toolbar-left">
            <h1 class="calendar-title">Schedule</h1>
            <div class="navigation-group">
                <button class="btn btn-outline-secondary btn-today" onclick="goToToday()">Today</button>
                <div class="nav-arrows">
                    <button class="btn-icon" onclick="changeMonth(-1)"><i class="fas fa-chevron-left"></i></button>
                    <button class="btn-icon" onclick="changeMonth(1)"><i class="fas fa-chevron-right"></i></button>
                </div>
                <h2 class="current-month">
                    {{ date('F Y', mktime(0, 0, 0, $currentMonth, 1, $currentYear)) }}
                </h2>
            </div>
        </div>
        
        <div class="toolbar-right">
            <form id="calendar-form" method="GET" class="d-none">
                <input type="hidden" name="month" id="f-month" value="{{ $currentMonth }}">
                <input type="hidden" name="year" id="f-year" value="{{ $currentYear }}">
            </form>

            <div class="view-switcher">
                <button class="btn-view active">Month</button>
                <button class="btn-view" onclick="document.getElementById('exceptions-list').scrollIntoView({behavior: 'smooth'})">List</button>
            </div>
        </div>
    </div>

    <div class="calendar-wrapper">
        <table class="google-calendar-table">
            <thead>
                <tr>
                    <th>MON</th>
                    <th>TUE</th>
                    <th>WED</th>
                    <th>THU</th>
                    <th>FRI</th>
                    <th>SAT</th>
                    <th>SUN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($calendar as $week)
                    <tr>
                        @foreach($week as $day)
                            <td class="calendar-cell {{ !$day['is_current_month'] ? 'not-current-month' : '' }} 
                                       {{ $day['date']->isToday() ? 'is-today' : '' }}"
                                onclick="if(!event.target.closest('.event-pill') && !event.target.closest('.btn-icon-mini')) showScheduleAlert('{{ $day['date']->format('Y-m-d') }}', '', '')">
                                
                                <div class="cell-header">
                                    <span class="day-number">{{ $day['date']->format('j') }}</span>
                                </div>

                                <div class="cell-content">
                                    @if($day['exception'] && $day['exception'] !== false)
                                        @foreach ($day['exception'] as $exception)

                                            @if ($exception->superadmin_id !== null)
                                                    <div class="event-pill event-{{ $exception['type'] }}">
                                                    <div class="event-title">{{ ucfirst(str_replace('_', ' ', $exception['type'])) }}</div>
                                                    <div class="event-creator" style="font-size: 9px; opacity: 0.8;">
                                                        @if($exception['superadmin_id'])
                                                            Super Admin
                                                        @elseif($exception['admin_id'])
                                                            Admin
                                                        @else
                                                            System
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <div class="event-pill event-{{ $exception['type'] }}" 
                                                onclick="event.stopPropagation(); showScheduleAlert('{{ $day['date']->format('Y-m-d') }}', '{{ $exception['type'] }}', '{{ addslashes($exception['description']) }}')">
                                                    <div class="event-title">{{ ucfirst(str_replace('_', ' ', $exception['type'])) }}</div>
                                                    <div class="event-creator" style="font-size: 9px; opacity: 0.8;">
                                                        @if($exception['superadmin_id'])
                                                            Super Admin
                                                        @elseif($exception['admin_id'])
                                                            Admin
                                                        @else
                                                            System
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                            
                                        @endforeach
                                    @else
                                        
                                        @php
                                            // Show default weekend based on system settings
                                            $dayOfWeek = $day['date']->dayOfWeek;
                                            $isDefaultWeekend = false;
                                            $weekendType = '';
                                            
                                            // Get weekend policy from system settings
                                            $weekendPolicySetting = \App\Models\SystemSetting::where('setting_key', 'weekend_policy')->first();
                                            $weekendPolicy = $weekendPolicySetting ? json_decode($weekendPolicySetting->setting_value, true) : [
                                                'recurring_days' => [0], // Default: Sunday only
                                                'specific_pattern' => []
                                            ];
                                            
                                            // Check recurring days
                                            if (in_array($dayOfWeek, $weekendPolicy['recurring_days'])) {
                                                $isDefaultWeekend = true;
                                                $weekendType = $dayOfWeek === 0 ? 'Sunday' : 'Saturday';
                                            }
                                            
                                            // Check specific patterns (e.g., 2nd/4th Saturday)
                                            if (isset($weekendPolicy['specific_pattern'][$dayOfWeek])) {
                                                $weekOfMonth = ceil($day['date']->day / 7);
                                                if (in_array($weekOfMonth, $weekendPolicy['specific_pattern'][$dayOfWeek])) {
                                                    $isDefaultWeekend = true;
                                                    $ordinals = ['', 'First', 'Second', 'Third', 'Fourth', 'Fifth'];
                                                    $weekendType = ($ordinals[$weekOfMonth] ?? $weekOfMonth . 'th') . ' ' . $day['date']->format('l');
                                                }
                                            }
                                        @endphp
                                        
                                        @if($isDefaultWeekend)
                                            <div class="event-pill event-weekend" style="opacity: 0.6;">
                                                <div class="event-title">{{ $weekendType }}</div>
                                                <div class="event-creator" style="font-size: 9px; opacity: 0.8;">System</div>
                                            </div>
                                        @endif
                                        @endif
                                </div>

                                <div class="cell-actions">
                                    @if($day['exception'])
                                        @foreach ($day['exception'] as $exception)
                                            @if ($exception->superadmin_id !== null)
                                            {{-- <button >
                                               
                                            </button>
                                            <button >

                                            </button> --}}

                                            @elseif($exception->admin_id !== null)
                                                <button class="btn-icon-mini text-danger" 
                                            onclick="event.stopPropagation(); deleteScheduleException('{{ $exception->id }}')"
                                            title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            <button class="btn-icon-mini text-primary" title="Edit/Add">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            @endif
                                        @endforeach

                                        
                                    @endif
                                    
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="calendar-legend">
        <div class="legend-item"><span class="dot dot-working"></span> Working Day</div>
        <div class="legend-item"><span class="dot dot-holiday"></span> Holiday</div>
        <div class="legend-item"><span class="dot dot-weekend"></span> Weekend</div>
    </div>
</div>

<div class="container-fluid mt-5" id="exceptions-list">
    <div class="card border-0 shadow-none">
        <div class="card-header bg-transparent border-0 ps-0">
            <h4 class="google-font text-dark">All Exceptions</h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table google-list-table">
                    <thead>
                        <tr>
                            <th>DATE</th>
                            <th>TYPE</th>
                            <th>DESCRIPTION</th>
                            <th>CREATED BY</th>
                            <th class="text-end">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($scheduleExceptions as $exception)
                            <tr>
                                <td class="fw-500">{{ Carbon\Carbon::parse($exception->exception_date)->format('D, M d, Y') }}</td>
                                <td>
                                    <span class="status-chip chip-{{ $exception->type }}">
                                        {{ ucfirst(str_replace('_', ' ', $exception->type)) }}
                                    </span>
                                </td>
                                <td class="text-muted">{{ $exception->description ?? 'No description' }}</td>
                                <td>
                                    @if($exception->superadmin_id) <span class="creator-badge">Super Admin</span>
                                    @elseif($exception->admin_id) <span class="creator-badge">Admin</span>
                                    @else <span class="creator-badge">System</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if ($exception->superadmin_id !== null)
                                        <span class='badge text-bg-warning p-2' style="font-size: .8rem; cursor: not-allowed;">Not Authorized</span>
                                    @else
                                        <button class="btn-icon-row" 
                                                onclick="showScheduleAlert('{{ $exception->exception_date }}', '{{ $exception->type }}', '{{ addslashes($exception->description) }}')">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button class="btn-icon-row text-danger" 
                                                onclick="deleteScheduleException('{{ $exception->id }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        
                                    @endif

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-calendar-check fa-3x mb-3 text-light-gray"></i>
                                    <p>No exceptions found for this month</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    /* --- Google Calendar Variables --- */
    :root {
        --gc-border: #dadce0;
        --gc-text: #3c4043;
        --gc-text-light: #70757a;
        --gc-blue: #1a73e8;
        --gc-blue-bg: #e8f0fe;
        --gc-green: #188038;
        --gc-green-bg: #ceead6;
        --gc-red: #d93025;
        --gc-red-bg: #fad2cf;
        --gc-yellow: #f6bf26;
        --gc-yellow-bg: #ffeecf;
        --gc-hover: #f1f3f4;
    }

    /* --- Container & Toolbar --- */
    .calendar-container {
        background: #fff;
        border-radius: 8px;
        /* box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15); */
        /* Google Calendar is flat on web, let's keep it clean */
        border: 1px solid var(--gc-border);
        overflow: hidden;
    }

    .calendar-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 24px;
        border-bottom: 1px solid var(--gc-border);
    }

    .toolbar-left {
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .calendar-title {
        font-family: 'Google Sans', sans-serif;
        font-size: 22px;
        color: #5f6368;
        font-weight: 400;
        margin: 0;
        display: none; /* Hidden on mobile/small screens usually */
    }

    @media (min-width: 768px) { .calendar-title { display: block; } }

    .navigation-group {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .btn-today {
        border: 1px solid var(--gc-border);
        color: var(--gc-text);
        font-weight: 500;
        border-radius: 4px;
        padding: 6px 16px;
        background: #fff;
    }
    .btn-today:hover { 
        background: var(--gc-hover); 
        color: rgb(3, 3, 50)
    }

    .nav-arrows { display: flex; gap: 8px; }

    .btn-icon {
        background: transparent;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        color: #5f6368;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-icon:hover { background: rgba(95,99,104,0.08); }

    .current-month {
        font-family: 'Google Sans', sans-serif;
        font-size: 22px;
        color: #3c4043;
        margin: 0;
        font-weight: 400;
        min-width: 180px;
    }

    .view-switcher {
        display: flex;
        border: 1px solid var(--gc-border);
        border-radius: 4px;
        overflow: hidden;
    }

    .btn-view {
        border: none;
        background: #fff;
        padding: 6px 16px;
        font-size: 14px;
        color: #5f6368;
        font-weight: 500;
    }
    .btn-view.active {
        background: #e8f0fe;
        color: var(--gc-blue);
    }
    .btn-view:hover:not(.active) { background: var(--gc-hover); }

    /* --- The Calendar Table --- */
    .calendar-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .google-calendar-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed; /* Ensures equal width columns */
    }

    .google-calendar-table th {
        text-align: center;
        font-size: 11px;
        font-weight: 600;
        color: #70757a;
        padding: 10px 0;
        border-bottom: 1px solid var(--gc-border);
        border-right: 1px solid var(--gc-border);
    }
    .google-calendar-table th:last-child { border-right: none; }

    .calendar-cell {
        height: 120px; /* Fixed height for consistent grid */
        border-right: 1px solid var(--gc-border);
        border-bottom: 1px solid var(--gc-border);
        vertical-align: top;
        padding: 8px;
        position: relative;
        transition: background 0.1s;
        cursor: pointer;
    }
    
    .calendar-cell:last-child { border-right: none; }
    .calendar-cell:hover { background-color: #f8f9fa; }
    .calendar-cell.not-current-month { background-color: #fcfcfc; color: #b3b3b3; }
    .calendar-cell.not-current-month .day-number { color: #b3b3b3; }

    /* Date Numbers */
    .cell-header { text-align: center; margin-bottom: 8px; }
    
    .day-number {
        font-size: 12px;
        font-weight: 500;
        color: #3c4043;
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .calendar-cell.is-today .day-number {
        background-color: var(--gc-blue);
        color: #fff;
    }

    /* Hover Actions (Edit/Delete) */
    .cell-actions {
        position: absolute;
        bottom: 4px;
        right: 4px;
        display: none; /* Hidden by default */
    }
    .calendar-cell:hover .cell-actions { display: flex; gap: 4px; }

    .btn-icon-mini {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: none;
        background: #fff;
        box-shadow: 0 1px 2px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: #5f6368;
        cursor: pointer;
    }
    .btn-icon-mini:hover { transform: scale(1.1); }

    /* Events (Exceptions) */
    .cell-content {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .event-pill {
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 11px;
        font-weight: 500;
        cursor: pointer;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        transition: opacity 0.2s;
    }
    .event-pill:hover { opacity: 0.8; }

    .event-working_day { background-color: var(--gc-green-bg); color: var(--gc-green); }
    .event-holiday { background-color: var(--gc-red-bg); color: var(--gc-red); }
    .event-weekend { background-color: var(--gc-yellow-bg); color: #e37400; }

    /* Legend */
    .calendar-legend {
        display: flex;
        padding: 12px 24px;
        gap: 20px;
        border-top: 1px solid var(--gc-border);
        font-size: 12px;
        color: #5f6368;
    }
    .dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 6px; }
    .dot-working { background: var(--gc-green); }
    .dot-holiday { background: var(--gc-red); }
    .dot-weekend { background: #f6bf26; }

    /* List Table */
    .google-list-table th {
        font-size: 11px;
        color: #5f6368;
        font-weight: 600;
        border-bottom: 1px solid var(--gc-border);
        padding: 12px 16px;
    }
    .google-list-table td {
        vertical-align: middle;
        padding: 12px 16px;
        border-bottom: 1px solid #f1f3f4;
        font-size: 14px;
        color: #3c4043;
    }
    .status-chip {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    .chip-working_day { background: #e6f4ea; color: #137333; }
    .chip-holiday { background: #fce8e6; color: #c5221f; }
    .chip-weekend { background: #fef7e0; color: #b06000; }
    
    .creator-badge {
        background: #00aaff;
        color: #ffffff;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
    }
    
    .btn-icon-row {
        background: transparent;
        border: none;
        color: #5f6368;
        padding: 4px 8px;
        cursor: pointer;
        border-radius: 4px;
    }
    .btn-icon-row:hover { background: #f1f3f4; color: #1a73e8; }
</style>

<script>
    // Navigation Logic
    function changeMonth(offset) {
        let month = parseInt(document.getElementById('f-month').value);
        let year = parseInt(document.getElementById('f-year').value);
        
        month += offset;
        if (month > 12) { month = 1; year++; }
        if (month < 1) { month = 12; year--; }
        
        updateFormAndSubmit(month, year);
    }

    function goToToday() {
        const d = new Date();
        updateFormAndSubmit(d.getMonth() + 1, d.getFullYear());
    }

    function updateFormAndSubmit(m, y) {
        document.getElementById('f-month').value = m;
        document.getElementById('f-year').value = y;
        document.getElementById('calendar-form').submit();
    }

    // Reuse your existing SweetAlert functions
    function showScheduleAlert(date, type, description) {
        Swal.fire({
            title: `<span style="font-family:'Google Sans'; color:#3c4043; font-size:20px;">${type ? 'Edit' : 'Add'} Schedule</span>`,
            html: `
                <div class="text-start mt-2">
                    <label class="form-label small text-muted">Date</label>
                    <input type="text" class="form-control mb-3" value="${new Date(date).toLocaleDateString(undefined, {weekday:'short', month:'short', day:'numeric'})}" disabled 
                           style="background:#f1f3f4; border:none; font-weight:500; color:#3c4043;">
                    
                    <label class="form-label small text-muted">Exception Type</label>
                    <div class="d-flex gap-2 mb-3">
                        <input type="radio" class="btn-check" name="swal-type" id="type-holiday" value="holiday" ${type === 'holiday' ? 'checked' : ''}>
                        <label class="btn btn-outline-danger flex-fill" for="type-holiday">Holiday</label>

                        <input type="radio" class="btn-check" name="swal-type" id="type-working" value="working_day" ${type === 'working_day' ? 'checked' : ''}>
                        <label class="btn btn-outline-success flex-fill" for="type-working">Working</label>
                        
                        <input type="radio" class="btn-check" name="swal-type" id="type-weekend" value="weekend" ${type === 'weekend' ? 'checked' : ''}>
                        <label class="btn btn-outline-warning flex-fill" for="type-weekend">Weekend</label>
                    </div>

                    <label class="form-label small text-muted">Description (Optional)</label>
                    <textarea id="swal-description" class="form-control" rows="2" placeholder="e.g., National Holiday">${description || ''}</textarea>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#1a73e8',
            confirmButtonText: 'Save',
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'rounded-4 border-0',
                confirmButton: 'px-4 rounded-pill font-weight-bold',
                cancelButton: 'px-4 rounded-pill'
            },
            preConfirm: () => {
                const selected = document.querySelector('input[name="swal-type"]:checked');
                if (!selected) {
                    Swal.showValidationMessage('Please select a type');
                    return false;
                }
                return { date: date, type: selected.value, description: document.getElementById('swal-description').value };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                saveScheduleException(result.value);
            }
        });
    }

    // Keep your existing save/delete fetch logic here (saveScheduleException, deleteScheduleException)
    function saveScheduleException(data) {
        fetch('{{ route("admin.schedule.exception.store") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(d => {
            if(d.success) location.reload();
            else Swal.fire('Error', d.error, 'error');
        });
    }

    function deleteScheduleException(id) {
        Swal.fire({
            title: 'Delete Exception?',
            text: "This will revert the day to default settings.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d93025',
            confirmButtonText: 'Delete',
            customClass: { popup: 'rounded-4' }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('{{ route("admin.schedule.exception.delete") }}', {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                }).then(r => r.json()).then(d => {
                    if(d.success) location.reload();
                    else Swal.fire('Error', 'Failed to delete', 'error');
                });
            }
        });
    }
</script>
@endsection