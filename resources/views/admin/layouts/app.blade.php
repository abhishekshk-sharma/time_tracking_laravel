<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - Time Tracking System</title>
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
            /* The "Slow Opening Box" Easing */
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

        /* --- Glossy Header --- */
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

        .admin-logo { font-weight: 600; font-size: 18px; color: #000; display: flex; align-items: center; gap: 10px; }
        .admin-logo i { color: var(--lux-blue); font-size: 20px; }

        /* --- The "Pill" Sidebar --- */
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 64px;
            width: var(--sidebar-width);
            height: calc(100vh - 64px);
            background: transparent;
            padding: 24px 16px;
            z-index: 1000;
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
            background: rgba(255, 255, 255, 0.5);
            color: var(--lux-blue);
            transform: translateX(5px);
        }

        .nav-link.active {
            background: #ffffff;
            color: #000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03), 0 1px 2px rgba(0,0,0,0.02);
        }
        .nav-link.active i { color: var(--lux-blue); opacity: 1; }

        /* --- Main Animation --- */
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

        /* --- The "Polished Product" Stats Grid --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 24px;
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

        /* --- Luxury Cards --- */
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

        /* --- Minimalist Buttons --- */
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
            background: var(--lux-blue); 
            color: white; 
            box-shadow: 0 4px 15px rgba(0, 113, 227, 0.2);
        }
        .btn-primary:hover { 
            background: #0077ed; 
            transform: scale(1.05); 
            box-shadow: 0 8px 25px rgba(0, 113, 227, 0.4); 
        }

        .btn-secondary {
            background: #f5f5f7;
            color: #1d1d1f;
        }
        .btn-secondary:hover { background: #e8e8ed; transform: translateY(-2px); }

        /* --- Clean Tables --- */
        .table-container { border-radius: 20px; overflow: scroll; border: 0.5px solid var(--lux-border); }

        
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            background: #fff; 
            /* overflow: hidden; */
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

        /* --- The Sticky Last Column Logic --- */
    .table-container {
        position: relative;
        overflow-x: auto; /* Enable horizontal scroll */
        scrollbar-width: thin; /* Clean scrollbar for Firefox */
        -webkit-overflow-scrolling: touch;
    }

    /* Target the last column (usually 'Actions') */
    .table th:last-child, 
    .table td:last-child {
        position: sticky;
        right: 0;
        z-index: 10; /* Ensure it stays above other content */
        background: inherit; /* Matches the row's background */
    }

    /* The "Glossy Anchor" Effect */
    .table th:last-child {
        background: #fbfbfd; /* Match the header's polished color */
        box-shadow: -4px 0 8px rgba(0, 0, 0, 0.03); 
        /* Subtle shadow to show it's "above" the scroll */
    }

    /* Visual separation when the table is scrolling */
    .table td:last-child {
        background: #ffffff;
        /*box-shadow: -8px 0 12px -8px rgba(0, 0, 0, 0.1);  "Elevated" feel while scrolling */
        border-left: 0.5px solid var(--lux-border);
    }

    /* Ensure the row hover doesn't break the sticky background */
    .table tr:hover td:last-child {
        /*background: #f5f5f7 !important;  Matches the luxury hover color we set before */
    }

        /* --- Custom Scrollbar --- */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d2d2d7; border-radius: 10px; }

    .table-container::-webkit-scrollbar, .table::-webkit-scrollbar{
            display: none !important;
        }

        /* --- Refined Typography & Global Anchors --- */
        a {
            color: var(--lux-blue);
            text-decoration: none;
            transition: all 0.3s var(--ease-apple);
        }

        a:hover {
            opacity: 0.7;
        }

        /* --- The Professional Page Header --- */
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

        /* --- High-End Header Navigation --- */
        .admin-header {
            /* Glass Effect */
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: saturate(180%) blur(20px);
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.05);
        }

        .admin-logo {
            transition: transform 0.4s var(--ease-apple);
            cursor: pointer;
        }

        .admin-logo:hover {
            transform: scale(1.02);
        }

        .admin-user span {
            font-size: 13px;
            color: #86868b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }

        /* --- Interactive Sidebar Layout --- */
        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        /* Custom Animation for Sidebar Entrance */
        .nav-item {
            opacity: 0;
            transform: translateX(-10px);
            animation: slideInLeft 0.5s var(--ease-apple) forwards;
        }

        /* Staggered animation for menu items */
        .nav-item:nth-child(1) { animation-delay: 0.1s; }
        .nav-item:nth-child(2) { animation-delay: 0.15s; }
        .nav-item:nth-child(3) { animation-delay: 0.2s; }
        .nav-item:nth-child(4) { animation-delay: 0.25s; }
        .nav-item:nth-child(5) { animation-delay: 0.3s; }

        /* --- Keyframes for the "Premium Feel" --- */
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-15px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* --- Global Action Icons --- */
        i {
            transition: transform 0.3s var(--ease-apple);
        }

        .nav-link:hover i {
            transform: scale(1.1) rotate(-5deg);
        }

        /* --- Table Action Links --- */
        .table td a {
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
        }

        .table td a:hover {
            background: #f5f5f7;
            opacity: 1;
            color: var(--lux-blue);
        }
        /* --- The Soft Matte Sidebar --- */
    .admin-sidebar {
        /* A very soft, desaturated blue-gray that feels high-end */
        background-color: #f2f2f7; 
        border-right: 1px solid rgba(0, 0, 0, 0.04);
        box-shadow: inset -10px 0 20px -15px rgba(0,0,0,0.05);
    }

    /* --- High-End Header & Logout Button --- */
    .admin-user {
        gap: 24px;
    }

    .logout-btn {
        background: rgba(255, 59, 48, 0.08); /* Apple System Red with low opacity */
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

    /* --- The "Recessed" Input Fields --- */
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
        /* Subtle inner shadow to make it feel "carved" into the card */
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
    }

    .form-control:focus {
        border-color: var(--lux-blue);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(0, 113, 227, 0.1), inset 0 1px 2px rgba(0,0,0,0.02);
        transform: translateY(-1px);
    }

    /* --- Refined Global Anchor Tags --- */
    a:not(.nav-link, .btn, .logout-btn) {
        color: var(--lux-blue);
        font-weight: 500;
        position: relative;
        transition: color 0.3s var(--ease-apple);
    }

    a:not(.nav-link, .btn, .logout-btn)::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 1.5px;
        bottom: -2px;
        left: 0;
        background-color: var(--lux-blue);
        transform: scaleX(0);
        transform-origin: bottom right;
        transition: transform 0.4s var(--ease-apple);
    }

    a:not(.nav-link, .btn, .logout-btn):hover::after {
        transform: scaleX(1);
        transform-origin: bottom left;
    }

    /* --- Improved Sidebar Interaction --- */
    .nav-link:hover {
        background: rgba(255, 255, 255, 0.8);
        color: var(--lux-blue);
    }

    .nav-link.active {
        background: #ffffff;
        color: var(--lux-blue);
        /* Soft outer glow for the active item */
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    }

    .admin-header {
        /* 1. The Glossy Surface */
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: saturate(180%) blur(20px); /* The "Frosted Glass" effect */
        
        /* 2. Precision Dimensions */
        height: 4rem;
        padding: 0 40px;
        
        /* 3. Layout: Professional Alignment */
        display: flex;
        align-items: center;
        justify-content: space-between;
        
        /* 4. Positioning: The Floating Sheet */
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1100; /* Highest layer */
        
        /* 5. The "Quiet Luxury" Border & Shadow */
        /* We use a 0.5px border to mimic high-end hardware manufacturing */
        border-bottom: 0.5px solid rgba(0, 0, 0, 0.08); 
        box-shadow: 0 1px 0 rgba(0, 0, 0, 0.02);
        
        /* 6. Smooth Entrance Animation */
        animation: headerSlideDown 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* Animation for the "Lid Opening" feel */
    @keyframes headerSlideDown {
        from { transform: translateY(-100%); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    /* Logo Branding: Tiny-Miny but Effective */
    .admin-logo {
        font-size: 19px;
        font-weight: 700;
        letter-spacing: -0.04em; /* Tighter tracking like Apple San Francisco font */
        color: #1d1d1f;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        transition: transform 0.4s var(--ease-apple);
    }

    .admin-logo i {
        color: var(--lux-blue);
        filter: drop-shadow(0 0 8px rgba(0, 113, 227, 0.2));
    }

    /* User Navigation Section */
    .admin-user {
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .admin-user span {
        font-size: 13px;
        font-weight: 600;
        color: #86868b;
        letter-spacing: 0.02em;
        /* Subtle transition for interactive feel */
        transition: color 0.3s ease;
    }


    /* --- Pagination Container --- */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px; /* Precision spacing */
        margin-top: 40px;
        margin-bottom: 20px;
        list-style: none;
        animation: fadeInUp 0.8s var(--ease-apple);
    }

    /* --- Individual Page Items --- */
    .pagination .page-item {
        display: inline-block;
    }

    /* --- The Buttons (Standard State) --- */
    .pagination .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0 14px;
        background: #ffffff;
        color: #86868b; /* Apple's secondary text color */
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        border-radius: 12px; /* Smooth rounded corners */
        border: 0.5px solid var(--lux-border);
        transition: all 0.4s var(--ease-apple);
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }

    /* --- Hover State: The "Lift" --- */
    .pagination .page-link:hover {
        color: var(--lux-blue);
        background: #ffffff;
        transform: translateY(-3px);
        border-color: rgba(0, 113, 227, 0.3);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
    }

    /* --- Active State: The "Polished Blue" --- */
    .pagination .page-item.active .page-link {
        background: var(--lux-blue);
        color: #ffffff;
        border-color: var(--lux-blue);
        box-shadow: 0 8px 15px rgba(0, 113, 227, 0.25);
        transform: translateY(-2px) scale(1.05); /* Slight pop-out effect */
    }

    /* --- Disabled State: The "Recessed" Look --- */
    .pagination .page-item.disabled .page-link {
        background: #fbfbfd;
        color: #d2d2d7;
        border-color: rgba(0, 0, 0, 0.03);
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }

    /* --- Navigation Icons (Arrows) --- */
    .pagination .page-link i {
        font-size: 12px;
        transition: transform 0.3s var(--ease-apple);
    }

    .pagination .page-item:first-child .page-link:hover i {
        transform: translateX(-3px); /* Interactive nudge to the left */
    }

    .pagination .page-item:last-child .page-link:hover i {
        transform: translateX(3px); /* Interactive nudge to the right */
    }

    /* --- Mobile Optimization --- */
    @media (max-width: 600px) {
        .pagination .page-link {
            min-width: 36px;
            height: 36px;
            padding: 0 10px;
            font-size: 13px;
        }
    }


    </style>
    {{-- <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #fafbfc;
            color: #1a1a1a;
            line-height: 1.5;
            min-height: 100vh;
            font-size: 14px;
        }

        /* Header */
        .admin-header {
            background: #ffffff;
            color: #1a1a1a;
            padding: 0 32px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e5e7eb;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .admin-logo {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .admin-user span {
            font-size: 15px;
            font-weight: 500;
            color: #64748b;
        }

        .logout-btn {
            background: #f3f4f6;
            color: #374151;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.2s ease;
        }

        .logout-btn:hover {
            background: #e5e7eb;
        }

        /* Sidebar */
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 64px;
            width: 240px;
            height: calc(100vh - 64px);
            background: #f9fafb;
            border-right: 1px solid #e5e7eb;
            overflow-y: auto;
            z-index: 999;
        }

        .sidebar-nav {
            padding: 16px 0;
        }

        .nav-item {
            margin-bottom: 1px;
            padding: 0 12px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 10px 12px;
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.15s ease;
        }

        .nav-link:hover {
            background: #f3f4f6;
            color: #374151;
        }

        .nav-link.active {
            background: #3b82f6;
            color: white;
        }

        .nav-link i {
            width: 18px;
            margin-right: 10px;
            font-size: 16px;
            text-align: center;
        }

        /* Main Content */
        .admin-main {
            margin-left: 240px;
            margin-top: 64px;
            padding: 24px;
            min-height: calc(100vh - 64px);
        }

        /* Page Header */
        .page-header {
            margin-bottom: 40px;
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 17px;
            font-weight: 400;
            line-height: 1.5;
        }

        /* Cards */
        .card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 24px;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #d7e1f3, #d1d4dc);
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0;
        }

        .card-body {
            padding: 20px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 4px;
        }

        .stat-label {
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            border: 1px solid transparent;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
            gap: 6px;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border-color: #d1d5db;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        /* Tables */
        .table-container {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .table th,
        .table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }

        .table th {
            background: #fafbfc;
            font-weight: 600;
            color: #374151;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            font-size: 14px;
            color: #1a1a1a;
        }

        .table tbody tr:hover {
            background: #f9fafb;
        }

        /* Forms */
        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            background: #ffffff;
            transition: border-color 0.15s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: 500;
            border-radius: 12px;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }

            .admin-main {
                margin-left: 0;
                padding: 20px;
            }

            .admin-header {
                padding: 0 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .page-title {
                font-size: 28px;
            }

            .card-body {
                padding: 20px;
            }

            .card-header {
                padding: 20px;
            }

            .table th,
            .table td {
                padding: 16px 12px;
            }
        }

        /* Alerts */
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

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 4px;
            margin: 24px 0;
        }

        .pagination .page-item {
            list-style: none;
        }

        .pagination .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            min-width: 40px;
            height: 40px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            color: #374151;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            background: #ffffff;
        }

        .pagination .page-link:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #1e293b;
        }

        .pagination .page-item.active .page-link {
            background: #6366f1;
            border-color: #6366f1;
            color: #ffffff;
        }

        .pagination .page-item.disabled .page-link {
            background: #f8fafc;
            border-color: #e2e8f0;
            color: #9ca3af;
            cursor: not-allowed;
        }

        .pagination .page-item.disabled .page-link:hover {
            background: #f8fafc;
            border-color: #e2e8f0;
            color: #9ca3af;
        }
    </style> --}}
    
    @stack('styles')
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="admin-logo">
            <i class="fas fa-clock"></i> Time Tracking Admin
        </div>
        <div class="admin-user">
            <span>Welcome, {{ auth()->user()->username }}</span>
            <a href="{{ route('logout') }}" class="logout-btn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </header>

    <!-- Sidebar -->
    <nav class="admin-sidebar">
        <div class="sidebar-nav">
            <div class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.employees') }}" class="nav-link {{ request()->routeIs('admin.employees*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    Employees
                </a>
            </div>
            {{-- <div class="nav-item">
                <a href="{{ route('admin.departments') }}" class="nav-link {{ request()->routeIs('admin.departments*') ? 'active' : '' }}">
                    <i class="fas fa-building"></i>
                    Departments
                </a>
            </div> --}}
            <div class="nav-item">
                <a href="{{ route('admin.attendance') }}" class="nav-link {{ request()->routeIs('admin.attendance*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check"></i>
                    Attendance
                </a>
            </div>
            {{-- <div class="nav-item">
                <a href="{{ route('admin.time-entries') }}" class="nav-link {{ request()->routeIs('admin.time-entries*') ? 'active' : '' }}">
                    <i class="fas fa-clock"></i>
                    Time Entries
                </a>
            </div> --}}
            <div class="nav-item">
                <a href="{{ route('admin.applications') }}" class="nav-link {{ request()->routeIs('admin.applications*') ? 'active' : '' }}">
                    <i class="fas fa-file-alt"></i>
                    Applications
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.employee-history') }}" class="nav-link {{ request()->routeIs('admin.employee-history*') ? 'active' : '' }}">
                    <i class="fas fa-history"></i>
                    Employee History
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.wfh') }}" class="nav-link {{ request()->routeIs('admin.wfh*') ? 'active' : '' }}">
                    <i class="fas fa-laptop-house"></i>
                    Work From Home
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.reports') }}" class="nav-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i>
                    Reports
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.schedule') }}" class="nav-link {{ request()->routeIs('admin.schedule*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i>
                    Schedule
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
        // CSRF token setup for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>