<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - Time Tracking System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        :root {
            /* --- Google Material 3 Token Palette --- */
            --md-primary: #0b57d0;
            --md-on-primary: #ffffff;
            --md-primary-container: #d3e3fd;
            --md-on-primary-container: #041e49;
            
            --md-surface: #ffffff;
            --md-surface-dim: #f8f9fa; /* Light gray background */
            --md-outline: #747775;
            --md-outline-variant: #e0e2e6;
            
            --md-text-main: #1f1f1f;
            --md-text-sub: #444746;
            
            /* --- Dimensions & Animation --- */
            --header-height: 64px;
            --sidebar-width: 280px;
            --radius-card: 16px;
            --radius-btn: 100px;
            --ease-calm: cubic-bezier(0.2, 0.0, 0, 1.0);
            
            /* --- Shadows for "Lift" effect --- */
            --elevation-hover: 0 4px 8px 3px rgba(105, 104, 104, 0.15), 0 1px 3px rgba(111, 111, 111, 0.3);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--md-surface-dim);
            color: var(--md-text-main);
            font-size: 14px;
            line-height: 20px;
            overflow-x: hidden;
        }

        /* --- 1. Interactive Header --- */
        .admin-header {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            height: var(--header-height);
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1200;
            border-bottom: 1px solid var(--md-outline-variant);
            transition: all 0.3s var(--ease-calm);
        }

        .admin-logo {
            font-family: 'Open Sans', sans-serif;
            font-size: 20px;
            font-weight: 600;
            color: var(--md-text-sub);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: transform 0.3s var(--ease-calm);
        }
        
        .admin-logo:hover { transform: scale(1.02); }
        .admin-logo i { font-size: 24px; color: var(--md-primary); }

        .admin-user { display: flex; align-items: center; gap: 16px; }

        .logout-btn {
            background: transparent;
            color: var(--md-primary);
            border: 1px solid var(--md-outline-variant);
            padding: 8px 24px;
            border-radius: var(--radius-btn);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s var(--ease-calm);
            font-size: 14px;
        }

        .logout-btn:hover {
            background-color: var(--md-primary-container);
            color: var(--md-on-primary-container);
            border-color: var(--md-primary-container);
            transform: translateY(-1px);
        }

        /* --- 2. "Calm" Sidebar --- */
        .admin-sidebar {
            position: fixed;
            left: 0; top: var(--header-height); bottom: 0;
            width: var(--sidebar-width);
            background-color: var(--md-surface);
            padding: 16px 12px;
            overflow-y: auto;
            z-index: 1100;
        }

        .nav-link {
            display: flex;
            align-items: center;
            height: 50px;
            padding: 0 24px;
            color: var(--md-text-sub);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border-radius: 7px;
            margin-bottom: 4px;
            transition: all 0.3s var(--ease-calm);
        }

        .nav-link i {
            font-size: 20px;
            width: 32px;
            margin-right: 12px;
            color: var(--md-text-sub);
            transition: color 0.3s var(--ease-calm);
        }

        /* Hover Interaction: Bloom */
        .nav-link:hover {
            background-color: rgba(11, 87, 208, 0.08);
            color: var(--md-text-main);
            padding-left: 28px;
        }

        /* Active State: The "Filled" Pill */
        .nav-link.active {
            background-color: var(--md-primary-container);
            color: var(--md-on-primary-container);
            font-weight: 600;
        }

        .nav-link.active i { color: var(--md-on-primary-container); }

        /* --- 3. Main Content Area --- */
        .admin-main {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 32px;
        }

        /* --- 4. Interactive Cards --- */
        .card {
            background-color: var(--md-surface);
            border: 1px solid var(--md-outline-variant);
            border-radius: var(--radius-card);
            margin-bottom: 24px;
            transition: all 0.4s var(--ease-calm);
            position: relative;
        }

        /* Lift Animation */
        .card:hover {
            transform: translateY(-2px);
            box-shadow: var(--elevation-hover);
            border-color: transparent;
        }

        .card-header {
            background: linear-gradient(135deg, #eeedfa, #f8eaf0);;
            padding: 24px 28px 8px;
            border-bottom: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 20px;
            color: var(--md-text-main);
            font-weight: 400;
        }

        .card-body { padding: 28px; }

        /* --- 5. Stats Cards --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #fff;
            border: 1px solid var(--md-outline-variant);
            border-radius: var(--radius-card);
            padding: 24px;
            transition: all 0.3s var(--ease-calm);
        }

        .stat-card:hover {
            border-color: var(--md-primary);
            background-color: #fbfdff;
            transform: scale(1.02);
        }

        .stat-number {
            font-size: 36px;
            color: var(--md-text-main);
            margin-bottom: 4px;
            transition: color 0.3s;
        }
        .stat-card:hover .stat-number { color: var(--md-primary); }

        /* --- 6. Buttons --- */
        .btn {
            border-radius: 7px;
            padding: 7px 15px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s var(--ease-calm);
        }

        .btn:active { transform: scale(0.96); }

        .btn-primary {
            background-color: var(--md-primary);
            color: var(--md-on-primary);
            box-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }

        .btn-primary:hover {
            background-color: #0b57d0;
            box-shadow: 0 4px 8px rgba(11, 87, 208, 0.3);
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background-color: transparent;
            border: 1px solid var(--md-outline-variant);
            color: var(--md-primary);
        }
        
        .btn-secondary:hover {
            background-color: rgba(11, 87, 208, 0.04);
            color: rgb(30, 2, 121);
        }

        button[type='submit']{
            margin-top: 10px;
        }   

        /* --- 7. Forms --- */
        .form-control {
            border: 1px solid rgba(55, 55, 55, 0.235);
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 14px;
            color: var(--md-text-main);
            width: 100%;
            background-color: var(--md-surface);
            transition: all 0.2s var(--ease-calm);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--md-primary);
            box-shadow: 0 0 0 2px var(--md-surface), 0 0 0 4px var(--md-primary);
        }

        /* --- 9. Page Header --- */
        .page-header {
            margin-bottom: 32px;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 400;
            color: var(--md-text-main);
            margin-bottom: 8px;
        }
        
        .page-subtitle {
            color: var(--md-text-sub);
            font-size: 16px;
            margin: 0;
        }
        
        /* --- 8. Tables (Sticky & Clean) --- */
        .table-container {
            border: 1px solid var(--md-outline-variant);
            border-radius: var(--radius-card);
            overflow: hidden;
            background: white;
            position: relative;
            overflow-x: auto;
        }

        .table { width: 100%; border-collapse: collapse; margin: 0; }

        .table th {
            padding: 16px 24px;
            background: #fff;
            color: var(--md-text-sub);
            font-weight: 500;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--md-outline-variant);
        }

        .table td {
            padding: 18px 24px;
            border-bottom: 1px solid var(--md-outline-variant);
            color: var(--md-text-main);
            background: #fff;
        }

        .table tr:hover td { background-color: #f8fafe; }

        /* Sticky Action Column */
        .table th:last-child, .table td:last-child {
            position: sticky; right: 0; z-index: 10;
        }
        .table th:last-child { box-shadow: -2px 0 4px rgba(0,0,0,0.02); }
        .table td:last-child { border-left: 1px solid var(--md-outline-variant); }
        
        /* Mobile */
        @media (max-width: 991px) {
            .admin-sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .admin-main { margin-left: 0; padding: 20px; }
        }
    </style>
    @stack('styles')
</head>
<body>

    <header class="admin-header">
        <a href="{{ route('admin.dashboard') }}" class="admin-logo">
            <i class="fas fa-chart-pie"></i>
            <span>Admin Console</span>
        </a>

        <div class="admin-user">
            <div class="notification-bell" id="notificationBell" style="margin-right: 16px; position: relative; cursor: pointer;">
                <i class="fas fa-bell" style="font-size: 18px; color: var(--md-text-sub);"></i>
                <span class="notification-badge" id="notificationCount" style="position: absolute; top: -8px; right: -8px; background: #dc2626; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 11px; display: none; align-items: center; justify-content: center; font-weight: 600;">0</span>
                <div class="notification-dropdown" id="notificationDropdown" style="position: absolute; top: 100%; right: 0; background: white; border: 1px solid var(--md-outline-variant); border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.15); width: 320px; max-height: 400px; overflow-y: auto; z-index: 1000; display: none; margin-top: 8px;">
                    <div id="notificationList" style="padding: 8px;">Loading...</div>
                </div>
            </div>
            <span style="font-size: 14px; font-weight: 500; color: #444;">
                Hi, {{ auth()->check() ? (auth()->user()->username ?? auth()->user()->name) : 'Guest' }}
            </span>
            <a href="{{ route('logout') }}" class="logout-btn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Sign out
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
        </div>
    </header>

    <nav class="admin-sidebar">
        <div class="sidebar-nav">
            <div class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('admin.employees') }}" class="nav-link {{ request()->routeIs('admin.employees*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> Employees
                </a>
            </div>

            {{-- <div class="nav-item">
                <a href="{{ route('admin.departments') }}" class="nav-link {{ request()->routeIs('admin.departments*') ? 'active' : '' }}">
                    <i class="fas fa-building"></i> Departments
                </a>
            </div> --}}

            <div class="nav-item">
                <a href="{{ route('admin.attendance') }}" class="nav-link {{ request()->routeIs('admin.attendance*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-day"></i> Attendance
                </a>
            </div>
            
            {{-- <div class="nav-item">
                <a href="{{ route('admin.time-entries') }}" class="nav-link {{ request()->routeIs('admin.time-entries*') ? 'active' : '' }}">
                    <i class="fas fa-clock"></i> Time Entries
                </a>
            </div> --}}

            <div class="nav-item">
                <a href="{{ route('admin.applications') }}" class="nav-link {{ request()->routeIs('admin.applications*') ? 'active' : '' }}">
                    <i class="fas fa-file-contract"></i> Applications
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('admin.salaries') }}" class="nav-link {{ request()->routeIs('admin.salaries*') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave"></i> Salaries
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('admin.employee-history') }}" class="nav-link {{ request()->routeIs('admin.employee-history*') ? 'active' : '' }}">
                    <i class="fas fa-history"></i> Employee History
                </a>
            </div>
            
            {{-- <div class="nav-item">
                <a href="{{ route('admin.wfh') }}" class="nav-link {{ request()->routeIs('admin.wfh*') ? 'active' : '' }}">
                    <i class="fas fa-laptop-house"></i> Work From Home
                </a>
            </div> --}}

            <div class="nav-item">
                <a href="{{ route('admin.reports') }}" class="nav-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('admin.schedule') }}" class="nav-link {{ request()->routeIs('admin.schedule*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i> Schedule
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.location-settings.index') }}" class="nav-link {{ request()->routeIs('admin.location-settings*') ? 'active' : '' }}">
                    <i class="fas fa-map-marker-alt"></i> Location Settings
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('admin.profile') }}" class="nav-link {{ request()->routeIs('admin.profile*') ? 'active' : '' }}">
                    <i class="fas fa-user-circle"></i> Profile
                </a>
            </div>

            {{-- <div class="nav-item">
                <a href="{{ route('admin.settings') }}" class="nav-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                    <i class="fas fa-sliders-h"></i> Settings
                </a>
            </div> --}}
        </div>
    </nav>

    <main class="admin-main">
        @if(session('success'))
            <div class="alert alert-success" style="background:#e6f4ea; color:#1e8e3e; padding:16px; border-radius:12px; margin-bottom:24px; border:none; display:flex; gap:12px; align-items:center;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger" style="background:#fce8e6; color:#d93025; padding:16px; border-radius:12px; margin-bottom:24px; border:none; display:flex; gap:12px; align-items:center;">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        
        // Notification functionality
        let notificationDropdownOpen = false;
        
        $('#notificationBell').click(function(e) {
            e.stopPropagation();
            if (!notificationDropdownOpen) {
                loadNotifications();
                $('#notificationDropdown').show();
                notificationDropdownOpen = true;
            } else {
                $('#notificationDropdown').hide();
                notificationDropdownOpen = false;
            }
        });
        
        $(document).click(function() {
            if (notificationDropdownOpen) {
                $('#notificationDropdown').hide();
                notificationDropdownOpen = false;
            }
        });
        
        function loadNotifications() {
            $.get('{{ route("admin.notifications") }}', function(notifications) {
                let html = '';
                let unreadCount = 0;
                
                if (notifications.length === 0) {
                    html = '<div style="padding: 16px; text-align: center; color: #666;">No notifications</div>';
                } else {
                    notifications.forEach(function(notification) {
                        if (notification.status === 'pending') unreadCount++;
                        
                        let appType = notification.application ? notification.application.req_type.replace('_', ' ') : 'Application';
                        let createdBy = notification.created_by ? notification.created_by.username : 'Employee';
                        let timeAgo = new Date(notification.created_at).toLocaleDateString();
                        
                        html += `
                            <div style="padding: 12px 16px; border-bottom: 1px solid #eee; cursor: pointer; transition: background 0.2s; ${notification.status === 'pending' ? 'background: rgba(11, 87, 208, 0.05); border-left: 3px solid var(--md-primary);' : ''}" data-id="${notification.id}" onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='${notification.status === 'pending' ? 'rgba(11, 87, 208, 0.05)' : 'white'}'">
                                <div style="font-weight: 600; margin-bottom: 4px; text-transform: capitalize;">${appType} Submitted</div>
                                <div style="font-size: 13px; color: #666; margin-bottom: 4px;">From: ${createdBy}</div>
                                <div style="font-size: 12px; color: #999;">${timeAgo}</div>
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
        
        $(document).on('click', '[data-id]', function() {
            let notificationId = $(this).data('id');
            $(this).css('background', 'white').css('border-left', 'none');
            
            $.post(`/admin/notifications/${notificationId}/read`, {
                _token: '{{ csrf_token() }}'
            });
        });
        
        // Load notifications on page load
        loadNotifications();
        
        // Refresh notifications every 30 seconds
        setInterval(loadNotifications, 30000);
    </script>
    @stack('scripts')
</body>
</html>