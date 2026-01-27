@extends('layouts.user')

@section('page-title', 'Welcome Back!')
@section('page-subtitle', 'Track your time and manage your workday efficiently')

@push('page-styles')
<style>
    /* Full-screen loading modal */
    .loading-overlay {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(0, 0, 0, 0.8) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        z-index: 999999 !important;
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
    }
    
    .loading-content {
        background: white !important;
        padding: 2rem !important;
        border-radius: 16px !important;
        text-align: center !important;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3) !important;
        max-width: 300px !important;
        width: 90% !important;
        position: relative !important;
    }
    
    @media (max-width: 400px) {
        .loading-overlay {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            min-width: 100vw !important;
            min-height: 100vh !important;
            z-index: 999999 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: rgba(0, 0, 0, 0.8) !important;
        }
        
        .loading-content {
            width: 85% !important;
            max-width: 280px !important;
            padding: 1.5rem !important;
            margin: 1rem !important;
            position: relative !important;
            background: white !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3) !important;
        }
    }
    
    .loading-spinner {
        width: 60px !important;
        height: 60px !important;
        border: 4px solid #f3f4f6 !important;
        border-top: 4px solid #3b82f6 !important;
        border-radius: 50% !important;
        animation: spin 1s linear infinite !important;
        margin: 0 auto 1rem !important;
    }
    
    .loading-text {
        font-size: 1.1rem !important;
        font-weight: 600 !important;
        color: #374151 !important;
        margin-bottom: 0.5rem !important;
    }
    
    .loading-subtext {
        font-size: 0.875rem !important;
        color: #6b7280 !important;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* SweetAlert above loading modal */
    .swal2-container {
        z-index: 9999999 !important;
    }
    
    .swal2-popup {
        z-index: 9999999 !important;
    }
    
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
        border: 2px solid transparent;
        border-radius: 16px;
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        cursor: pointer;
    }
    
    /* Punch In Button - Professional Blue */
    #punchInBtn {
        background: #2563eb;
        color: white;
        border-color: #1d4ed8;
    }
    #punchInBtn:hover:not(:disabled) {
        background: #1d4ed8;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
        border-color: #1e40af;
    }
    #punchInBtn:active:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
    }
    
    /* Lunch Start Button - Professional Orange */
    #lunchStartBtn {
        background: #ea580c;
        color: white;
        border-color: #dc2626;
    }
    #lunchStartBtn:hover:not(:disabled) {
        background: #dc2626;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(234, 88, 12, 0.3);
        border-color: #b91c1c;
    }
    #lunchStartBtn:active:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(234, 88, 12, 0.4);
    }
    
    /* Lunch End Button - Professional Teal */
    #lunchEndBtn {
        background: #0891b2;
        color: white;
        border-color: #0e7490;
    }
    #lunchEndBtn:hover:not(:disabled) {
        background: #0e7490;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(8, 145, 178, 0.3);
        border-color: #155e75;
    }
    #lunchEndBtn:active:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(8, 145, 178, 0.4);
    }
    
    /* Punch Out Button - Professional Purple */
    #punchOutBtn {
        background: #7c3aed;
        color: white;
        border-color: #6d28d9;
    }
    #punchOutBtn:hover:not(:disabled) {
        background: #6d28d9;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(124, 58, 237, 0.3);
        border-color: #5b21b6;
    }
    #punchOutBtn:active:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(124, 58, 237, 0.4);
    }
    
    .tracking-btn:disabled,
    .tracking-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
        background: #e5e7eb !important;
        color: #9ca3af !important;
        border-color: #d1d5db !important;
        box-shadow: none !important;
    }
    
    .tracking-btn:disabled:hover,
    .tracking-btn.disabled:hover {
        transform: none !important;
        box-shadow: none !important;
        background: #e5e7eb !important;
    }
    
    /* Add pulse effect for active buttons */
    .tracking-btn:not(:disabled)::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    .tracking-btn:not(:disabled):active::before {
        width: 300px;
        height: 300px;
    }
    
    /* Add loading state */
    .tracking-btn.processing {
        pointer-events: none;
        opacity: 0.8;
    }
    
    .tracking-btn.processing .btn-content i {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
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
        position: absolute;
        right: 10px;
        top: 15px;
        padding-left: 12px;
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
        
        /* Enhanced mobile time-tracking-card */
        .time-tracking-card {
            margin: 1rem 0;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(248,250,252,0.95) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            overflow: hidden;
            position: relative;
        }
        
        .time-tracking-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #4facfe);
        }
        
        .time-tracking-card .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1.5rem 1.25rem 1rem;
        }
        
        .time-tracking-card .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .time-tracking-card .card-title i {
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 1.25rem;
        }
        
        .tracking-controls {
            padding: 1.25rem;
        }
    }

    .my-swal-popup { z-index: 9999999 !important; }
    
    /* Lunch Alarm Button */
    #lunchAlarmBtn {
        position: fixed !important;
        top: 20px !important;
        right: 20px !important;
        z-index: 9999999 !important;
        background: #ef4444 !important;
        color: white !important;
        border: none !important;
        padding: 15px 20px !important;
        border-radius: 50px !important;
        font-weight: 600 !important;
        box-shadow: 0 4px 20px rgba(239, 68, 68, 0.4) !important;
        animation: pulse-alarm 1s infinite !important;
        cursor: pointer !important;
        display: none !important;
    }
    
    @keyframes pulse-alarm {
        0% { transform: scale(1); box-shadow: 0 4px 20px rgba(239, 68, 68, 0.4); }
        50% { transform: scale(1.05); box-shadow: 0 8px 30px rgba(239, 68, 68, 0.6); }
        100% { transform: scale(1); box-shadow: 0 4px 20px rgba(239, 68, 68, 0.4); }
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
    <strong>Today is your Half Day</strong> - You have an approved half-day leave for today
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
            <button class="btn-icon" id="refreshData" title="Refresh Data">
                <i class="fas fa-sync-alt"></i>
            </button>
        </h2>
        <div class="card-actions">
        </div>
    </div>
    
    <div class="tracking-controls">
        <div class="control-group">
            <button id="punchInBtn" class="btn tracking-btn">
                <div class="btn-content">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Punch In</span>
                </div>
                <div class="btn-description">Start workday</div>
            </button>
            <button id="lunchStartBtn" class="btn tracking-btn">
                <div class="btn-content">
                    <i class="fas fa-utensils"></i>
                    <span>Lunch Start</span>
                </div>
                <div class="btn-description">Begin break</div>
            </button>
        </div>
        
        <div class="control-group">
            
            <button id="lunchEndBtn" class="btn tracking-btn">
                <div class="btn-content">
                    <i class="fas fa-coffee"></i>
                    <span>Lunch End</span>
                </div>
                <div class="btn-description">Resume work</div>
            </button>
            <button id="punchOutBtn" class="btn tracking-btn">
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

<!-- Full-screen loading modal -->
<div id="loadingModal" class="loading-overlay" style="display: none;">
    <div class="loading-content">
        <div class="loading-spinner"></div>
        <div class="loading-text" id="loadingText">Processing...</div>
        <div class="loading-subtext" id="loadingSubtext">Please wait</div>
    </div>
</div>

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
        showLoadingModal("punch_in");
        handleTimeAction('punch_in');
    });
    
    $('#punchOutBtn').click(function() {
        showLoadingModal("punch_out");
        handleTimeAction('punch_out');
    });
    
    $('#lunchStartBtn').click(function() {
        showLoadingModal("lunch_start");
        handleTimeAction('lunch_start');
    });
    
    $('#lunchEndBtn').click(function() {
        showLoadingModal("lunch_end");
        handleTimeAction('lunch_end');
        stopLunchAlarm(); // Stop alarm when lunch ends
    });
    
    let isProcessing = false; // Prevent multiple simultaneous requests
    let lunchAlarmTimer = null;
    let lunchAlarmAudio = null;
    
    function handleTimeAction(action) {
        // Prevent multiple clicks during processing
        if (isProcessing) {
            return;
        }
        
        isProcessing = true;
        
        // Show loading modal IMMEDIATELY
        showLoadingModal(action);
        
        // Disable the clicked button immediately
        const clickedBtn = $('#' + action.replace('_', '') + 'Btn');
        clickedBtn.prop('disabled', true).addClass('processing');
        
        // Small delay to ensure modal is visible before geolocation
        setTimeout(() => {
            // Get user location if needed
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        performTimeAction(action, {
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude
                        });
                    },
                    function(error) {
                        // Location failed, try without coordinates
                        performTimeAction(action, {});
                    }
                );
            } else {
                // No geolocation support
                performTimeAction(action, {});
            }
        }, 100);
    }
    
    function performTimeAction(action, locationData) {
        const route = action === 'punch_in' ? '{{ route("punch.in") }}' : 
                     action === 'punch_out' ? '{{ route("punch.out") }}' : 
                     action === 'lunch_start' ? '{{ route("lunch.start") }}' : 
                     '{{ route("lunch.end") }}';
        
        $.ajax({
            url: route,
            method: 'POST',
            data: {
                ...locationData,
                _token: '{{ csrf_token() }}'
            },
            timeout: 30000,
            success: function(response) {
                
                if (response.require_image) {
                    showImageCaptureModal(action);
                } else if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false,
                        customClass: {
                            popup: 'my-swal-popup'
                        }
                    });
                    loadTimeData();
                    loadActivityData();
                    updateButtonStates();
                    hideLoadingModal();
                }
            },
            error: function(xhr) {
                hideLoadingModal();
                const response = xhr.responseJSON;
                if (response && response.require_image) {
                    showImageCaptureModal(action);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response?.error || 'Unable to process request. Please try again.',
                        zIndex: 9999999
                    });
                }
            },
            complete: function() {
                isProcessing = false;
                const clickedBtn = $('#' + action.replace('_', '') + 'Btn');
                clickedBtn.removeClass('processing');
                updateButtonStates();
            }
        });
    }
    
    function showImageCaptureModal(action) {
        Swal.fire({
            title: 'Image Capture Required',
            html: `
                <div style="text-align: center;">
                    <p>Please capture your image to proceed with ${action.replace('_', ' ')}.</p>
                    <video id="camera" width="300" height="200" autoplay style="border-radius: 8px; margin: 10px 0;"></video>
                    <canvas id="canvas" width="300" height="200" style="display: none;"></canvas>
                    <br>
                    <button id="captureBtn" class="btn btn-primary" style="margin: 10px;">Capture Image</button>
                    <button id="retakeBtn" class="btn btn-secondary" style="margin: 10px; display: none;">Retake</button>
                </div>
            `,
            showCancelButton: true,
            showConfirmButton: false,
            cancelButtonText: 'Cancel',
            allowOutsideClick: false,
            didOpen: () => {
                startCamera();
            },
            willClose: () => {
                stopCamera();
            }
        });
        
        // Handle capture button
        $(document).on('click', '#captureBtn', function() {
            captureImage(action);
        });
        
        // Handle retake button
        $(document).on('click', '#retakeBtn', function() {
            $('#camera').show();
            $('#captureBtn').show().text('Capture Image');
            $('#retakeBtn').hide();
            startCamera();
        });
    }
    
    let stream = null;
    
    function startCamera() {
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(function(mediaStream) {
                stream = mediaStream;
                const video = document.getElementById('camera');
                video.srcObject = stream;
            })
            .catch(function(error) {
                console.error('Camera error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Camera Error',
                    text: 'Unable to access camera. Please check permissions.',
                    zIndex: 9999999
                });
            });
    }
    
    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
    }
    
    function captureImage(action) {
        const video = document.getElementById('camera');
        const canvas = document.getElementById('canvas');
        const context = canvas.getContext('2d');
        
        // Draw video frame to canvas
        context.drawImage(video, 0, 0, 300, 200);
        
        // Get image data
        const imageData = canvas.toDataURL('image/jpeg');
        
        // Hide video and show captured image
        video.style.display = 'none';
        canvas.style.display = 'block';
        
        // Update buttons
        $('#captureBtn').text('Submit Image').off('click').on('click', function() {
            submitImage(imageData, action);
        });
        $('#retakeBtn').show();
        
        stopCamera();
    }
    
    function submitImage(imageData, action) {
        $.ajax({
            url: '{{ route("capture.image") }}',
            method: 'POST',
            data: {
                image: imageData,
                entry_type: action,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                Swal.close();
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false,
                        zIndex: 9999999
                    });
                    loadTimeData();
                    loadActivityData();
                    updateButtonStates();
                }
            },
            error: function(xhr) {
                Swal.close();
                const response = xhr.responseJSON;
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response?.error || 'Failed to submit image. Please try again.',
                    zIndex: 9999999
                });
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
                // console.log('Time data received:', data);
                 // Debug log
                
                if (data) {
                    console.log('Updating DOM elements...');
                    
                    // Update work time - use network or total_hours as fallback
                    let workTimeValue = data.network || data.total_hours || '0H 0M';
                    // console.log('Work time value:', workTimeValue);
                    
                    if (workTimeValue === '0H 0M' || workTimeValue === '00:00:00' || workTimeValue === '') {
                        $('#workTime').text(workTimeValue).css('color', '#666');
                        // console.log('Set work time to: No work logged');
                    } else {
                        $('#workTime').text(workTimeValue).css('color', '#333');
                        // console.log('Set work time to:', workTimeValue);
                    }
                    
                    // Update lunch time
                    let lunchTimeValue = data.totalLunchByemp || '0M';
                    // console.log('Lunch time value:', lunchTimeValue);
                    
                    if (lunchTimeValue === '0H 0M' || lunchTimeValue === '0M' || lunchTimeValue === '') {
                        $('#lunchTime').text(lunchTimeValue).css('color', '#666');
                        // console.log('Set lunch time to: No lunch taken');
                    } else {
                        $('#lunchTime').text(lunchTimeValue).css('color', '#333');
                        // console.log('Set lunch time to:', lunchTimeValue);
                    }
                    
                    // Update status - remove HTML tags for clean display
                    let statusText = data.late || 'Not Punched In';
                    // console.log('Raw status text:', statusText);
                    statusText = statusText.replace(/<[^>]*>/g, ''); // Remove HTML tags
                    $('#status').text(statusText);
                    // console.log('Set status to:', statusText);
                    
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
                console.log('Button state response:', response); // Debug log
                
                // Reset all buttons first
                $('.tracking-btn').prop('disabled', false).removeClass('disabled');
                
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
                    // Punched out - allow punch in again
                    $('#punchOutBtn, #lunchStartBtn, #lunchEndBtn').prop('disabled', true).addClass('disabled');
                } else if (response == '6') {
                    // Work complete - disable all buttons
                    $('.tracking-btn').prop('disabled', true).addClass('disabled');
                } else if (response.includes && (response.includes('leave') || response.includes('holiday') || response === 'regularization')) {
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
    
    // Loading modal functions
    function showLoadingModal(action) {
        const actionText = {
            'punch_in': 'Punching In',
            'punch_out': 'Punching Out', 
            'lunch_start': 'Starting Lunch',
            'lunch_end': 'Ending Lunch'
        };
        
        $('#loadingText').text(actionText[action] || 'Processing');
        $('#loadingSubtext').text('Please wait...');
        $('#loadingModal').show();
    }
    
    function hideLoadingModal() {
        $('#loadingModal').hide();
    }
});
</script>
@endpush