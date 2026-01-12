@extends('layouts.app')

@section('title', $pageTitle ?? 'TimeTrack Pro')

@push('styles')
<style>
    /* User Layout Common Styles */
    .user-avatar {
        width: 80px;
        height: 80px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2.5rem;
        box-shadow: var(--shadow-lg);
        animation: pulse 2s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    .status-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 0.75rem;
        padding: 0.5rem 1rem;
        background: rgba(16, 185, 129, 0.1);
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--success);
        animation: blink 2s ease-in-out infinite;
    }
    
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
    
    .status-text {
        color: var(--success);
    }
    
    .nav-indicator {
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 3px;
        height: 0;
        background: linear-gradient(180deg, var(--primary), var(--secondary));
        border-radius: 3px 0 0 3px;
        transition: var(--transition);
    }
    
    .nav-link.active .nav-indicator {
        height: 70%;
    }
    
    .logout-btn {
        color: var(--danger) !important;
    }
    
    .logout-btn:hover {
        background: rgba(239, 68, 68, 0.1) !important;
    }

    /* Mobile menu toggle */
    .mobile-menu-toggle {
        display: none;
        position: fixed;
        top: 1rem;
        left: 1rem;
        z-index: 1001;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        border: none;
        cursor: pointer;
        box-shadow: var(--shadow-lg);
        transition: var(--transition);
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
    }
    
    .mobile-menu-toggle:hover {
        transform: scale(1.1);
    }
    
    .mobile-menu-toggle:active {
        transform: scale(0.95);
    }
    
    /* Sidebar overlay */
    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity 0.3s ease, visibility 0.3s ease;
        touch-action: manipulation;
    }
    
    .sidebar-overlay.active {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }
    
    /* Improve touch targets */
    .nav-link, .btn, button, input, select, textarea {
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
    }
    
    .nav-link {
        min-height: 44px;
        display: flex;
        align-items: center;
    }

    @media (max-width: 768px) {
        .mobile-menu-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            padding: 0;
        }
        
        .layout-grid {
            grid-template-columns: 1fr;
            gap: 0;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: -100%;
            width: 280px;
            height: 100vh;
            z-index: 1000;
            transition: left 0.3s ease;
            overflow-y: auto;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar.active {
            left: 0;
        }
        
        .sidebar-overlay {
            visibility: visible;
        }
        
        .main-content {
            margin-left: 0;
            padding: 1rem;
            margin-top: 70px;
            width: 100%;
            min-height: calc(100vh - 70px);
        }
        
        .page-header {
            margin-bottom: 1rem;
        }
        
        .page-title {
            font-size: 1.5rem;
        }
        
        .page-subtitle {
            font-size: 0.875rem;
        }
    }
</style>
@stack('page-styles')
@endpush

