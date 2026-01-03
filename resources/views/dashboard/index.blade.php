@extends('layouts.user')

@section('page-title', 'Welcome Back!')
@section('page-subtitle', 'Track your time and manage your workday efficiently')

@push('page-styles')
<style>
    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .current-time-widget {
        text-align: right;
    }
    
    .time-display {
        font-size: 2rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-family: 'Courier New', monospace;
    }
    
    .date-display {
        font-size: 0.875rem;
        color: var(--gray-500);
        font-weight: 500;
    }
    
    .card-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(99, 102, 241, 0.1));
        color: var(--primary);
    }
    
    .card-trend {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.75rem;
        font-size: 0.75rem;
        color: var(--gray-500);
    }
    
    .card-trend i {
        font-size: 0.625rem;
    }
    

    .recent-activity-card{
        margin-top: 2rem;
    }
    
    .clock-widget {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7));
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: var(--radius-xl);
        padding: 1.5rem;
        text-align: center;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-md);
    }
    
    .clock-display {
        font-size: 3rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-family: 'Courier New', monospace;
        margin-bottom: 0.5rem;
    }
    
    .clock-date {
        font-size: 1rem;
        color: var(--gray-600);
        margin-bottom: 1rem;
    }
    
    .time-format-toggle {
        background: var(--primary);
        color: white;
        border: none;
        border-radius: var(--radius);
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        cursor: pointer;
        transition: var(--transition);
    }
    
    .time-format-toggle:hover {
        background: var(--secondary);
        transform: translateY(-1px);
    }
    .tracking-controls {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .control-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .tracking-btn {
        flex-direction: column;
        padding: 1.5rem;
        min-height: 120px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    
    .tracking-btn:disabled,
    .tracking-btn.disabled {
        opacity: 0.4;
        cursor: not-allowed;
        transform: none !important;
    }
    
    .tracking-btn:disabled:hover,
    .tracking-btn.disabled:hover {
        transform: none !important;
        box-shadow: none !important;
    }
    
    .btn-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        z-index: 2;
        position: relative;
    }
    
    .btn-content i {
        font-size: 2rem;
        margin: 0;
    }
    
    .btn-content span {
        font-size: 1rem;
        font-weight: 700;
    }
    
    .btn-description {
        font-size: 0.75rem;
        opacity: 0.8;
        margin-top: 0.5rem;
        font-weight: 400;
        z-index: 2;
        position: relative;
    }
    
    .btn-lg {
        font-size: 1rem;
    }
    
    .card-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .btn-icon {
        width: 36px;
        height: 36px;
        border-radius: var(--radius);
        border: 1px solid var(--gray-300);
        background: white;
        color: var(--gray-600);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition);
    }
    
    .btn-icon:hover {
        background: var(--gray-50);
        color: var(--primary);
        border-color: var(--primary);
    }
    
    .activity-timeline {
        min-height: 200px;
        position: relative;
    }
    
    .activity-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 200px;
        color: var(--gray-400);
        text-align: center;
    }
    
    .activity-placeholder i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    .activity-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .activity-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid var(--gray-200);
        transition: var(--transition);
    }
    
    .activity-item:hover {
        background: var(--gray-50);
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    
    .activity-details {
        flex: 1;
    }
    
    .activity-name {
        font-weight: 600;
        color: var(--gray-900);
        text-transform: capitalize;
        margin-bottom: 0.25rem;
    }
    
    .activity-time {
        font-size: 0.875rem;
        color: var(--gray-500);
    }

    @media (max-width: 1024px) {
        .summary-grid {
            margin-top: 2rem;
            grid-template-columns: repeat(2, 1fr);
        }
        
        .time-display {
            font-size: 1.5rem;
        }
    }
    
    @media (max-width: 768px) {
        .header-content {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }
        
        .current-time-widget {
            text-align: center;
        }
        
        .summary-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        
        }
        
        .control-group {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }
        
        .tracking-btn {
            min-height: 100px;
            padding: 1rem;
        }
        
        .btn-content i {
            font-size: 1.5rem;
        }
        
        .btn-content span {
            font-size: 0.875rem;
        }
        
        .btn-description {
            font-size: 0.625rem;
        }
        
        .page-title {
            font-size: 1.5rem;
        }
        
        .time-display {
            font-size: 1.25rem;
        }
        
        .card {
            padding: 1rem;
        }
        
        .card-header {
            margin-bottom: 1rem;
        }
        
        .card-title {
            font-size: 1rem;
        }
    }
    
    @media (max-width: 480px) {
        .summary-card {
            padding: 1rem;
        }
        
        .card-icon {
            width: 40px;
            height: 40px;
            font-size: 1.25rem;
        }
        
        .tracking-btn {
            min-height: 80px;
            padding: 0.75rem;
        }
        
        .btn-content i {
            font-size: 1.25rem;
        }
    }
