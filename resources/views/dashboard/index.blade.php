@extends('layouts.user')

@section('page-title', 'Welcome Back!')
@section('page-subtitle', 'Track your time and manage your workday efficiently')

@push('page-styles')

<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f8;
            color: #1e293b;
            line-height: 1.5;
            padding: 2rem 1.5rem;
        }

        /* Layout container */
        .dashboard-container {
            max-width: 1280px;
            margin: 0 auto;
        }

        /* === Global Cards & Elements === */
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --secondary: #0f172a;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-900: #0f172a;
            --radius: 0.75rem;
            --radius-xl: 1rem;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --transition: all 0.2s ease;
        }

        /* Typography & spacing */
        h1, h2, h3 {
            font-weight: 600;
            letter-spacing: -0.01em;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 800;
            background: linear-gradient(135deg, #1e293b, #4f46e5);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.25rem;
        }

        .page-subtitle {
            color: var(--gray-500);
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* alert half day */
        .alert-halfday {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border-left: 4px solid #3b82f6;
            border-radius: var(--radius);
            padding: 1rem 1.25rem;
            margin-bottom: 1.75rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
            color: #1e40af;
            font-weight: 500;
        }

        /* Clock widget professional */
        .clock-widget {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            border: 1px solid var(--gray-200);
        }

        .clock-display {
            font-size: 2.5rem;
            font-weight: 700;
            font-family: 'Inter', monospace;
            letter-spacing: 1px;
            color: #0f172a;
            line-height: 1.2;
        }

        .clock-date {
            font-size: 0.9rem;
            color: var(--gray-500);
            font-weight: 500;
        }

        .time-format-toggle {
            background: var(--gray-100);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--gray-700);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
            left: 80%;
            border-right: double;
        }

        .time-format-toggle i {
            font-size: 0.9rem;
        }

        .time-format-toggle:hover {
            background: var(--gray-200);
        }

        /* Summary grid (3 cards) */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background: white;
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: var(--gray-300);
        }

        .status-card{
            margin-top: -1.5rem;
            margin-bottom: 2rem;
        }

        .card-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.6rem;
            margin-bottom: 1rem;
        }

        .summary-card h3 {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: var(--gray-500);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .summary-card p {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .card-trend {
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            color: var(--gray-500);
        }

        /* Time tracking card */
        .time-tracking-card {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .card-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            background: linear-gradient(135deg, var(--gray-50), #f8fafc);
        }

        .card-title {
            font-size: 1.375rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--gray-900);
        }

        .card-title i {
            color: var(--primary);
            font-size: 1.25rem;
        }

        

        .tracking-controls {
            padding: 2rem;
        }

        .control-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .control-group:last-child {
            margin-bottom: 0;
        }

        .tracking-btn {
            border: none;
            border-radius: var(--radius);
            padding: 1.5rem 1.25rem;
            font-weight: 600;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            min-height: 120px;
            justify-content: center;
        }

        .tracking-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .tracking-btn:hover:not(:disabled)::before {
            left: 100%;
        }

        .btn-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            z-index: 1;
            position: relative;
        }

        .btn-content i {
            font-size: 2rem;
            opacity: 0.9;
        }

        .btn-content span {
            font-size: 1rem;
            font-weight: 600;
            text-align: center;
        }

        .btn-description {
            font-size: 0.75rem;
            opacity: 0.8;
            font-weight: 400;
            text-align: center;
            z-index: 1;
            position: relative;
        }

        /* individual button styles */
        #punchInBtn {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }
        #punchInBtn:hover:not(:disabled) {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            transform: translateY(-3px);
            box-shadow: 0 12px 24px -6px rgba(37, 99, 235, 0.4);
        }

        #lunchStartBtn {
            background: linear-gradient(135deg, #ea580c, #c2410c);
        }
        #lunchStartBtn:hover:not(:disabled) {
            background: linear-gradient(135deg, #c2410c, #9a3412);
            transform: translateY(-3px);
            box-shadow: 0 12px 24px -6px rgba(234, 88, 12, 0.4);
        }

        #lunchEndBtn {
            background: linear-gradient(135deg, #0d9488, #0f766e);
        }
        #lunchEndBtn:hover:not(:disabled) {
            background: linear-gradient(135deg, #0f766e, #115e59);
            transform: translateY(-3px);
            box-shadow: 0 12px 24px -6px rgba(13, 148, 136, 0.4);
        }

        #punchOutBtn {
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
        }
        #punchOutBtn:hover:not(:disabled) {
            background: linear-gradient(135deg, #6d28d9, #5b21b6);
            transform: translateY(-3px);
            box-shadow: 0 12px 24px -6px rgba(124, 58, 237, 0.4);
        }

        .tracking-btn:disabled, .tracking-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            filter: grayscale(0.2);
            transform: none !important;
            box-shadow: none !important;
        }

        .tracking-btn:disabled::before, .tracking-btn.disabled::before {
            display: none;
        }

        /* Activity timeline */
        .activity-card {
            background: white;
            border-radius: var(--radius-xl);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }
        .activity-content {
            padding: 1.5rem;
        }
        .activity-timeline {
            min-height: 240px;
            padding: 0.5rem 0;
        }
        .activity-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 1rem;
            color: var(--gray-400);
            gap: 0.75rem;
        }
        .activity-placeholder i { font-size: 2.5rem; opacity: 0.6; }

        /* loading modal professional */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(6px);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .loading-modal {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            text-align: center;
            width: 280px;
            box-shadow: 0 25px 40px rgba(0,0,0,0.2);
        }
        .spinner {
            width: 56px;
            height: 56px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* responsive adjustments */
        @media (max-width: 640px) {
            body { padding: 1rem; }

            .clock-widget {
                padding: 1rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .clock-display { font-size: 2rem; }
            .clock-date { font-size: 0.8rem; }

            .time-tracking-card .card-header {
                padding: 1.25rem 1.5rem;
            }

            .time-tracking-card .card-title {
                font-size: 1.125rem;
            }

            .tracking-controls {
                padding: 1.5rem;
            }

            .control-group {
                gap: 1rem;
            }

            .tracking-btn {
                padding: 1.25rem 1rem;
                min-height: 100px;
            }

            .btn-content i {
                font-size: 1.5rem;
            }

            .btn-content span {
                font-size: 0.9rem;
            }

            .btn-description {
                font-size: 0.7rem;
            }

            .summary-grid {
                grid-template-columns: 1fr 1fr !important;
                gap: 1rem;
            }

            .summary-card {
                padding: 1rem;
            }

            .card-icon {
                width: 40px;
                height: 40px;
                font-size: 1.25rem;
            }

            .summary-card p {
                font-size: 1.25rem;
            }

            .activity-content {
                padding: 1rem;
            }
        }

        @media (max-width: 480px) {
            .control-group {
                width: 120%;
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
                margin-left: -1.3rem;
            }

            .tracking-btn {
                min-height: 80px;
                padding: 1rem 0.75rem;
            }

            .summary-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 0.75rem;
            }

            .time-tracking-card .card-header {
                padding: 1.2rem 0.9rem;
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

<div class="card time-tracking-card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-fingerprint"></i>
            Time Tracking
            
        </h2>
        <div class="card-actions">
        </div>
    </div>

    <div class="tracking-controls">
        <div class="control-group">
            <button id="punchInBtn" class="tracking-btn">
                <div class="btn-content">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Punch In</span>
                </div>
                <div class="btn-description">Start workday</div>
            </button>
            <button id="lunchStartBtn" class="tracking-btn">
                <div class="btn-content">
                    <i class="fas fa-utensils"></i>
                    <span>Lunch Start</span>
                </div>
                <div class="btn-description">Begin break</div>
            </button>
        </div>

        <div class="control-group">
            <button id="lunchEndBtn" class="tracking-btn">
                <div class="btn-content">
                    <i class="fas fa-coffee"></i>
                    <span>Lunch End</span>
                </div>
                <div class="btn-description">Resume work</div>
            </button>
            <button id="punchOutBtn" class="tracking-btn">
                <div class="btn-content">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Punch Out</span>
                </div>
                <div class="btn-description">End workday</div>
            </button>
        </div>
    </div>
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
</div>


<div class="card recent-activity-card" >
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-activity"></i>
            Today's Activity
        </h2>
    </div>

    <div class="activity-content">
        <div class="summary-card status-card ">
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

        <div class="activity-timeline" id="activityTimeline">
            <div class="activity-placeholder">
                <i class="fas fa-clock"></i>
                <p>No activity recorded yet today</p>
            </div>
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
        // setTimeout(() => {
            // Get user location if needed
        //     if (navigator.geolocation) {
        //         navigator.geolocation.getCurrentPosition(
        //             function(position) {
        //                 performTimeAction(action, {
        //                     latitude: position.coords.latitude,
        //                     longitude: position.coords.longitude
        //                 });
        //             },
        //             function(error) {
        //                 // Location failed, try without coordinates
        //                 performTimeAction(action, {});
        //             }
        //         );
        //     } else {
        //         // No geolocation support
        //         performTimeAction(action, {});
        //     }
        // }, 100);
        performTimeAction(action, {});
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
                    <video id="camera" width="300" height="300" autoplay style="border-radius: 8px; margin: 10px 0;"></video>
                    <canvas id="canvas" width="300" height="300" style="display: none;"></canvas>
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
        context.drawImage(video, 0, 0, 300, 300);
        
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