@section('content')
<!-- Mobile menu toggle -->
<button class="mobile-menu-toggle" id="mobileMenuToggle">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="container">
    <div class="layout-grid">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h3>{{ Auth::user()->full_name }}
                    <div class="notification-bell" id="notificationBell">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge" id="notificationCount" style="display: none;">0</span>
                        <div class="notification-dropdown" id="notificationDropdown">
                            <div id="notificationList">Loading...</div>
                        </div>
                    </div>
                </h3>
                <p>ID: {{ Auth::user()->emp_id }}</p>
                <div class="status-indicator online">
                    <span class="status-dot"></span>
                    <span class="status-text">Online</span>
                </div>
            </div>
            
            <nav>
                <ul class="nav-menu">
                    <li class="nav-item">
                        @if(session('at_office'))
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                            <div class="nav-indicator"></div>
                        </a>
                        @endif
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('applications.index') }}" class="nav-link {{ request()->routeIs('applications.index') ? 'active' : '' }}">
                            <i class="fas fa-file-alt"></i>
                            <span>Applications</span>
                            <div class="nav-indicator"></div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('applications.history') }}" class="nav-link {{ request()->routeIs('applications.history') ? 'active' : '' }}">
                            <i class="fas fa-history"></i>
                            <span>History</span>
                            <div class="nav-indicator"></div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('schedule') }}" class="nav-link {{ request()->routeIs('schedule') ? 'active' : '' }}">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Schedule</span>
                            <div class="nav-indicator"></div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('profile') }}" class="nav-link {{ request()->routeIs('profile') ? 'active' : '' }}">
                            <i class="fas fa-user-cog"></i>
                            <span>Profile</span>
                            <div class="nav-indicator"></div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('payslips.index') }}" class="nav-link {{ request()->routeIs('payslips.*') ? 'active' : '' }}">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>Payslips</span>
                            <div class="nav-indicator"></div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="nav-link logout-btn">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                                <div class="nav-indicator"></div>
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">@yield('page-title')</h1>
                <p class="page-subtitle">@yield('page-subtitle')</p>
            </div>
            
            @yield('page-content')
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Mobile menu functionality
    $('#mobileMenuToggle').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('.sidebar').toggleClass('active');
        $('.sidebar-overlay').toggleClass('active');
        $(this).find('i').toggleClass('fa-bars fa-times');
    });
    
    $('#sidebarOverlay').click(function(e) {
        e.preventDefault();
        $('.sidebar').removeClass('active');
        $('.sidebar-overlay').removeClass('active');
        $('#mobileMenuToggle i').removeClass('fa-times').addClass('fa-bars');
    });
    
    // Close sidebar when clicking nav links on mobile
    $('.nav-link').click(function() {
        if (window.innerWidth <= 768) {
            $('.sidebar').removeClass('active');
            $('.sidebar-overlay').removeClass('active');
            $('#mobileMenuToggle i').removeClass('fa-times').addClass('fa-bars');
        }
    });
    
    // Prevent body scroll when sidebar is open on mobile
    $('.sidebar').on('transitionend', function() {
        if ($(this).hasClass('active')) {
            $('body').css('overflow', 'hidden');
        } else {
            $('body').css('overflow', 'auto');
        }
    });
    
    // Notification functionality
    let notificationDropdownOpen = false;
    
    $('#notificationBell').click(function(e) {
        e.stopPropagation();
        if (!notificationDropdownOpen) {
            loadNotifications();
            $('#notificationDropdown').addClass('show');
            notificationDropdownOpen = true;
        } else {
            $('#notificationDropdown').removeClass('show');
            notificationDropdownOpen = false;
        }
    });
    
    $(document).click(function() {
        if (notificationDropdownOpen) {
            $('#notificationDropdown').removeClass('show');
            notificationDropdownOpen = false;
        }
    });
    
    function loadNotifications() {
        $.get('{{ route("notifications") }}', function(notifications) {
            let html = '';
            let unreadCount = 0;
            
            if (notifications.length === 0) {
                html = '<div class="notification-item">No notifications</div>';
            } else {
                notifications.forEach(function(notification) {
                    if (notification.status === 'pending') unreadCount++;
                    
                    let appType = notification.application ? notification.application.req_type.replace('_', ' ') : 'Application';
                    let createdBy = notification.created_by ? notification.created_by.username : 'Admin';
                    let timeAgo = new Date(notification.created_at).toLocaleDateString();
                    
                    html += `
                        <div class="notification-item ${notification.status === 'pending' ? 'unread' : ''}" data-id="${notification.id}">
                            <div style="font-weight: 600; margin-bottom: 4px;">${appType} Update</div>
                            <div style="font-size: 0.875rem; color: #666; margin-bottom: 4px;">From: ${createdBy}</div>
                            <div style="font-size: 0.75rem; color: #999;">${timeAgo}</div>
                        </div>
                    `;
                });
            }
            
            $('#notificationList').html(html);
            
            if (unreadCount > 0) {
                $('#notificationCount').text(unreadCount).show();
            } else {
                $('#notificationCount').hide();
            }
        });
    }
    
    $(document).on('click', '.notification-item[data-id]', function() {
        let notificationId = $(this).data('id');
        $(this).removeClass('unread');
        
        $.post(`/notifications/${notificationId}/read`, {
            _token: '{{ csrf_token() }}'
        });
    });
    
    // Load notifications on page load
    loadNotifications();
    
    // Refresh notifications every 30 seconds
    setInterval(loadNotifications, 30000);
});
</script>
@stack('page-scripts')
@endpush