</style>
@endpush

@section('page-content')

@if($isHalfDay)
<div class="alert alert-info" style="margin-bottom: 2rem; padding: 1rem 1.5rem; background: linear-gradient(135deg, #e3f2fd, #bbdefb); border: 1px solid #2196f3; border-radius: 8px; color: #1565c0;">
    <i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i>
    <strong>Today is your Half Day</strong> - Work hour for today is {{ $halfDayTime }}
</div>
@endif

<div class="clock-widget">
    <div class="clock-display" id="clockTime">--:--</div>
    <div class="clock-date" id="clockDate">Loading...</div>
    <button class="time-format-toggle" id="timeFormatToggle">12H</button>
</div>

<div class="summary-grid">
    <div class="summary-card work-card">
        <div class="card-icon">
            <i class="fas fa-clock"></i>
        </div>
        <h3>Work Time</h3>
        <p id="workTime">Loading...</p>
        <div class="card-trend">
            <i class="fas fa-arrow-up"></i>
            <span>Today's progress</span>
        </div>
    </div>
    <div class="summary-card lunch-card">
        <div class="card-icon">
            <i class="fas fa-utensils"></i>
        </div>
        <h3>Lunch Time</h3>
        <p id="lunchTime">Loading...</p>
        <div class="card-trend">
            <i class="fas fa-minus"></i>
            <span>Break duration</span>
        </div>
    </div>
    <div class="summary-card status-card">
        <div class="card-icon">
            <i class="fas fa-user-check"></i>
        </div>
        <h3>Status</h3>
        <p id="status">Loading...</p>
        <div class="card-trend">
            <i class="fas fa-circle"></i>
            <span id="statusIndicator">Checking status...</span>
        </div>
    </div>
</div>


<div class="card time-tracking-card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-fingerprint"></i>
            Time Tracking
        </h2>
        <div class="card-actions">
            <button class="btn-icon" id="refreshData" title="Refresh Data">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>
    
    <div class="tracking-controls">
        <div class="control-group">
            <button id="punchInBtn" class="btn btn-success btn-lg tracking-btn">
                <div class="btn-content">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Punch In</span>
                </div>
                <div class="btn-description">Start workday</div>
            </button>
            <button id="lunchStartBtn" class="btn btn-warning btn-lg tracking-btn">
                <div class="btn-content">
                    <i class="fas fa-utensils"></i>
                    <span>Lunch Start</span>
                </div>
                <div class="btn-description">Begin break</div>
            </button>
        </div>
        
        <div class="control-group">
            
            <button id="lunchEndBtn" class="btn btn-warning btn-lg tracking-btn">
                <div class="btn-content">
                    <i class="fas fa-coffee"></i>
                    <span>Lunch End</span>
                </div>
                <div class="btn-description">Resume work</div>
            </button>
            <button id="punchOutBtn" class="btn btn-danger btn-lg tracking-btn">
                <div class="btn-content">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Punch Out</span>
                </div>
                <div class="btn-description">End workday</div>
            </button>
        </div>
    </div>
</div>

<div class="card recent-activity-card" >
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-activity"></i>
            Today's Activity
        </h2>
    </div>
    
    <div class="activity-timeline" id="activityTimeline">
        <div class="activity-placeholder">
            <i class="fas fa-clock"></i>
            <p>No activity recorded yet today</p>
        </div>
    </div>
</div>




@endsection

