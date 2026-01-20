@extends('admin.layouts.app')

@section('title', 'Employee Profile')

@section('content')
<div class="container-fluid p-4">
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="profile-avatar">
                {{ substr($employee->username ?? 'U', 0, 1) }}
            </div>
            <div>
                <h1 class="page-title mb-0">{{ $employee->full_name }}</h1>
                <div class="d-flex align-items-center gap-2 text-muted mt-1">
                    <span class="material-id">ID: {{ $employee->emp_id }}</span>
                    <span class="dot-separator">•</span>
                    <span>{{ $employee->email }}</span>
                </div>
            </div>
        </div>
        

        <div class="d-flex gap-2">
            <a href="{{ route('admin.employees.history', $employee) }}" class="btn btn-outline-google">
                <i class="fas fa-history me-2"></i> History
            </a>
            <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-primary-google">
                <i class="fas fa-pen me-2"></i> Edit Profile
            </a>

            <a href="{{ route('admin.employees') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            
        </div>
        
    </div>
    {{-- <div class="mt-4">
        <a href="{{ route('admin.employees') }}" class="btn-back">
            <i class="fas fa-arrow-left me-2"></i> Back to Directory
        </a>
    </div> --}}

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="g-card h-100">
                <div class="g-card-header">
                    <h5 class="g-card-title"><i class="fas fa-user-circle me-2"></i> Personal Information</h5>
                </div>
                <div class="g-card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Full Name</label>
                            <div class="value">{{ $employee->full_name ?: '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>Username</label>
                            <div class="value">{{ $employee->username ?: '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>Contact\Phone</label>
                            <div class="value">{{ $employee->phone ?: '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>Email</label>
                            <div class="value">{{ $employee->email ?: '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>Date of Birth</label>
                            <div class="value">{{ $employee->dob ? $employee->dob->format('M d, Y') : '-' }}</div>
                        </div>
                        <div class="info-item full-width">
                            <label>Residential Address</label>
                            <div class="value">{{ $employee->address ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="g-card h-100">
                <div class="g-card-header">
                    <h5 class="g-card-title"><i class="fas fa-briefcase me-2"></i> Work Details</h5>
                </div>
                <div class="g-card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Department</label>
                            <div class="value">{{ $employee->department->name ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>Role / Position</label>
                            <div class="value d-flex align-items-center gap-2">
                                {{ $employee->position ?: ucfirst($employee->role) }}
                            </div>
                        </div>
                        <div class="info-item">
                            <label>Status</label>
                            <div class="value">
                                @if($employee->status === 'active')
                                    <span class="status-pill status-active"><i class="fas fa-check-circle"></i> Active</span>
                                @else
                                    <span class="status-pill status-inactive"><i class="fas fa-ban"></i> Inactive</span>
                                @endif
                            </div>
                        </div>
                        <div class="info-item">
                            <label>Employee Grade</label>
                            <div class="value">
                                @if($employee->senior_junior === 'senior')
                                    <span class="status-pill status-active"><i class="fa-solid fa-user-tie"></i> Senior</span>
                                @else
                                    <span class="status-pill status-junior  ">
                                        <i class="fa-solid fa-code-fork"></i> Junior
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="info-item">
                            <label>City Category</label>
                            <div class="value">
                                <span class="region-chip">{{ $employee->metro_city == 1 ? 'Metro City' : 'Non-Metro City' }}</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <label>Branch</label>
                            <div class="value">
                                <span class="region-chip">{{ $employee->region->name ?? 'Global' }}</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <label>Joining Date</label>
                            <div class="value">{{ $employee->hire_date ? $employee->hire_date->format('M d, Y') : '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>Reporting Manager</label>
                            <div class="value">{{ $employee->referrance ?: 'System Admin' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="g-card">
                <div class="g-card-header d-flex justify-content-between align-items-center">
                    <h5 class="g-card-title"><i class="fas fa-file-invoice-dollar me-2"></i> Compensation</h5>
                    @if($employee->salary)
                        <a href="{{ route('admin.salaries.edit', $employee->salary) }}" class="btn-link-google">Edit Details</a>
                    @else
                        <a href="{{ route('admin.salaries.create') }}?emp_id={{ $employee->emp_id }}" class="btn-link-google">Setup Salary</a>
                    @endif
                </div>
                <div class="g-card-body">
                    @if($employee->salary)
                        <div class="salary-wrapper">
                            <div class="salary-section">
                                <h6 class="section-label text-success">Earnings</h6>
                                <div class="salary-row">
                                    <span>Basic Salary</span>
                                    <span class="amount">₹{{ number_format($employee->salary->basic_salary, 2) }}</span>
                                </div>
                                <div class="salary-row">
                                    <span>HRA</span>
                                    <span class="amount">₹{{ number_format($employee->salary->hra, 2) }}</span>
                                </div>
                                <div class="salary-row">
                                    <span>Transport (TA)</span>
                                    <span class="amount">₹{{ number_format($employee->salary->ta, 2) }}</span>
                                </div>
                                <div class="salary-row">
                                    <span>Conveyance</span>
                                    <span class="amount">₹{{ number_format($employee->salary->conveyance_allowance, 2) }}</span>
                                </div>
                            </div>

                            <div class="salary-section bordered-left">
                                <h6 class="section-label text-danger">Deductions</h6>
                                <div class="salary-row">
                                    <span>Provident Fund (PF)</span>
                                    <span class="amount text-danger">- ₹{{ number_format($employee->salary->pf, 2) }}</span>
                                </div>
                                <div class="salary-row">
                                    <span>Professional Tax (PT)</span>
                                    <span class="amount text-danger">- ₹{{ number_format($employee->salary->pt, 2) }}</span>
                                </div>
                            </div>

                            <div class="salary-total">
                                <label>Gross Monthly Salary</label>
                                <div class="gross-amount">₹{{ number_format($employee->salary->gross_salary, 2) }}</div>
                                <div class="effective-date">Effective: {{ $employee->salary->effective_from->format('F d, Y') }}</div>
                            </div>
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="icon-box"><i class="fas fa-coins"></i></div>
                            <h6>No Salary Configured</h6>
                            <p>Setup a salary structure to manage payroll for this employee.</p>
                            <a href="{{ route('admin.salaries.create') }}?emp_id={{ $employee->emp_id }}" class="btn btn-primary-google mt-2">Configure Now</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    
</div>

<style>
    /* --- Google Material Design 3 Styling --- */
    :root {
        --g-blue: #1a73e8;
        --g-blue-hover: #1557b0;
        --g-text-dark: #202124;
        --g-text-gray: #5f6368;
        --g-border: #dadce0;
        --g-bg-hover: #f1f3f4;
        --g-green: #188038;
        --g-red: #d93025;
    }

    body { background-color: #f8f9fa; }

    /* Header Profile */
    .page-title {
        font-family: 'Google Sans', 'Roboto', sans-serif;
        font-size: 24px;
        color: var(--g-text-dark);
        font-weight: 400;
    }
    .profile-avatar {
        width: 56px; height: 56px;
        background-color: #a142f4; /* Google Purple or dynamic color */
        color: white;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 24px;
        font-weight: 500;
    }
    .dot-separator { margin: 0 6px; font-weight: bold; }

    /* Google Style Buttons */
    .btn-primary-google {
        background-color: var(--g-blue);
        color: white;
        border: none;
        padding: 8px 24px;
        border-radius: 4px;
        font-weight: 500;
        font-size: 14px;
        transition: 0.2s;
    }
    .btn-primary-google:hover { background-color: var(--g-blue-hover); box-shadow: 0 1px 2px rgba(0,0,0,0.2); }

    .btn-outline-google {
        background-color: white;
        color: var(--g-text-gray);
        border: 1px solid var(--g-border);
        padding: 8px 16px;
        border-radius: 4px;
        font-weight: 500;
        font-size: 14px;
    }
    .btn-outline-google:hover { background-color: #f8f9fa; color: var(--g-text-dark); border-color: var(--g-text-dark); }

    .btn-link-google {
        color: var(--g-blue);
        text-decoration: none;
        font-weight: 500;
        font-size: 14px;
    }
    .btn-link-google:hover { text-decoration: underline; }

    .btn-back {
        position:relative;
        right: 10px;
        margin-bottom: 10px;
        color: var(--g-text-gray);
        text-decoration: none;
        font-weight: 500;
        display: inline-flex; align-items: center;
        padding: 8px 16px;
        border-radius: 20px;
    }
    .btn-back:hover { background-color: rgba(0,0,0,0.05); color: var(--g-text-dark); }

    /* Google Cards */
    .g-card {
        background: white;
        border: 1px solid var(--g-border);
        border-radius: 8px;
        overflow: hidden;
    }
    .g-card-header {
        padding: 16px 24px;
        border-bottom: 1px solid transparent; /* Cleaner look */
    }
    .g-card-title {
        font-family: 'Google Sans', sans-serif;
        font-size: 16px;
        color: var(--g-text-dark);
        margin: 0;
        font-weight: 500;
    }
    .g-card-body { padding: 8px 24px 24px 24px; }

    /* Info Grid System */
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    .info-item {
        display: flex;
        flex-direction: column;
    }
    .info-item.full-width { grid-column: span 2; }
    
    .info-item label {
        font-size: 12px;
        font-weight: 500;
        color: var(--g-text-gray);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    .info-item .value {
        font-size: 15px;
        color: var(--g-text-dark);
        padding-bottom: 6px;
        border-bottom: 1px solid transparent;
        transition: 0.2s;
    }
    /* Subtle hover interaction */
    .info-item:hover .value { border-bottom-color: #e0e0e0; }

    /* Chips & Badges */
    .status-pill {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 12px;
        border-radius: 16px;
        font-size: 13px;
        font-weight: 500;
    }
    .status-active { background-color: #e6f4ea; color: #137333; }
    .status-inactive { background-color: #fce8e6; color: #c5221f; }
    .status-junior { background-color: #fff5d3; color: #024703; }

    .region-chip {
        background: #f1f3f4;
        color: var(--g-text-dark);
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 13px;
        border: 1px solid #e0e0e0;
    }

    /* Salary Section Specifics */
    .salary-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 0;
        border: 1px solid var(--g-border);
        border-radius: 8px;
        overflow: hidden;
    }
    .salary-section {
        flex: 2;
        padding: 20px;
        min-width: 250px;
    }
    .bordered-left { border-left: 1px solid var(--g-border); }
    
    .salary-total {
        flex: 1;
        padding: 20px;
        background-color: #f8f9fa;
        border-left: 1px solid var(--g-border);
        display: flex; flex-direction: column; justify-content: center;
        min-width: 200px;
    }

    .section-label {
        font-size: 12px; text-transform: uppercase; font-weight: 700; margin-bottom: 12px; letter-spacing: 0.5px;
    }
    .salary-row {
        display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px;
    }
    .amount { font-family: 'Roboto Mono', monospace; font-weight: 500; }

    .salary-total label { font-size: 12px; color: var(--g-text-gray); margin-bottom: 4px; }
    .gross-amount { font-size: 28px; color: var(--g-green); font-weight: 400; font-family: 'Google Sans', sans-serif; }
    .effective-date { font-size: 12px; color: var(--g-text-gray); margin-top: 4px; }

    /* Empty State */
    .empty-state { text-align: center; padding: 30px; }
    .icon-box { 
        width: 48px; height: 48px; background: #f1f3f4; border-radius: 50%; 
        display: inline-flex; align-items: center; justify-content: center; 
        color: var(--g-text-gray); margin-bottom: 12px; 
    }

    @media (max-width: 991px) {
        .salary-wrapper { flex-direction: column; }
        .bordered-left { border-left: none; border-top: 1px solid var(--g-border); }
        .salary-total { border-left: none; border-top: 1px solid var(--g-border); align-items: flex-start; }
        .info-grid { grid-template-columns: 1fr; }
        .info-item.full-width { grid-column: span 1; }
    }
</style>
@endsection