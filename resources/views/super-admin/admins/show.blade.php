@extends('super-admin.layouts.app')

@section('title', 'Admin Details')

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Administrator Details</h1>
            <p class="page-subtitle">{{ $admin->username }} ({{ $admin->emp_id }})</p>
        </div>
        <div>
            <a href="{{ route('super-admin.admins.edit', $admin) }}" class="btn btn-primary" >
                <i class="fas fa-edit"></i> Edit Admin
            </a>
            <a href="{{ route('super-admin.admins') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
    <!-- Basic Information -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Basic Information</h3>
        </div>
        <div class="card-body">
            <div class="info-row">
                <label>Employee ID:</label>
                <span class="badge p-2 text-bg-info">{{ $admin->emp_id }}</span>
            </div>
            <div class="info-row">
                <label>Full Name:</label>
                <span>{{ $admin->username }}</span>
            </div>
            <div class="info-row">
                <label>Email Address:</label>
                <span>{{ $admin->email }}</span>
            </div>
            <div class="info-row">
                <label>Role:</label>
                <span class="badge p-2 text-bg-primary">{{ ucfirst($admin->role) }}</span>
            </div>
            <div class="info-row">
                <label>Status:</label>
                <span class="badge p-2 {{ $admin->status == 'active' ? 'text-bg-success' : 'text-bg-danger' }}">
                    {{ ucfirst($admin->status) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Department & Location -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Department & Location</h3>
        </div>
        <div class="card-body">
            <div class="info-row">
                <label>Department:</label>
                <span>{{ $admin->department->name ?? 'Not Assigned' }}</span>
            </div>
            <div class="info-row">
                <label>Designation:</label>
                <span>{{ $admin->position ?? 'Not Specified' }}</span>
            </div>
            <div class="info-row">
                <label>Branch:</label>
                <span>{{ $admin->region->name ?? 'Not Assigned' }}</span>
            </div>
            @if($admin->region)
            <div class="info-row">
                <label>PIN Code:</label>
                <span>{{ $admin->region->pin_code }}</span>
            </div>
            <div class="info-row" title="{{ $admin->address }}">
                <label style="margin-right: 9px;">Address:</label>
                <span >{{$admin->address }}</span>
            </div>
            @endif
            {{-- <div class="info-row">
                <label>Reference Admin:</label>
                <span>{{ $referenceAdmin ? $referenceAdmin->username . ' (' . $referenceAdmin->emp_id . ')' : 'None' }}</span>
            </div> --}}
        </div>
    </div>
</div>

@if($admin->salary)
<div style="margin-bottom: 30px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Salary Information</h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                <div class="salary-item">
                    <label>Basic Salary</label>
                    <span class="amount">₹{{ number_format($admin->salary->basic_salary, 2) }}</span>
                </div>
                <div class="salary-item">
                    <label>HRA</label>
                    <span class="amount">₹{{ number_format($admin->salary->hra, 2) }}</span>
                </div>
                <div class="salary-item">
                    <label>Conveyance</label>
                    <span class="amount">₹{{ number_format($admin->salary->conveyance_allowance, 2) }}</span>
                </div>
                <div class="salary-item">
                    <label>PF Deduction</label>
                    <span class="amount deduction">₹{{ number_format($admin->salary->pf, 2) }}</span>
                </div>
                <div class="salary-item">
                    <label>PT Deduction</label>
                    <span class="amount deduction">₹{{ number_format($admin->salary->pt, 2) }}</span>
                </div>
                <div class="salary-item">
                    <label>Gross Salary</label>
                    <span class="amount gross">₹{{ number_format($admin->salary->basic_salary + $admin->salary->hra + $admin->salary->conveyance_allowance, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Assigned Employees -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title">Assigned Employees (<span id="employee-count">{{ $assignedEmployees->total() }}</span>)</h3>
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <input type="text" id="employee-search" placeholder="Search by ID or name..." 
                       style="padding: 6px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px; width: 200px;" 
                       value="{{ request('search') }}">
                <button onclick="clearSearch()" style="padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; background: #f8f9fa; cursor: pointer; font-size: 12px;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <label style="font-size: 12px; color: #666; margin: 0;">Per Page:</label>
                <select id="per-page-select" style="padding: 4px 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div id="employee-list">
            @include('super-admin.admins.partials.employee-list')
        </div>
    </div>
</div>

<script>
let searchTimeout;
const adminId = {{ $admin->id }};

document.getElementById('employee-search').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadEmployees();
    }, 300);
});

document.getElementById('per-page-select').addEventListener('change', function() {
    loadEmployees();
});

function loadEmployees(page = 1) {
    const search = document.getElementById('employee-search').value;
    const perPage = document.getElementById('per-page-select').value;
    
    fetch(`{{ route('super-admin.admins.show', $admin->id) }}?search=${search}&per_page=${perPage}&page=${page}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('employee-list').innerHTML = html;
        updateEmployeeCount();
    });
}

function clearSearch() {
    document.getElementById('employee-search').value = '';
    loadEmployees();
}

function updateEmployeeCount() {
    const cards = document.querySelectorAll('.employee-card').length;
    const emptyState = document.querySelector('.empty-state');
    if (emptyState) {
        document.getElementById('employee-count').textContent = '0';
    }
}

// Handle pagination clicks
document.addEventListener('click', function(e) {
    if (e.target.closest('.pagination a')) {
        e.preventDefault();
        const url = new URL(e.target.closest('.pagination a').href);
        const page = url.searchParams.get('page');
        loadEmployees(page);
    }
});
</script>

<style>
.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row label {
    font-weight: 500;
    color: #333;
    font-size: 14px;
}

.info-row span {
    color: #666;
    font-size: 14px;
}

.badge p-2 {
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.text-bg-success {
    background: #d4edda;
    color: #155724;
}

.text-bg-danger {
    background: #f8d7da;
    color: #721c24;
}

.text-bg-primary {
    background: #d1ecf1;
    color: #0c5460;
}

.text-bg-info {
    background: #d1ecf1;
    color: #0c5460;
}

.salary-item {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.salary-item label {
    display: block;
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 5px;
    font-weight: 500;
}

.salary-item .amount {
    font-size: 16px;
    font-weight: 600;
    color: #28a745;
}

.salary-item .amount.deduction {
    color: #dc3545;
}

.salary-item .amount.gross {
    color: #007bff;
    font-size: 18px;
}

.employee-card {
    display: flex;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.employee-card:hover {
    background: #e9ecef;
    transform: translateY(-1px);
}

.employee-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #ff6b35, #ff9900);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
    font-weight: 600;
    margin-right: 15px;
    flex-shrink: 0;
}

.employee-info {
    flex: 1;
}

.employee-name {
    font-weight: 600;
    font-size: 14px;
    color: #333;
    margin-bottom: 4px;
}

.employee-details {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 2px;
}

.employee-status {
    margin-top: 5px;
    font-size: 10px;
}

.empty-state {
    text-align: center;
    color: #6c757d;
    padding: 60px 20px;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.3;
}

.empty-state p {
    font-size: 16px;
    margin: 0;
}
</style>
@endsection