@push('page-scripts')
<script>
$(document).ready(function() {
    let is24Hour = true; // Default to 24-hour format
    
    // Update clock time
    function updateClock() {
        const now = new Date();
        // Convert to IST (UTC+5:30)
        const istOffset = 5.5 * 60 * 60 * 1000;
        const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
        const istTime = new Date(utc + istOffset);
        
        const timeString = istTime.toLocaleTimeString('en-IN', {
            hour12: !is24Hour,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            timeZone: 'Asia/Kolkata'
        });
        
        const dateString = istTime.toLocaleDateString('en-IN', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            timeZone: 'Asia/Kolkata'
        });
        
        $('#clockTime').text(timeString);
        $('#clockDate').text(dateString);
    }
    
    // Time format toggle
    $('#timeFormatToggle').click(function() {
        is24Hour = !is24Hour;
        $(this).text(is24Hour ? '12H' : '24H');
        updateClock();
    });
    
    updateClock();
    setInterval(updateClock, 1000);
    
    // Load initial data
    loadTimeData();
    loadActivityData();
    updateButtonStates();
    
    // Refresh data functionality
    $('#refreshData').click(function() {
        $(this).find('i').addClass('fa-spin');
        loadTimeData();
        loadActivityData();
        updateButtonStates();
        setTimeout(() => {
            $(this).find('i').removeClass('fa-spin');
        }, 1000);
    });
    
    // Time tracking button handlers
    $('#punchInBtn').click(function() {
        handleTimeAction('punch_in');
    });
    
    $('#punchOutBtn').click(function() {
        handleTimeAction('punch_out');
    });
    
    $('#lunchStartBtn').click(function() {
        handleTimeAction('lunch_start');
    });
    
    $('#lunchEndBtn').click(function() {
        handleTimeAction('lunch_end');
    });
    
    let isProcessing = false; // Prevent multiple simultaneous requests
    
    function handleTimeAction(action) {
        // Prevent multiple clicks during processing
        if (isProcessing) {
            return;
        }
        
        isProcessing = true;
        
        // Disable the clicked button immediately
        const clickedBtn = $('#' + action.replace('_', '') + 'Btn');
        clickedBtn.prop('disabled', true).addClass('processing');
        
        $.ajax({
            url: '{{ route("api.time.action") }}',
            method: 'POST',
            data: {
                click: action,
                _token: '{{ csrf_token() }}'
            },
            timeout: 30000, // 30 second timeout
            success: function(response) {
                console.log('Time action response:', response);
                if (response === 'nothing') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: action.replace('_', ' ').toUpperCase() + ' recorded successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadTimeData();
                    loadActivityData();
                    updateButtonStates();
                } else if (response === 'duplicate_action') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Already Recorded!',
                        text: 'This action has already been recorded today.',
                    });
                } else if (response === 'work_complete') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Work Day Complete!',
                        text: 'You have completed your required work hours for today.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                    loadTimeData();
                    loadActivityData();
                    updateButtonStates();
                } else if (response === 'invalid_sequence') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Action!',
                        text: 'Please follow the correct sequence of actions.',
                    });
                } else if (response === 'must_punch_in_first') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Punch In Required!',
                        text: 'Please punch in first before other actions.',
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Unable to record ' + action.replace('_', ' ') + '. Please try again.',
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Time action error:', error);
                if (status === 'timeout') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Network Timeout!',
                        text: 'Request timed out. Please check your connection and try again.',
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error!',
                        text: 'Connection failed. Please check your network and try again.',
                    });
                }
            },
            complete: function() {
                // Re-enable processing and update button states
                isProcessing = false;
                clickedBtn.removeClass('processing');
                updateButtonStates(); // This will properly set button states
            }
        });
    }
    
    function loadTimeData() {
        $.ajax({
            url: '{{ route("api.time.worked") }}',
            method: 'POST',
            data: {
                click: 'timeWorked',
                _token: '{{ csrf_token() }}'
            },
            success: function(data) {
                console.log('Time data received:', data); // Debug log
                
                if (data) {
                    console.log('Updating DOM elements...');
                    
                    // Update work time - use network or total_hours as fallback
                    let workTimeValue = data.network || data.total_hours || '0H 0M';
                    console.log('Work time value:', workTimeValue);
                    
                    if (workTimeValue === '0H 0M' || workTimeValue === '00:00:00' || workTimeValue === '') {
                        $('#workTime').text(workTimeValue).css('color', '#666');
                        console.log('Set work time to: No work logged');
                    } else {
                        $('#workTime').text(workTimeValue).css('color', '#333');
                        console.log('Set work time to:', workTimeValue);
                    }
                    
                    // Update lunch time
                    let lunchTimeValue = data.totalLunchByemp || '0M';
                    console.log('Lunch time value:', lunchTimeValue);
                    
                    if (lunchTimeValue === '0H 0M' || lunchTimeValue === '0M' || lunchTimeValue === '') {
                        $('#lunchTime').text(lunchTimeValue).css('color', '#666');
                        console.log('Set lunch time to: No lunch taken');
                    } else {
                        $('#lunchTime').text(lunchTimeValue).css('color', '#333');
                        console.log('Set lunch time to:', lunchTimeValue);
                    }
                    
                    // Update status - remove HTML tags for clean display
                    let statusText = data.late || 'Not Punched In';
                    console.log('Raw status text:', statusText);
                    statusText = statusText.replace(/<[^>]*>/g, ''); // Remove HTML tags
                    $('#status').text(statusText);
                    console.log('Set status to:', statusText);
                    
                    // Update status indicator based on the status
                    if (statusText.toLowerCase().includes('present')) {
                        $('#statusIndicator').text('Working');
                        $('.status-card .card-icon').css('color', '#22c55e');
                    } else if (statusText.toLowerCase().includes('late')) {
                        $('#statusIndicator').text('Late arrival');
                        $('.status-card .card-icon').css('color', '#f59e0b');
                    } else if (statusText.toLowerCase().includes('absent')) {
                        $('#statusIndicator').text('Not present');
                        $('.status-card .card-icon').css('color', '#ef4444');
                    } else if (statusText.toLowerCase().includes('not available')) {
                        $('#statusIndicator').text('System loading...');
                        $('.status-card .card-icon').css('color', '#6b7280');
                    } else {
                        $('#statusIndicator').text('Ready to start');
                        $('.status-card .card-icon').css('color', '#6b7280');
                    }
                } else {
                    console.error('No data received from time API');
                    $('#workTime').text('API Error');
                    $('#lunchTime').text('API Error');
                    $('#status').text('Data unavailable');
                }
            },
            error: function(xhr, status, error) {
                console.error('Load time data error:', xhr.responseText || error);
                // Set default values on error
                $('#workTime').text('0H 0M');
                $('#lunchTime').text('0H 0M');
                $('#status').text('Data unavailable');
                $('#statusIndicator').text('Please refresh');
            }
        });
    }
    
    function loadActivityData() {
        $.ajax({
            url: '{{ route("api.time.details") }}',
            method: 'POST',
            data: {
                click: 'getDetails',
                _token: '{{ csrf_token() }}'
            },
            success: function(data) {
                if (data && data.trim() !== '') {
                    $('#activityTimeline').html(data);
                } else {
                    $('#activityTimeline').html(`
                        <div class="activity-placeholder">
                            <i class="fas fa-clock"></i>
                            <p>No activity recorded yet today</p>
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.error('Load activity data error:', xhr.responseText || error);
            }
        });
    }
    
    // Check punch status and update buttons
    function updateButtonStates() {
        $.ajax({
            url: '{{ route("api.time.check-punch") }}',
            method: 'POST',
            data: {
                click: 'checkfirstpunchin',
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                // Reset all buttons first
                $('.tracking-btn').prop('disabled', false).removeClass('disabled');
                
                // Get current time data for work hours check
                let currentWorkTime = $('#workTime').text();
                let currentLunchTime = $('#lunchTime').text();
                
                // Parse work hours (format: "XH YM")
                let workHours = 0;
                let lunchHours = 0;
                
                if (currentWorkTime && currentWorkTime !== 'Loading...' && currentWorkTime !== 'No work logged') {
                    let workMatch = currentWorkTime.match(/(\d+)H\s*(\d+)M/);
                    if (workMatch) {
                        workHours = parseInt(workMatch[1]) + (parseInt(workMatch[2]) / 60);
                    }
                }
                
                if (currentLunchTime && currentLunchTime !== 'Loading...' && currentLunchTime !== 'No lunch taken') {
                    let lunchMatch = currentLunchTime.match(/(\d+)H\s*(\d+)M/);
                    if (lunchMatch) {
                        lunchHours = parseInt(lunchMatch[1]) + (parseInt(lunchMatch[2]) / 60);
                    }
                }
                
                let totalHours = workHours + lunchHours;
                
                if (response == '5') {
                    // Not punched in - only punch in enabled
                    $('#punchOutBtn, #lunchStartBtn, #lunchEndBtn').prop('disabled', true).addClass('disabled');
                } else if (response == '1') {
                    // Punched in - disable punch in, enable others
                    $('#punchInBtn').prop('disabled', true).addClass('disabled');
                } else if (response == '2') {
                    // Lunch started - disable all except lunch end
                    $('#punchInBtn, #punchOutBtn, #lunchStartBtn').prop('disabled', true).addClass('disabled');
                } else if (response == '3') {
                    // Lunch ended - disable punch in and lunch end, enable punch out and lunch start
                    $('#punchInBtn, #lunchEndBtn').prop('disabled', true).addClass('disabled');
                } else if (response == '4') {
                    // Punched out - allow punch in again to continue work
                    $('#punchOutBtn, #lunchStartBtn, #lunchEndBtn').prop('disabled', true).addClass('disabled');
                } else if (response == 'work_complete') {
                    // Work completed - disable all buttons
                    $('.tracking-btn').prop('disabled', true).addClass('disabled');
                } else if (response.includes('leave') || response.includes('holiday') || response === 'regularization') {
                    // On leave/holiday - disable all buttons
                    $('.tracking-btn').prop('disabled', true).addClass('disabled');
                }
            },
            error: function(xhr, status, error) {
                console.error('Update button states error:', xhr.responseText || error);
            }
        });
    }
    
    // Refresh data every 5 minutes (300 seconds) to reduce server load
    setInterval(function() {
        loadTimeData();
        updateButtonStates();
    }, 300000);
});
</script>
@endpush