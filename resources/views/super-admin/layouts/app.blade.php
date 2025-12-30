<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Super Admin Panel') - Time Tracking System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Amazon+Ember:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Styles -->
    <style>
        :root {
            --lux-blue: #0071e3; 
            --lux-bg: #f5f5f7;   
            --lux-surface: rgba(255, 255, 255, 0.85);
            --lux-border: rgba(0, 0, 0, 0.08);
            --lux-text: #1d1d1f;
            --sidebar-width: 260px;
            --ease-apple: cubic-bezier(0.28, 0.11, 0.32, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background-color: var(--lux-bg);
            color: var(--lux-text);
            letter-spacing: -0.01em;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        .admin-header {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: saturate(180%) blur(20px);
            height: 64px;
            padding: 0 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1001;
            border-bottom: 0.5px solid var(--lux-border);
        }

        .admin-logo { 
            font-weight: 600; 
            font-size: 18px; 
            color: #000; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        .admin-logo i { 
            color: #ff6b35; 
            font-size: 20px; 
        }

        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 64px;
            width: var(--sidebar-width);
            height: calc(100vh - 64px);
            background: #f2f2f7;
            padding: 24px 16px;
            z-index: 1000;
            border-right: 1px solid rgba(0, 0, 0, 0.04);
            box-shadow: inset -10px 0 20px -15px rgba(0,0,0,0.05);
        }

        .nav-item { margin-bottom: 4px; }
        .nav-link {
            display: flex;
            align-items: center;
            padding: 10px 18px;
            color: #86868b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border-radius: 12px;
            transition: all 0.4s var(--ease-apple);
        }

        .nav-link i { width: 22px; margin-right: 12px; font-size: 17px; opacity: 0.8; }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.8);
            color: #ff6b35;
        }

        .nav-link.active {
            background: #ffffff;
            color: #ff6b35;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        }
        .nav-link.active i { color: #ff6b35; opacity: 1; }

        .admin-main {
            margin-left: var(--sidebar-width);
            margin-top: 64px;
            padding: 40px 50px;
            animation: boxUnfold 0.9s var(--ease-apple);
        }

        @keyframes boxUnfold {
            0% { opacity: 0; transform: translateY(15px) scale(0.99); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 10px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: #ffffff;
            padding: 28px;
            border-radius: 24px;
            border: 0.5px solid var(--lux-border);
            box-shadow: 0 2px 4px rgba(0,0,0,0.01);
            transition: all 0.5s var(--ease-apple);
            display: flex;
            flex-direction: column;
        }

        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.06);
        }

        .stat-number {
            font-size: 38px;
            font-weight: 700;
            color: #1d1d1f;
            letter-spacing: -0.04em;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 12px;
            font-weight: 600;
            color: #86868b;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .card {
            background: #ffffff;
            border-radius: 24px;
            border: 0.5px solid var(--lux-border);
            box-shadow: 0 4px 6px rgba(0,0,0,0.01);
            overflow: hidden;
            margin-bottom: 30px;
            transition: box-shadow 0.4s var(--ease-apple);
        }
        
        .card:hover { box-shadow: 0 15px 35px rgba(0,0,0,0.04); }

        .card-header {
            padding: 24px 30px;
            background: #fff;
            border-bottom: 0.5px solid var(--lux-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title { font-size: 17px; font-weight: 600; color: #1d1d1f; }
        .card-body { padding: 30px; }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 24px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.4s var(--ease-apple);
            cursor: pointer;
            border: none;
            gap: 8px;
        }

        .btn-primary { 
            background: #ff6b35; 
            color: white; 
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.2);
        }
        .btn-primary:hover { 
            background: #ff5722; 
            transform: scale(1.05); 
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4); 
        }

        .btn-secondary {
            background: #f5f5f7;
            color: #1d1d1f;
        }
        .btn-secondary:hover { background: #e8e8ed; transform: translateY(-2px); }

        .table-container { border-radius: 20px; overflow: scroll; border: 0.5px solid var(--lux-border); }
        
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            background: #fff; 
        }
        .table th {
            padding: 18px 24px;
            background: #fbfbfd;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #86868b;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            border-bottom: 0.5px solid var(--lux-border);
        }
        .table td { padding: 20px 24px; border-bottom: 0.5px solid var(--lux-border); font-size: 14px; }
        .table tr:last-child td { border-bottom: none; }
        .table tr:hover td { background: #fdfdfd; }

        .table th:last-child, 
        .table td:last-child {
            position: sticky;
            right: 0;
            z-index: 10;
            background: inherit;
        }

        .table th:last-child {
            background: #fbfbfd;
            box-shadow: -4px 0 8px rgba(0, 0, 0, 0.03); 
        }

        .table td:last-child {
            background: #ffffff;
            border-left: 0.5px solid var(--lux-border);
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d2d2d7; border-radius: 10px; }

        .table-container::-webkit-scrollbar, .table::-webkit-scrollbar{
            display: none !important;
        }

        a {
            color: #ff6b35;
            text-decoration: none;
            transition: all 0.3s var(--ease-apple);
        }

        a:hover {
            opacity: 0.7;
        }

        .page-header {
            margin-bottom: 48px;
            padding-bottom: 24px;
            border-bottom: 0.5px solid var(--lux-border);
            animation: slideDown 0.8s var(--ease-apple);
        }

        .page-title {
            font-size: 34px;
            font-weight: 700;
            color: #1d1d1f;
            letter-spacing: -0.02em;
            margin-bottom: 8px;
        }

        .page-subtitle {
            font-size: 17px;
            color: #86868b;
            font-weight: 400;
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .logout-btn {
            background: rgba(255, 59, 48, 0.08);
            color: #ff3b30;
            border: 0.5px solid rgba(255, 59, 48, 0.2);
            padding: 6px 16px;
            border-radius: 18px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.02em;
            transition: all 0.4s var(--ease-apple);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logout-btn:hover {
            background: #ff3b30;
            color: #ffffff;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(255, 59, 48, 0.3);
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #86868b;
            margin-bottom: 8px;
            display: block;
            margin-left: 4px;
        }

        .form-control {
            background: #ffffff;
            border: 0.5px solid var(--lux-border);
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 15px;
            color: var(--lux-text);
            width: 100%;
            transition: all 0.4s var(--ease-apple);
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
        }

        .form-control:focus {
            border-color: #ff6b35;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1), inset 0 1px 2px rgba(0,0,0,0.02);
            transform: translateY(-1px);
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 40px;
            margin-bottom: 20px;
            list-style: none;
            animation: fadeInUp 0.8s var(--ease-apple);
        }

        .pagination .page-item {
            display: inline-block;
        }

        .pagination .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 14px;
            background: #ffffff;
            color: #86868b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            border-radius: 12px;
            border: 0.5px solid var(--lux-border);
            transition: all 0.4s var(--ease-apple);
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .pagination .page-link:hover {
            color: #ff6b35;
            background: #ffffff;
            transform: translateY(-3px);
            border-color: rgba(255, 107, 53, 0.3);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }

        .pagination .page-item.active .page-link {
            background: #ff6b35;
            color: #ffffff;
            border-color: #ff6b35;
            box-shadow: 0 8px 15px rgba(255, 107, 53, 0.25);
            transform: translateY(-2px) scale(1.05);
        }

        .pagination .page-item.disabled .page-link {
            background: #fbfbfd;
            color: #d2d2d7;
            border-color: rgba(0, 0, 0, 0.03);
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border-color: #bbf7d0;
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border-color: #fecaca;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="admin-logo">
            <i class="fas fa-crown"></i> Super Admin Panel
        </div>
        <div class="admin-user">
            <span>Welcome, {{ auth('super_admin')->user()->username }}</span>
            <a href="{{ route('super-admin.logout') }}" class="logout-btn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
            <form id="logout-form" action="{{ route('super-admin.logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </header>

    <!-- Sidebar -->
    <nav class="admin-sidebar">
        <div class="sidebar-nav">
            <div class="nav-item">
                <a href="{{ route('super-admin.dashboard') }}" class="nav-link {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('super-admin.salaries') }}" class="nav-link {{ request()->routeIs('super-admin.salaries*') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave"></i>
                    Salary Management
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('super-admin.employees') }}" class="nav-link {{ request()->routeIs('super-admin.employees*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    Employees
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('super-admin.attendance') }}" class="nav-link {{ request()->routeIs('super-admin.attendance*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check"></i>
                    Attendance
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('super-admin.applications') }}" class="nav-link {{ request()->routeIs('super-admin.applications*') ? 'active' : '' }}">
                    <i class="fas fa-file-alt"></i>
                    Applications
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('super-admin.reports') }}" class="nav-link {{ request()->routeIs('super-admin.reports*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i>
                    Reports
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('super-admin.settings') }}" class="nav-link {{ request()->routeIs('super-admin.settings*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i>
                    Settings
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('super-admin.admins') }}" class="nav-link {{ request()->routeIs('super-admin.admins*') ? 'active' : '' }}">
                    <i class="fas fa-user-shield"></i>
                    Admin Management
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="admin-main">
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>