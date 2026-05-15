@extends('layouts.app')

@section('title', $pageTitle ?? 'TimeTrack Pro')

@push('styles')
<style>
    :root {
        --bottom-nav-height: 70px;
        --status-bar-height: 28px;
    }

    /* Main App Container - Mobile-first */
    .app-container {
        max-width: 600px;
        margin: 0 auto;
        background: #f5f7fb;
        min-height: 100vh;
        position: relative;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
        padding-bottom: var(--bottom-nav-height);
        display: flex;
        flex-direction: column;
    }

    /* Status Bar Filler (Android style) */
    .status-bar {
        height: var(--status-bar-height);
        background: transparent;
    }

    /* App Bar / Header - Material Top Bar */
    .app-bar {
        background: white;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #eef2f6;
        position: sticky;
        top: 0;
        z-index: 100;
        backdrop-filter: blur(10px);
        background: rgba(255,255,255,0.96);
    }

    .app-bar-title {
        font-size: 1.25rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .app-bar-actions {
        display: flex;
        gap: 12px;
    }

    .app-bar-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 20px;
        background: var(--gray-100);
        color: var(--gray-700);
        cursor: pointer;
        transition: 0.2s;
        position: relative;
    }

    .app-bar-icon:active {
        background: var(--gray-200);
        transform: scale(0.96);
    }

    /* Notification Badge */
    .nav-badge {
        position: absolute;
        top: -4px;
        right: -6px;
        background: #ef4444;
        color: white;
        font-size: 0.6rem;
        font-weight: bold;
        min-width: 18px;
        height: 18px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 5px;
    }

    /* Main Content Area - Scrollable Screen */
    .main-screen {
        padding: 16px;
        flex: 1;
        overflow-y: auto;
    }

    /* Bottom Navigation - Material BottomAppBar style */
    .bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        max-width: 600px;
        margin: 0 auto;
        background: rgba(255,255,255,0.96);
        backdrop-filter: blur(20px);
        height: var(--bottom-nav-height);
        display: flex;
        align-items: center;
        justify-content: space-around;
        padding: 8px 12px;
        box-shadow: 0 -2px 12px rgba(0,0,0,0.06);
        border-top: 1px solid rgba(0,0,0,0.05);
        z-index: 1000;
    }

    .nav-item-bottom {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        flex: 1;
        padding: 8px 0;
        border-radius: 1px;
        transition: 0.2s;
        cursor: pointer;
        background: transparent;
        border: none;
        font-family: inherit;
        color: #94a3b8;
        text-decoration: none;
    }

    .nav-item-bottom i {
        font-size: 1.4rem;
    }

    .nav-item-bottom span {
        font-size: 0.7rem;
        font-weight: 500;
    }

    .nav-item-bottom.active {
        color: var(--primary);
        background: rgba(79,70,229,0.08);
    }
    
    .nav-item-bottom:hover {
        color: var(--primary);
        text-decoration: none;
    }

    .nav-item-bottom:active {
        transform: scale(0.96);
    }

    /* Notification Dropdown */
    .notification-dropdown-mobile {
        position: fixed;
        top: 60px;
        right: 16px;
        background: white;
        border-radius: 20px;
        width: 320px;
        max-height: 450px;
        overflow-y: auto;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        z-index: 2000;
        display: none;
        border: 1px solid var(--gray-200);
    }
    
    .notification-dropdown-mobile.show {
        display: block;
    }

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
        font-size: 14px !important;
    }
    
    @keyframes pulse-alarm {
        0% { transform: scale(1); box-shadow: 0 4px 20px rgba(239, 68, 68, 0.4); }
        50% { transform: scale(1.05); box-shadow: 0 8px 30px rgba(239, 68, 68, 0.6); }
        100% { transform: scale(1); box-shadow: 0 4px 20px rgba(239, 68, 68, 0.4); }
    }
    
    @media (max-width: 768px) {
        #lunchAlarmBtn {
            top: 10px !important;
            right: 10px !important;
            padding: 12px 16px !important;
            font-size: 12px !important;
        }
    }
