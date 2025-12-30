@extends('super-admin.layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content')
<div class="page-header">
    <h1 class="page-title">Super Admin Dashboard</h1>
    <p class="page-subtitle">Complete system overview and salary management</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number">{{ $stats['total_employees'] }}</div>
        <div class="stat-label">Total Employees</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $stats['active_employees'] }}</div>
        <div class="stat-label">Active Employees</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $stats['total_departments'] }}</div>
        <div class="stat-label">Departments</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $stats['pending_applications'] }}</div>
        <div class="stat-label">Pending Applications</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quick Actions</h3>
    </div>
    <div class="card-body">
        <div style="display: flex; gap: 16px; flex-wrap: wrap;">
            <a href="{{ route('super-admin.salaries.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Salary
            </a>
            <a href="{{ route('super-admin.salaries') }}" class="btn btn-secondary">
                <i class="fas fa-money-bill-wave"></i> Manage Salaries
            </a>
            <a href="{{ route('super-admin.employees') }}" class="btn btn-secondary">
                <i class="fas fa-users"></i> View Employees
            </a>
            <a href="{{ route('super-admin.reports') }}" class="btn btn-secondary">
                <i class="fas fa-chart-bar"></i> Generate Reports
            </a>
        </div>
    </div>
</div>
@endsection