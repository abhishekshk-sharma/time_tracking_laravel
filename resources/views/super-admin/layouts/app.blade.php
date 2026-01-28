<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Super Admin Console') - Time Tracking System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <style>
        :root {
            /* --- Google Material 3 Token Palette --- */
            --md-primary: #0b57d0;
            --md-on-primary: #ffffff;
            --md-primary-container: #d3e3fd;
            --md-on-primary-container: #041e49;
            
            --md-surface: #ffffff;
            --md-surface-dim: #f8f9fa; 
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
            
            --elevation-hover: 0 4px 8px 3px rgba(129, 126, 126, 0.15), 0 1px 3px rgba(116, 116, 116, 0.3);
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

        body::-webkit-scrollbar{
            display: none;
        }

        /* --- 1. Header --- */
        .admin-header {
            background-color: linear-gradient(135deg, #eeedfa, #f8eaf0);;
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
        }
        
        .admin-logo i { color: #d93025; font-size: 22px; }

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
        }

        /* --- 2. Sidebar --- */
        .admin-sidebar {
            position: fixed;
            left: 0; top: var(--header-height); bottom: 0;
            width: var(--sidebar-width);
            background-color: var(--md-surface);
            padding: 16px 12px;
            overflow-y: auto;
            z-index: 1100;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .admin-sidebar::-webkit-scrollbar {
            width: 0;
            height: 0;
            display: none;
        }
        /* Sidebar Navigation & Groups */
        .nav-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--md-outline);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 24px 0 8px 24px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 48px;
            padding: 0 24px;
            color: var(--md-text-sub);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border-radius: var(--radius-btn);
            margin-bottom: 4px;
            transition: all 0.3s var(--ease-calm);
            cursor: pointer;
        }

        .nav-link-content { display: flex; align-items: center; gap: 12px; }

        .nav-link i.nav-icon {
            font-size: 20px;
            width: 24px;
            text-align: center;
            color: var(--md-text-sub);
        }

        .nav-link:hover {
            border-radius: 7px;
            background-color: rgba(11, 87, 208, 0.08);
            color: var(--md-text-main);
        }

        .nav-link.active {
            border-radius: 7px;
            background-color: var(--md-primary-container);
            color: var(--md-on-primary-container);
            font-weight: 600;
        }
        .nav-link.active i.nav-icon { color: var(--md-on-primary-container); }

        /* Sub-menu Styles */
        .sub-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s var(--ease-calm);
            padding-left: 0;
            list-style: none;
        }

        .sub-menu.open { max-height: 500px; }

        .sub-nav-link {
            display: flex;
            align-items: center;
            height: 40px;
            padding-left: 60px; /* Indented */
            padding-right: 24px;
            color: var(--md-text-sub);
            text-decoration: none;
            font-size: 13.5px;
            border-radius: var(--radius-btn);
            margin-bottom: 2px;
        }

        .sub-nav-link:hover {
            border-radius: 7px;
            background-color: rgba(0,0,0,0.04);
            color: var(--md-text-main);
        }

        .sub-nav-link.active {
            border-radius: 7px;
            color: var(--md-primary);
            background-color: rgba(11, 87, 208, 0.08);
            font-weight: 600;
        }

        .chevron-icon { font-size: 12px; transition: transform 0.3s; }
        
        /* Rotate chevron when Expanded */
        .nav-link[aria-expanded="true"] .chevron-icon { transform: rotate(180deg); }

        /* --- 3. Main Content --- */
        .admin-main {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 32px;
        }

        /* --- Global Components --- */
        .card {
            background-color: var(--md-surface);
            border: 1px solid var(--md-outline-variant);
            border-radius: var(--radius-card);
            margin-bottom: 24px;
            position: relative;
            transition: all 0.4s var(--ease-calm);
        }
        .card:hover { transform: translateY(-2px); box-shadow: var(--elevation-hover); border-color: transparent; }
        .card-header { background: linear-gradient(135deg, #eeedfa, #f8eaf0);; padding: 24px 28px 8px; border-bottom: none; display: flex; justify-content: space-between; align-items: center; }
        .card-title { font-size: 20px; color: var(--md-text-main); font-weight: 400; }
        .card-body { padding: 28px; }

        /* Buttons */
        .btn { border-radius: 7px; padding: 7px 15px; font-weight: 500; border: none; transition: all 0.3s; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;}
        .btn-primary { background-color: var(--md-primary); color: #fff; box-shadow: 0 1px 2px rgba(0,0,0,0.2); }
        .btn-primary:hover { background-color: #0b57d0; box-shadow: 0 4px 8px rgba(11, 87, 208, 0.3); transform: translateY(-1px); }
        .btn-secondary { background: transparent; border: 1px solid var(--md-outline-variant); color: var(--md-primary); }
        .btn-secondary:hover { background-color: rgba(11, 87, 208, 0.04); color: rgb(3, 3, 56);}
        .form-group{
            margin-bottom: 15px;
        }
        

        /* Edit modal */

        #editModal{
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 15000;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        /* Forms */
        .form-control { border: 1px solid rgba(55, 55, 55, 0.235);; border-radius: 4px; padding: 8px 12px; width: 100%; transition: 0.2s; font-size: 14px; }
        .form-control:focus { outline: none; border-color: var(--md-primary); box-shadow: 0 0 0 2px #fff, 0 0 0 4px var(--md-primary); }

        /* Tables */
        .table-container { border: 1px solid var(--md-outline-variant); border-radius: var(--radius-card); overflow: hidden; background: white; position: relative; overflow-x: auto;}
        .table { width: 100%; border-collapse: collapse; }
        .table th { padding: 16px 24px; background: #fff; color: var(--md-text-sub); font-weight: 500; border-bottom: 1px solid var(--md-outline-variant); text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }
        .table td { padding: 18px 24px; border-bottom: 1px solid var(--md-outline-variant); color: var(--md-text-main); vertical-align: middle; }
        .table tr:hover td { background-color: #f8fafe; }
        
        /* Sticky Column */
        .table th:last-child, .table td:last-child { position: sticky; right: 0; z-index: 10; }
        .table th:last-child { box-shadow: -2px 0 4px rgba(0,0,0,0.02); }
        .table td:last-child { border-left: 1px solid var(--md-outline-variant); background: #fff; }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 24px; }
        .stat-card { background: #fff; border: 1px solid var(--md-outline-variant); border-radius: var(--radius-card); padding: 24px; transition: 0.3s; }
        .stat-card:hover { border-color: var(--md-primary); transform: scale(1.02); }
        .stat-number { font-size: 36px; color: var(--md-text-main); }
        .stat-label { font-size: 13px; color: var(--md-text-sub); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }

        /* Alerts */
        .alert { border-radius: 8px; padding: 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; border: none; }
        .alert-success { background: #e6f4ea; color: #1e8e3e; }
        .alert-danger { background: #fce8e6; color: #d93025; }

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
        <a href="{{ route('super-admin.dashboard') }}" class="admin-logo">
            <i class="fas fa-shield-halved"></i>
            <span>Super Admin</span>
        </a>

        <div class="admin-user">
            <div class="notification-bell" id="notificationBell" style="margin-right: 16px; position: relative; cursor: pointer;">
                <i class="fas fa-bell" style="font-size: 18px; color: var(--md-text-sub);"></i>
                <span class="notification-badge" id="notificationCount" style="position: absolute; top: -8px; right: -8px; background: #dc2626; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 11px; display: none; align-items: center; justify-content: center; font-weight: 600; padding-left: 4px;">0</span>
                <div class="notification-dropdown" id="notificationDropdown" style="position: absolute; top: 100%; right: 0; background: white; border: 1px solid var(--md-outline-variant); border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.15); width: 320px; max-height: 400px; overflow-y: auto; z-index: 1000; display: none; margin-top: 8px;">
                    <div id="notificationList" style="padding: 8px;">Loading...</div>
                </div>
            </div>
            <span style="font-size: 14px; font-weight: 500; color: #444;">Hi, 
                {{ auth('super_admin')->check() ? auth('super_admin')->user()->username : 'Super Admin' }}
            </span>
            <a href="{{ route('super-admin.logout') }}" class="logout-btn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Sign out
            </a>
            <form id="logout-form" action="{{ route('super-admin.logout') }}" method="POST" style="display: none;">@csrf</form>
        </div>
    </header>

    <nav class="admin-sidebar">
        <div class="sidebar-nav">
            
            <div class="nav-item">
                <a href="{{ route('super-admin.dashboard') }}" class="nav-link {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
                    <div class="nav-link-content"><i class="fas fa-chart-pie nav-icon"></i> Dashboard</div>
                </a>
            </div>

            <div class="nav-label">Management</div>

            <div class="nav-item">
                <a href="{{ route('super-admin.admins') }}" class="nav-link {{ request()->routeIs('super-admin.admins*') ? 'active' : '' }}">
                    <div class="nav-link-content"><i class="fas fa-user-shield nav-icon"></i> Administrators</div>
                </a>
            </div>

            @php
                $isWorkforceActive = request()->routeIs('super-admin.employees*') || request()->routeIs('super-admin.employee-history*') || request()->routeIs('super-admin.attendance*') || request()->routeIs('super-admin.time-entry-images*') ;
            @endphp
            <div class="nav-item">
                <div class="nav-link {{ $isWorkforceActive ? 'active' : '' }}" onclick="toggleMenu('menu-workforce')" aria-expanded="{{ $isWorkforceActive ? 'true' : 'false' }}">
                    <div class="nav-link-content"><i class="fas fa-users nav-icon"></i> Workforce</div>
                    <i class="fas fa-chevron-down chevron-icon"></i>
                </div>
                <ul id="menu-workforce" class="sub-menu {{ $isWorkforceActive ? 'open' : '' }}">
                    <li><a href="{{ route('super-admin.employees') }}" class="sub-nav-link {{ request()->routeIs('super-admin.employees*') ? 'active' : '' }}">All Employees</a></li>
                    <li><a href="{{ route('super-admin.attendance') }}" class="sub-nav-link {{ request()->routeIs('super-admin.attendance*') ? 'active' : '' }}">Daily Attendance</a></li>
                    <li><a href="{{ route('super-admin.time-entry-images') }}" class="sub-nav-link {{ request()->routeIs('super-admin.time-entry-images*') ? 'active' : '' }}">Entry Images</a></li>
                    <li><a href="{{ route('super-admin.employee-history') }}" class="sub-nav-link {{ request()->routeIs('super-admin.employee-history*') ? 'active' : '' }}">History Logs</a></li>
                    
                </ul>
            </div>

            <div class="nav-label">Operations</div>

            @php
                $isTimeActive =  request()->routeIs('super-admin.time-entries*')  || request()->routeIs('super-admin.schedule*') || request()->routeIs('super-admin.applications*');
            @endphp
            <div class="nav-item">
                <div class="nav-link {{ $isTimeActive ? 'active' : '' }}" onclick="toggleMenu('menu-time')" aria-expanded="{{ $isTimeActive ? 'true' : 'false' }}">
                    <div class="nav-link-content"><i class="fas fa-clock nav-icon"></i> Time & Attendance</div>
                    <i class="fas fa-chevron-down chevron-icon"></i>
                </div>
                <ul id="menu-time" class="sub-menu {{ $isTimeActive ? 'open' : '' }}">
                    
                    <li><a href="{{ route('super-admin.time-entries') }}" class="sub-nav-link {{ request()->routeIs('super-admin.time-entries*') ? 'active' : '' }}">All Entries</a></li>
                    
                    <li><a href="{{ route('super-admin.schedule') }}" class="sub-nav-link {{ request()->routeIs('super-admin.schedule*') ? 'active' : '' }}">Schedule/Calendar</a></li>
                    <li><a href="{{ route('super-admin.applications') }}" class="sub-nav-link {{ request()->routeIs('super-admin.applications*') ? 'active' : '' }}">Applications</a></li>
                </ul>
            </div>

            @php
                $isOrgActive = request()->routeIs('super-admin.departments*') || request()->routeIs('super-admin.regions*') || request()->routeIs('super-admin.location-settings*') || request()->routeIs('super-admin.leave-days*') || request()->routeIs('super-admin.tax-slabs*');
            @endphp
            <div class="nav-item">
                <div class="nav-link {{ $isOrgActive ? 'active' : '' }}" onclick="toggleMenu('menu-org')" aria-expanded="{{ $isOrgActive ? 'true' : 'false' }}">
                    <div class="nav-link-content"><i class="fas fa-building nav-icon"></i> Organization</div>
                    <i class="fas fa-chevron-down chevron-icon"></i>
                </div>
                <ul id="menu-org" class="sub-menu {{ $isOrgActive ? 'open' : '' }}">
                    <li><a href="{{ route('super-admin.departments') }}" class="sub-nav-link {{ request()->routeIs('super-admin.departments*') ? 'active' : '' }}">Departments</a></li>
                    <li><a href="{{ route('super-admin.regions') }}" class="sub-nav-link {{ request()->routeIs('super-admin.regions*') ? 'active' : '' }}">Branch</a></li>
                    <li><a href="{{ route('super-admin.location-settings.index') }}" class="sub-nav-link {{ request()->routeIs('super-admin.location-settings*') ? 'active' : '' }}">Location Settings</a></li>
                    <li><a href="{{ route('super-admin.leave-days') }}" class="sub-nav-link {{ request()->routeIs('super-admin.leave-days*') ? 'active' : '' }}">Leave Days Settings</a></li>
                    <li><a href="{{ route('super-admin.tax-slabs') }}" class="sub-nav-link {{ request()->routeIs('super-admin.tax-slabs*') ? 'active' : '' }}">Payroll Settings</a></li>
                </ul>
            </div>

            <div class="nav-item">
                <a href="{{ route('super-admin.salaries') }}" class="nav-link {{ request()->routeIs('super-admin.salaries*') ? 'active' : '' }}">
                    <div class="nav-link-content"><i class="fas fa-file-invoice-dollar nav-icon"></i> Payroll & Salary</div>
                </a>
            </div>

            <div class="nav-label">System</div>

            <div class="nav-item">
                <a href="{{ route('super-admin.reports') }}" class="nav-link {{ request()->routeIs('super-admin.reports*') ? 'active' : '' }}">
                    <div class="nav-link-content"><i class="fas fa-chart-bar nav-icon"></i> Reports</div>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('super-admin.notifications') }}" class="nav-link {{ request()->routeIs('super-admin.notifications*') ? 'active' : '' }}">
                    <div class="nav-link-content"><i class="fas fa-bell nav-icon"></i> Notifications</div>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('super-admin.settings') }}" class="nav-link {{ request()->routeIs('super-admin.settings*') ? 'active' : '' }}">
                    <div class="nav-link-content"><i class="fas fa-sliders-h nav-icon"></i> Settings</div>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('super-admin.profile') }}" class="nav-link {{ request()->routeIs('super-admin.profile*') ? 'active' : '' }}">
                    <div class="nav-link-content"><i class="fas fa-user-cog nav-icon"></i> Profile</div>
                </a>
            </div>

        </div>
    </nav>

    <main class="admin-main">
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        // Sidebar Toggle Logic
        function toggleMenu(menuId) {
            const menu = document.getElementById(menuId);
            const parentLink = menu.previousElementSibling;
            
            // Toggle Open Class
            menu.classList.toggle('open');
            
            // Rotate Chevron
            const isExpanded = menu.classList.contains('open');
            parentLink.setAttribute('aria-expanded', isExpanded);
        }
        
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
            $.get('{{ route("super-admin.notifications") }}', function(notifications) {
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
                            <div style="padding: 12px 16px; border-bottom: 1px solid #eee; cursor: pointer; transition: background 0.2s; ${notification.status === 'pending' ? 'background: rgba(11, 87, 208, 0.05); border-left: 3px solid var(--md-primary);' : ''}" data-id="${notification.id}" onclick="window.location.href='{{ route('super-admin.notifications') }}'" onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='${notification.status === 'pending' ? 'rgba(11, 87, 208, 0.05)' : 'white'}'">
                                <div style="font-weight: 600; margin-bottom: 4px; text-transform: capitalize;">${appType} Submitted</div>
                                <div style="font-size: 13px; color: #666; margin-bottom: 4px;">From: ${createdBy}</div>
                                <div style="font-size: 12px; color: #999;">${timeAgo}</div>
                            </div>
                        `;
                    });
                }
                
                $('#notificationList').html(html);
                
                if (unreadCount > 0) {
                    if(unreadCount > 9){

                        $('#notificationCount').text('9+').show();
                    }else{

                        $('#notificationCount').text(unreadCount).show();
                    }
                } else {
                    $('#notificationCount').hide();
                }
            });
        }
        
        $(document).on('click', '[data-id]', function() {
            let notificationId = $(this).data('id');
            $(this).css('background', 'white').css('border-left', 'none');
            
            $.post(`/super-admin/notifications/${notificationId}/read`, {
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