</style>
@stack('page-styles')
@endpush

@section('content')
<!-- Lunch Alarm Button -->
<button id="lunchAlarmBtn" style="display: none;">
    <i class="fas fa-bell"></i> Stop Lunch Alarm
</button>
<audio id="lunchAlarmAudio" preload="auto"></audio>

<div class="app-container">
    <!-- Status Bar -->
    <div class="status-bar"></div>

    <!-- App Bar -->
    <div class="app-bar">
        <div class="app-bar-title">@yield('page-title', 'TimeTrack Pro')</div>
        <div class="app-bar-actions">
            <div class="app-bar-icon" id="mobileNotificationIcon">
                <i class="fas fa-bell"></i>
                <span id="navNotificationBadge" class="nav-badge" style="display: none;">0</span>
            </div>
        </div>
    </div>

    <!-- Notification Dropdown -->
    <div id="notificationMobileDropdown" class="notification-dropdown-mobile">
        <div id="notificationListMobile" style="padding: 8px;"></div>
    </div>

    <!-- Main Content Area -->
    <div class="main-screen">
        @if(View::hasSection('page-subtitle'))
        <div style="margin-bottom: 16px; color: var(--gray-500); font-size: 0.95rem;">
            @yield('page-subtitle')
        </div>
        @endif
        
        @yield('page-content')
    </div>

    <!-- Bottom Navigation Bar -->
    <div class="bottom-nav">
        @if(session('at_office'))
        <a href="{{ route('dashboard') }}" class="nav-item-bottom {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        @endif
        <a href="{{ route('applications.index') }}" class="nav-item-bottom {{ request()->routeIs('applications.index') ? 'active' : '' }}">
            <i class="fas fa-file-alt"></i>
            <span>Applications</span>
        </a>
        <a href="{{ route('schedule') }}" class="nav-item-bottom {{ request()->routeIs('schedule') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i>
            <span>Schedule</span>
        </a>
        <a href="{{ route('payslips.index') }}" class="nav-item-bottom {{ request()->routeIs('payslips.*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Payslips</span>
        </a>
        <a href="{{ route('profile') }}" class="nav-item-bottom {{ request()->routeIs('profile') ? 'active' : '' }}">
            <i class="fas fa-user-cog"></i>
            <span>Profile</span>
        </a>
        
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // ========== NOTIFICATION SYSTEM ==========
    let notificationDropdownOpen = false;
    
    $('#mobileNotificationIcon').on('click', function(e) {
        e.stopPropagation();
        if (!notificationDropdownOpen) {
            loadNotifications();
            $('#notificationMobileDropdown').addClass('show');
            notificationDropdownOpen = true;
        } else {
            $('#notificationMobileDropdown').removeClass('show');
            notificationDropdownOpen = false;
        }
    });
    
    $(document).on('click', function(e) {
        if (notificationDropdownOpen && !$(e.target).closest('#mobileNotificationIcon, #notificationMobileDropdown').length) {
            $('#notificationMobileDropdown').removeClass('show');
            notificationDropdownOpen = false;
        }
    });
    
    function loadNotifications() {
        $.ajax({
            url: '{{ route("notifications") }}',
            type: 'GET',
            global: false,
            success: function(notifications) {
                let html = '';
                let unreadCount = 0;
                if (notifications.length === 0) {
                    html = '<div style="padding: 20px; text-align: center; color: #94a3b8;"><i class="fas fa-bell-slash"></i><p style="margin-top: 8px;">No notifications</p></div>';
                } else {
                    notifications.forEach(function(notif) {
                        if (notif.status === 'pending') unreadCount++;
                        let appType = notif.application ? notif.application.req_type.replace('_', ' ') : 'Application';
                        let statusText = notif.application ? notif.application.status : 'pending';
                        let statusColor = statusText === 'approved' ? '#10b981' : statusText === 'rejected' ? '#ef4444' : '#f59e0b';
                        let statusIcon = statusText === 'approved' ? 'fa-check-circle' : (statusText === 'rejected' ? 'fa-times-circle' : 'fa-clock');
                        
                        let bgColor = notif.status === 'pending' ? 'rgba(79, 70, 229, 0.05)' : 'transparent';
                        let borderLeft = notif.status === 'pending' ? '3px solid var(--primary)' : '3px solid transparent';
                        
                        html += `<div data-id="${notif.id}" style="padding: 14px 16px; border-bottom: 1px solid #e2e8f0; background: ${bgColor}; border-left: ${borderLeft}; cursor:pointer;" class="notif-item">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <i class="fas ${statusIcon}" style="color: ${statusColor};"></i>
                                <div style="flex:1;">
                                    <div style="font-weight:600; font-size:0.85rem; color: #1e293b;">${appType} Request #${notif.App_id}</div>
                                    <div style="font-size:0.7rem; color:${statusColor}; font-weight: 600;">STATUS: ${statusText.toUpperCase()}</div>
                                    <div style="font-size:0.65rem; color:#94a3b8;">${new Date(notif.created_at).toLocaleDateString()}</div>
                                </div>
                            </div>
                        </div>`;
                    });
                }
                $('#notificationListMobile').html(html);
                if (unreadCount > 0) {
                    $('#navNotificationBadge').text(unreadCount > 9 ? '9+' : unreadCount).show();
                } else {
                    $('#navNotificationBadge').hide();
                }
            }
        });
    }

    $(document).on('click', '.notif-item', function() {
        let notificationId = $(this).data('id');
        $(this).css('background', 'transparent').css('border-left', '3px solid transparent');
        
        $.post(`/notifications/${notificationId}/read`, {
            _token: '{{ csrf_token() }}'
        }).done(function() {
            window.location.href = '{{ route("notifications.index") }}';
        });
    });
    
    loadNotifications();
    setInterval(loadNotifications, 30000);
    
    // ========== LUNCH ALARM ==========
    let alarmPlaying = false;
    let alarmTimeout = null;
    
    function checkLunchAlarm() {
        const empId = '{{ Auth::user()->emp_id ?? Auth::user()->id ?? "" }}';
        if(!empId) return;
        $.ajax({
            url: `{{ url("/api/lunch-alarm/check") }}/${empId}`,
            method: 'GET',
            global: false,
            success: function(data) {
                if (data.alarm_active && !alarmPlaying) {
                    $('#lunchAlarmBtn').show();
                    alarmPlaying = true;
                    const audio = document.getElementById('lunchAlarmAudio');
                    if(audio) {
                        audio.loop = false;
                        audio.play().catch(e => console.log('Audio play failed:', e));
                    }
                    alarmTimeout = setTimeout(() => { if(alarmPlaying) stopLunchAlarm(); }, 10000);
                } else if (!data.alarm_active && alarmPlaying) {
                    stopLunchAlarm();
                }
            }
        });
    }
    
    function stopLunchAlarm() {
        alarmPlaying = false;
        $('#lunchAlarmBtn').hide();
        if (alarmTimeout) clearTimeout(alarmTimeout);
        const audio = document.getElementById('lunchAlarmAudio');
        if(audio) { audio.pause(); audio.currentTime = 0; }
        $.ajax({ url: '{{ route("lunch-alarm.stop") }}', method: 'POST', data: { _token: '{{ csrf_token() }}' }, global: false });
    }
    
    $(document).on('click', '#lunchAlarmBtn', function() { stopLunchAlarm(); });
    
    setInterval(checkLunchAlarm, 10000);
    checkLunchAlarm();
});
</script>
@stack('page-scripts')
@endpush