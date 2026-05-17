@extends('layouts.user')

@section('page-title', 'Welcome Back!')
@section('page-subtitle', 'Track your time and manage your workday efficiently')

@push('page-styles')
<style>
    /* ========== ORIGINAL STYLES (KEPT INTACT) ========== */
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
        --radius-5px: 5px;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --transition: all 0.2s ease;
        --bottom-nav-height: 70px;
 
        
        /* Activity timeline additional variables */
        --bg-card: #ffffff;
        --bg-hover: #fefefe;
        --border-light: #f0f2f5;
        --text-primary: #1e293b;
        --text-secondary: #5b6e8c;
        --text-muted: #8a99b4;
        --accent-soft: #f5f3ff;
        --shadow-soft: 0 2px 8px rgba(0, 0, 0, 0.03), 0 1px 2px rgba(0, 0, 0, 0.02);
        --shadow-soft-hover: 0 6px 16px rgba(0, 0, 0, 0.04), 0 2px 4px rgba(0, 0, 0, 0.02);
        --punch-in-light: #eef2ff;
        --punch-in-icon: #4f46e5;
        --punch-out-light: #f5f3ff;
        --punch-out-icon: #7c3aed;
        --lunch-light: #fffbeb;
        --lunch-icon: #f59e0b;
    }

    /* Typography & spacing */
    h1, h2, h3 {
        font-weight: 600;
        letter-spacing: -0.01em;
    }

    /* ========== NEW FLUTTER-LIKE ANDROID APP STRUCTURE ========== */
    
    /* Main App Container - Mobile-first */
    .app-container {
        max-width: 600px;
        margin: 0 auto;
        background: #f5f7fb;
        min-height: 100vh;
        position: relative;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
        padding-bottom: var(--bottom-nav-height);
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
        overflow-y: auto;
        flex: 1;
    }

    /* Screen transitions */
    .screen {
        display: none;
        animation: fadeSlideUp 0.25s ease-out;
    }

    .screen.active-screen {
        display: block;
    }

    @keyframes fadeSlideUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
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

 
    /* Notification Dropdown */
    .notification-dropdown-mobile {
        position: fixed;
        top: 60px;
        right: 16px;
        background: white;
        border-radius: 5px;
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

    /* User Profile Card */
    .profile-card {
        background: linear-gradient(135deg, #ffffff, #fefeff);
        border-radius: 24px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02), 0 4px 12px rgba(0,0,0,0.03);
        border: 1px solid rgba(79,70,229,0.08);
        text-align: center;
    }

    .profile-avatar-large {
        width: 72px;
        height: 72px;
        margin: 0 auto 12px;
        border-radius: 36px;
        background: linear-gradient(135deg, var(--primary), #8b5cf6);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
    }

    .profile-name {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
    }

    .profile-id {
        font-size: 0.8rem;
        color: var(--gray-500);
        margin-top: 4px;
    }

    .online-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #e6f7ec;
        padding: 6px 14px;
        border-radius: 40px;
        font-size: 0.7rem;
        font-weight: 600;
        color: #10b981;
        margin-top: 12px;
    }

    /* Preserve all original dashboard styles */
    .page-header {
        margin-bottom: 1rem;
    }

    .page-title {
        font-size: 1.5rem;
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

    .alert-halfday {
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        border-left: 4px solid #3b82f6;
        border-radius: var(--radius);
        padding: 0.75rem 1rem;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.9rem;
        color: #1e40af;
        font-weight: 500;
    }

    .clock-widget {
        background: white;
        border-radius: var(--radius-5px);
        box-shadow: var(--shadow-md);
        padding: 1rem;
        margin-bottom: 0.75rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        border: 1px solid var(--gray-200);
    }

    .clock-display {
        font-size: 2rem;
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
    }

    .time-format-toggle i {
        font-size: 0.9rem;
    }

    .time-format-toggle:hover {
        background: var(--gray-200);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }

    .summary-card {
        background: white;
        border-radius: var(--radius-5px);
        padding: 0.6rem 0.8rem;
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

    .status-card {
        grid-column: span 2;
        margin-top: 0;
        margin-bottom: 0.75rem;
    }


    .card-icon {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #eef2ff, #e0e7ff);
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        font-size: 1.2rem;
        margin-bottom: 0.5rem;
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
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--gray-900);
        margin-bottom: 0.2rem;
    }

    .card-trend {
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        color: var(--gray-500);
    }

    .time-tracking-card {
        background: white;
        border-radius: var(--radius-5px);
        box-shadow: var(--shadow-md);
        margin-bottom: 0.75rem;
        border: 1px solid var(--gray-200);
        overflow: hidden;
    }

    .card-header {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        background: linear-gradient(135deg, var(--gray-50), #f8fafc);
    }

    .card-title {
        font-size: 1.125rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: var(--gray-900);
    }

    .card-title i {
        color: var(--primary);
        font-size: 1.1rem;
    }

    .tracking-controls {
        position: relative;
        padding: 0.75rem 1rem;
    }

    .control-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .control-group:last-child {
        margin-bottom: 0;
    }

    .tracking-btn {
        border: none;
        border-radius: var(--radius-5px);
        padding: 0.75rem 0.5rem;
        font-weight: 600;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        min-height: 80px;
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
        font-size: 1.4rem;
        opacity: 0.9;
    }

    .btn-content span {
        font-size: 0.85rem;
        font-weight: 600;
        text-align: center;
    }

    .btn-description {
        font-size: 0.65rem;
        opacity: 0.8;
        font-weight: 400;
        text-align: center;
        z-index: 1;
        position: relative;
    }

    #punchInBtn { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
    #punchInBtn:hover:not(:disabled) { background: linear-gradient(135deg, #1d4ed8, #1e40af); transform: translateY(-3px); box-shadow: 0 12px 24px -6px rgba(37, 99, 235, 0.4); }
    #lunchStartBtn { background: linear-gradient(135deg, #ea580c, #c2410c); }
    #lunchStartBtn:hover:not(:disabled) { background: linear-gradient(135deg, #c2410c, #9a3412); transform: translateY(-3px); box-shadow: 0 12px 24px -6px rgba(234, 88, 12, 0.4); }
    #lunchEndBtn { background: linear-gradient(135deg, #0ba699, #0e9489); }
    #lunchEndBtn:hover:not(:disabled) { background: linear-gradient(135deg, #0f766e, #115e59); transform: translateY(-3px); box-shadow: 0 12px 24px -6px rgba(13, 148, 136, 0.4); }
    #punchOutBtn { background: linear-gradient(135deg, #7c3aed, #6d28d9); }
    #punchOutBtn:hover:not(:disabled) { background: linear-gradient(135deg, #6d28d9, #5b21b6); transform: translateY(-3px); box-shadow: 0 12px 24px -6px rgba(124, 58, 237, 0.4); }

    .tracking-btn:disabled, .tracking-btn.disabled {
        opacity: 0.22;
        cursor: not-allowed;
        filter: grayscale(0.2);
        transform: none !important;
        box-shadow: none !important;
    }

    .tracking-btn:disabled::before, .tracking-btn.disabled::before {
        display: none;
    }

    /* Status text styles */
    .status-text-present { -webkit-text-fill-color: #1fff009e !important; font-size: 32px !important; }
    .status-text-late { -webkit-text-fill-color: orange !important; font-size: 32px !important; }
    .status-text-absent { -webkit-text-fill-color: red !important; font-size: 32px !important; }
    .status-text-icon-present { -webkit-text-fill-color: #1fff009e !important; }
    .status-text-icon-late { -webkit-text-fill-color: orange !important;}
    .status-text-icon-absent { -webkit-text-fill-color: red !important;}
    .status-Indicator-present { -webkit-text-fill-color: #198754 !important; font-size: 12px !important; font-weight: 800;}
    .status-Indicator-late { -webkit-text-fill-color: #6e3a02 !important; font-size: 12px !important; font-weight: 800;}
    .status-Indicator-absent { -webkit-text-fill-color: #ba2218 !important; font-size: 12px !important; font-weight: 800;}

    /* Activity timeline */
    .activity-card {
        background: white;
        border-radius: var(--radius-5px);
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

    /* Section Title */
    .section-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 1.25rem;
        padding-bottom: 0.7rem;
        border-bottom: 1.5px solid var(--gray-200);
        display: flex;
        align-items: center;
        gap: 0.6rem;
        letter-spacing: -0.2px;
    }

    .section-title i {
        font-size: 1rem;
        color: var(--primary);
        background: var(--accent-soft);
        padding: 0.35rem;
        border-radius: 0.5rem;
    }

    /* Activity List */
    .activity-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.8rem 1rem;
        background: var(--bg-card);
        border-radius: var(--radius);
        border: 1px solid var(--border-light);
        transition: var(--transition);
        position: relative;
        cursor: default;
        box-shadow: 3px 3px 5px -2px rgb(157, 172, 197);
        margin-bottom: 0.5rem;
    }

    .activity-item:hover {
        background: var(--bg-hover);
        border-color: var(--gray-200);
        transform: translateX(3px);
        box-shadow: var(--shadow-soft-hover);
    }

    .activity-icon {
        width: 42px;
        height: 42px;
        border-radius: var(--radius);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: var(--transition);
    }

    .activity-icon i {
        font-size: 1.2rem;
        transition: var(--transition);
    }

    .activity-item[data-type="punch_in"] .activity-icon { background: var(--punch-in-light); }
    .activity-item[data-type="punch_in"] .activity-icon i { color: var(--punch-in-icon); }
    .activity-item[data-type="punch_out"] .activity-icon { background: var(--punch-out-light); }
    .activity-item[data-type="punch_out"] .activity-icon i { color: var(--punch-out-icon); }
    .activity-item[data-type="lunch_start"] .activity-icon,
    .activity-item[data-type="lunch_end"] .activity-icon { background: var(--lunch-light); }
    .activity-item[data-type="lunch_start"] .activity-icon i,
    .activity-item[data-type="lunch_end"] .activity-icon i { color: var(--lunch-icon); }

    .activity-item:hover .activity-icon i { transform: scale(1.05); }

    .activity-details {
        flex: 1;
        min-width: 0;
    }

    .activity-name {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--text-primary);
        text-transform: capitalize;
        letter-spacing: -0.2px;
        margin-bottom: 0.2rem;
        line-height: 1.4;
    }

    .activity-time {
        font-size: 0.7rem;
        color: var(--text-muted);
        font-weight: 500;
        letter-spacing: 0.2px;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    /* Loading Modal */
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

    /* Responsive Adjustments */
    @media (max-width: 640px) {
        .time-format-toggle {         
            position: relative;
            display: flex;
            align-self: end; 
        }
        .clock-widget { padding: 1rem; flex-direction: column; align-items: flex-start; gap: 0.1rem; }
        .clock-display { font-size: 2rem; }
        .clock-date { font-size: 0.8rem; }
        .time-tracking-card .card-header { padding: 1.25rem 1.5rem; }
        .time-tracking-card .card-title { font-size: 1.125rem; }
        
        .control-group { gap: 1rem; }
        .tracking-btn { padding: 1.25rem 1rem; min-height: 100px; }
        .btn-content i { font-size: 1.5rem; }
        .btn-content span { font-size: 0.9rem; }
        .btn-description { font-size: 0.7rem; }
        .summary-grid { grid-template-columns: 1fr 1fr !important; gap: 0.5rem; }
        .summary-card { padding: 0.6rem 0.75rem; }
        .card-icon { width: 32px; height: 32px; font-size: 1rem; margin-bottom: 0.4rem; }
        .summary-card h3 { font-size: 0.8rem; margin-bottom: 0.3rem; }
        .summary-card p { font-size: 1.1rem; }
        .card-trend { font-size: 0.65rem; }
        .activity-content { padding: 1rem; }
    }

    @media (max-width: 480px) {
        .control-group { width: 100%; grid-template-columns: repeat(2, 1fr); gap: 0.6rem; }
        .tracking-btn { min-height: 80px; padding: 0.6rem; }
        .time-tracking-card .card-header { padding: 1rem 0.75rem; }
        .summary-grid { gap: 0.4rem; }
    }
</style>
@endpush

@section('page-content')
<!-- User Profile Card -->
<!-- <div class="profile-card">
    <div class="profile-avatar-large">
        <i class="fas fa-user-circle"></i>
    </div>
    <div class="profile-name">{{ Auth::user()->full_name ?? Auth::user()->name ?? 'Employee' }}</div>
    <div class="profile-id">ID: {{ Auth::user()->emp_id ?? Auth::user()->id ?? 'EMP001' }}</div>
    <div class="online-badge">
        <span class="status-dot" style="background:#10b981; width:8px;height:8px;border-radius:50%;display:inline-block;"></span>
        <span>Active</span>
    </div>
</div> -->

<!-- Dashboard Content -->
<div id="dashboardContent">
            @if($isHalfDay ?? false)
            <div class="alert-halfday">
                <i class="fas fa-info-circle"></i>
                <strong>Today is your Half Day</strong> - You have an approved half-day leave for today
            </div>
            @endif

            <div class="clock-widget">
                <div class="clock-display" id="clockTime">--:--</div>
                <div class="clock-date" id="clockDate">Loading...</div>
                <button class="time-format-toggle" id="timeFormatToggle"><i class="fas fa-clock"></i> 12H</button>
            </div>

            <div class="time-tracking-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-fingerprint"></i>
                        Time Tracking
                    </h2>
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
                    <div class="card-icon"><i class="fas fa-clock"></i></div>
                    <h3>Work Time</h3>
                    <p id="workTime">Loading...</p>
                    <div class="card-trend"><i class="fas fa-arrow-up"></i><span>Today's progress</span></div>
                </div>
                <div class="summary-card lunch-card">
                    <div class="card-icon"><i class="fas fa-utensils"></i></div>
                    <h3>Lunch Time</h3>
                    <p id="lunchTime">Loading...</p>
                    <div class="card-trend"><i class="fas fa-minus"></i><span>Break duration</span></div>
                </div>
                <div class="summary-card status-card">
                    <div class="card-icon" id="statusCardIcon"><i class="fas fa-user-check"></i></div>
                    <h3>Status</h3>
                    <p id="status">Loading...</p>
                    <div class="card-trend">
                        <i class="fas fa-circle" id="statusIndicatorIcon"></i>
                        <span id="statusIndicator">Checking status...</span>
                    </div>
                </div>
            </div>


        </div>
</div>

<!-- Loading Modal -->
<div id="loadingModal" class="loading-overlay" style="display: none;">
    <div class="loading-modal">
        <div class="spinner"></div>
        <div style="font-weight: 600; margin-top: 0.5rem;" id="loadingText">Processing...</div>
        <div style="font-size: 0.8rem; color: #64748b;">Please wait...</div>
    </div>
</div>
@endsection

@push('page-scripts')
<script>
$(document).ready(function() {
    // ========== FLUTTER-LIKE NAVIGATION ==========
    let loadedStates = {
        applications: false,
        schedule: false,
        profile: false,
        payslips: false
    };

    function showScreen(screenId) {
        $('.screen').removeClass('active-screen');
        $('#screen' + screenId.charAt(0).toUpperCase() + screenId.slice(1)).addClass('active-screen');
        $('.nav-item-bottom').removeClass('active');
        $(`.nav-item-bottom[data-screen="${screenId}"]`).addClass('active');
        
        if (screenId === 'applications' && !loadedStates.applications) {
            loadRouteContent('{{ route("applications.index") }}', '#applicationsContent', () => { loadedStates.applications = true; });
        } else if (screenId === 'schedule' && !loadedStates.schedule) {
            loadRouteContent('{{ route("schedule") }}', '#scheduleContent', () => { loadedStates.schedule = true; });
        } else if (screenId === 'profile' && !loadedStates.profile) {
            loadRouteContent('{{ route("profile") }}', '#profileContent', () => { loadedStates.profile = true; });
        } else if (screenId === 'payslips' && !loadedStates.payslips) {
            loadRouteContent('{{ route("payslips.index") }}', '#payslipsContent', () => { loadedStates.payslips = true; });
        }
    }

    function loadRouteContent(url, containerSelector, callback) {
        $.ajax({
            url: url,
            type: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(html) {
                let $html = $(html);
                let content = $html.find('.main-content').html() || 
                             $html.find('#page-content').html() || 
                             $html.find('.page-content').html() ||
                             $html.find('main').html() ||
                             $html.filter('div').html();
                if (!content || content.trim() === '') content = html;
                $(containerSelector).html(content);
                if (callback) callback();
            },
            error: function() {
                $(containerSelector).html('<div style="padding:40px;text-align:center;color:#ef4444;"><i class="fas fa-exclamation-circle"></i><p style="margin-top:12px;">Error loading content. Please try again.</p></div>');
            }
        });
    }

    $('.nav-item-bottom').on('click', function() {
        showScreen($(this).data('screen'));
    });

    // ========== CLOCK FUNCTIONALITY ==========
    let is24Hour = true;
    
    function updateClock() {
        const now = new Date();
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
    
    $('#timeFormatToggle').click(function() {
        is24Hour = !is24Hour;
        $(this).html(is24Hour ? '<i class="fas fa-clock"></i> 12H' : '<i class="fas fa-clock"></i> 24H');
        updateClock();
    });
    
    updateClock();
    setInterval(updateClock, 1000);
    
    // ========== TIME TRACKING FUNCTIONS ==========
    function loadTimeData() {
        $.ajax({
            url: '{{ route("api.time.worked") }}',
            method: 'POST',
            global: false,
            data: { click: 'timeWorked', _token: '{{ csrf_token() }}' },
            success: function(data) {
                if (data) {
                    let workTimeValue = data.network || data.total_hours || '0H 0M';
                    $('#workTime').text(workTimeValue).css('color', workTimeValue === '0H 0M' ? '#666' : '#333');
                    
                    let lunchTimeValue = data.totalLunchByemp || '0M';
                    $('#lunchTime').text(lunchTimeValue).css('color', lunchTimeValue === '0M' ? '#666' : '#333');
                    
                    let statusText = data.late || 'Not Punched In';
                    $('#status').text(statusText);
                    

                    var statusClass = 'status-text-' + statusText.toLowerCase();
                    var indicatorClass = 'status-Indicator-' + statusText.toLowerCase();
                    var iconClass = 'status-text-icon-' + statusText.toLowerCase();

                    // Remove old status-text-* classes only
                    $('#status').removeClass(function(index, className) {
                        return (className.match(/(^|\s)status-text-\S+/g) || []).join(' ');
                    }).addClass(statusClass);

                    $('#statusIndicator').removeClass(function(index, className) {
                        return (className.match(/(^|\s)status-Indicator-\S+/g) || []).join(' ');
                    }).addClass(indicatorClass);

                    $('#statusIndicatorIcon').removeClass(function(index, className) {
                        return (className.match(/(^|\s)status-Indicator-\S+/g) || []).join(' ');
                    }).addClass(indicatorClass);

                    $('#statusCardIcon').removeClass(function(index, className) {
                        return (className.match(/(^|\s)status-text-icon-\S+/g) || []).join(' ');
                    }).addClass(iconClass);



                    if (statusText.toLowerCase().includes('present')) {
                        $('#statusIndicator').text('Working');
                    } else if (statusText.toLowerCase().includes('late')) {
                        $('#statusIndicator').text('Late arrival');
                    } else if (statusText.toLowerCase().includes('absent')) {
                        $('#statusIndicator').text('Not present');
                    } else {
                        $('#statusIndicator').text('Ready to start');
                    }
                }
            },
            error: function() {
                $('#workTime').text('0H 0M');
                $('#lunchTime').text('0H 0M');
                $('#status').text('Data unavailable');
            }
        });
    }
    

    
    function updateButtonStates() {
        $.ajax({
            url: '{{ route("api.time.check-punch") }}',
            method: 'POST',
            global: false,
            data: { click: 'checkfirstpunchin', _token: '{{ csrf_token() }}' },
            success: function(response) {
                $('.tracking-btn').prop('disabled', false).removeClass('disabled');
                if (response == '5') {
                    $('#punchOutBtn, #lunchStartBtn, #lunchEndBtn').prop('disabled', true).addClass('disabled');
                } else if (response == '1') {
                    $('#punchInBtn').prop('disabled', true).addClass('disabled');
                    $('#lunchEndBtn').prop('disabled', true).addClass('disabled');
                } else if (response == '2') {
                    $('#punchInBtn, #punchOutBtn, #lunchStartBtn').prop('disabled', true).addClass('disabled');
                } else if (response == '3') {
                    $('#punchInBtn, #lunchEndBtn').prop('disabled', true).addClass('disabled');
                } else if (response == '4') {
                    $('#punchOutBtn, #lunchStartBtn, #lunchEndBtn').prop('disabled', true).addClass('disabled');
                } else if (response == '6') {
                    $('.tracking-btn').prop('disabled', true).addClass('disabled');
                }

                loadTimeData();
            }
        });
    }
    
    let isProcessing = false;
    
    function showLoadingModal(action) {
        const actionText = { 'punch_in': 'Punching In', 'punch_out': 'Punching Out', 'lunch_start': 'Starting Lunch', 'lunch_end': 'Ending Lunch' };
        $('#loadingText').text(actionText[action] || 'Processing');
        $('#loadingModal').show();
    }
    
    function hideLoadingModal() { $('#loadingModal').hide(); }
    
    function handleTimeAction(action) {
        if (isProcessing) return;
        isProcessing = true;
        showLoadingModal(action);
        const clickedBtn = $('#' + action.replace('_', '') + 'Btn');
        clickedBtn.prop('disabled', true).addClass('processing');
        
        const route = action === 'punch_in' ? '{{ route("punch.in") }}' : 
                     action === 'punch_out' ? '{{ route("punch.out") }}' : 
                     action === 'lunch_start' ? '{{ route("lunch.start") }}' : 
                     '{{ route("lunch.end") }}';
        
        $.ajax({
            url: route,
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            timeout: 30000,
            success: function(response) {
                hideLoadingModal();
                if (response.require_image) {
                    // Simplified image capture handling
                    setTimeout(() => {
                        Swal.fire({ title: 'Image Capture', text: 'Please capture your image', icon: 'info' });
                    }, 100);
                } else if (response.success) {
                    loadTimeData();
                    updateButtonStates();
                    setTimeout(() => {
                        Swal.fire({ icon: 'success', title: 'Success!', text: response.message, timer: 2000, showConfirmButton: false });
                    }, 100);
                }
            },
            error: function(xhr) {
                hideLoadingModal();
                const response = xhr.responseJSON;
                Swal.fire({ icon: 'error', title: 'Error!', text: response?.error || 'Unable to process request.', zIndex: 9999999 });
            },
            complete: function() {
                isProcessing = false;
                clickedBtn.removeClass('processing');
                updateButtonStates();
            }
        });
    }
    
    $('#punchInBtn').click(() => handleTimeAction('punch_in'));
    $('#punchOutBtn').click(() => handleTimeAction('punch_out'));
    $('#lunchStartBtn').click(() => handleTimeAction('lunch_start'));
    $('#lunchEndBtn').click(() => handleTimeAction('lunch_end'));
    
    // ========== INITIALIZATION ==========
    loadTimeData();
    updateButtonStates();
    
    setInterval(function() { loadTimeData(); updateButtonStates(); }, 300000);
});
</script>
@endpush