@extends('super-admin.layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content')
<div class="page-header">
    <h1 class="page-title">Super Admin Dashboard</h1>
    <p class="page-subtitle">Complete system overview and salary management</p>
</div>

<!-- 1. Statistics Cards -->
{{-- <div class="stats-grid">
    <a href="{{ route('super-admin.employees') }}" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer;">
        <div class="stat-number">{{ $stats['total_employees'] }}</div>
        <div class="stat-label" style="margin-top: 5px;">Total Employees</div>
    </a>
    <a href="{{ route('super-admin.employees', ['status' => 'active']) }}" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer;">
        <div class="stat-number">{{ $stats['active_employees'] }}</div>
        <div class="stat-label" style="margin-top: 5px;">Active Employees</div>
    </a>
    <a href="{{ route('super-admin.employees', ['status' => 'inactive']) }}" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer;">
        <div class="stat-number">{{ $stats['inactive_employees'] }}</div>
        <div class="stat-label" style="margin-top: 5px;">Inactive Employees</div>
    </a>
    <a href="{{ route('super-admin.attendance', ['date' => today()->format('Y-m-d')]) }}" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer;">
        <div class="stat-number">{{ $todayAttendance['present'] }}</div>
        <div class="stat-label" style="margin-top: 5px;">Present Today</div>
    </a>
    <a href="{{ route('super-admin.departments') }}" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer;">
        <div class="stat-number">{{ $stats['total_departments'] }}</div>
        <div class="stat-label" style="margin-top: 5px;">Departments</div>
    </a>
    <a href="{{ route('super-admin.applications', ['status' => 'pending']) }}" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer;">
        <div class="stat-number">{{ $stats['pending_applications'] }}</div>
        <div class="stat-label" style="margin-top: 5px;">Pending Applications</div>
    </a>
</div> --}}



<!-- Professional Statistics Cards with Modern Design -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr)">
    <a href="{{ route('super-admin.employees') }}" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: white;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['total_employees'] }}</div>
            <div class="stat-label">Total Employees</div>
            {{-- <div class="stat-trend positive">+12% from last month</div> --}}
        </div>
    </a>
    
    <a href="{{ route('super-admin.employees', ['status' => 'active']) }}" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: white;"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="19" y1="8" x2="19" y2="14"></line><line x1="22" y1="11" x2="16" y2="11"></line></svg>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['active_employees'] }}</div>
            <div class="stat-label">Active Employees</div>
            {{-- <div class="stat-trend positive">+5% from last week</div> --}}
        </div>
    </a>
    
    <a href="{{ route('super-admin.employees', ['status' => 'inactive']) }}" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: white;"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="18" y1="8" x2="23" y2="13"></line><line x1="23" y1="8" x2="18" y2="13"></line></svg>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['inactive_employees'] }}</div>
            <div class="stat-label">Inactive Employees</div>
            {{-- <div class="stat-trend negative">-2% from yesterday</div> --}}
        </div>
    </a>
    
    <a href="{{ route('super-admin.attendance', ['date' => today()->format('Y-m-d')]) }}" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: white;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line><path d="M8 14h.01"></path><path d="M12 14h.01"></path><path d="M16 14h.01"></path><path d="M8 18h.01"></path><path d="M12 18h.01"></path><path d="M16 18h.01"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $todayAttendance['present'] }}</div>
            <div class="stat-label">Present Today</div>
            {{-- <div class="stat-trend">On time: {{ $todayAttendance['on_time'] ?? 'N/A' }}</div> --}}
        </div>
    </a>
    
    <a href="{{ route('super-admin.departments') }}" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: white;"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['total_departments'] }}</div>
            <div class="stat-label">Departments</div>
            {{-- <div class="stat-trend">5 with open positions</div> --}}
        </div>
    </a>
    
    <a href="{{ route('super-admin.applications', ['status' => 'pending']) }}" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon" style="background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%);">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: white;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8 10a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.574 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['pending_applications'] }}</div>
            <div class="stat-label">Pending Applications</div>
            {{-- <div class="stat-trend urgent">Requires attention</div> --}}
        </div>
    </a>
</div>






<!-- 2. Today's Attendance Analysis & Recent Applications -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px; margin-top: 30px;">
    <!-- Recent Applications -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Applications</h3>
        </div>
        <div class="card-body">
            @if($recentApplications->count() > 0)
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentApplications as $application)
                            <tr>
                                <td>{{ $application->employee->username }}</td>
                                <td>
                                    <span class="badge p-2 text-bg-secondary">{{ ucfirst(str_replace('_', ' ', $application->req_type)) }}</span>
                                </td>
                                <td>{{ $application->start_date->format('M d, Y') }}</td>
                                <td>
                                    @if($application->status === 'pending')
                                        <span class="badge p-2 text-bg-warning">Pending</span>
                                    @elseif($application->status === 'approved')
                                        <span class="badge p-2 text-bg-success">Approved</span>
                                    @else
                                        <span class="badge p-2 text-bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>
                                    @if($application->status === 'pending')
                                        <button class="btn btn-sm btn-success" onclick="updateApplicationStatus({{ $application->id }}, 'approved')" style="margin-bottom: 5px;">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="updateApplicationStatus({{ $application->id }}, 'rejected')" style="margin-bottom: 5px;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">No recent applications</p>
            @endif
        </div>
    </div>

    <!-- Today's Attendance -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Today's Attendance</h3>
        </div>
        <div class="card-body">
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="font-size: 48px; font-weight: 600; color: #c7511f; margin-bottom: 10px;">
                    {{ $todayAttendance['percentage'] }}%
                </div>
                <div style="color: #565959; font-size: 14px;">Attendance Rate</div>
            </div>
            
            <div style="margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span style="font-size: 14px; color: #0f1111;">Present</span>
                    <span style="font-size: 14px; font-weight: 500;">{{ $todayAttendance['present'] }}</span>
                </div>
                <div style="background: #e2e3e5; height: 8px; border-radius: 4px; overflow: hidden;">
                    <div style="background: #067d62; height: 100%; width: {{ $todayAttendance['percentage'] }}%; transition: width 0.3s;"></div>
                </div>
            </div>
            
            <div style="margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span style="font-size: 14px; color: #0f1111;">Absent</span>
                    <span style="font-size: 14px; font-weight: 500;">{{ $todayAttendance['absent'] }}</span>
                </div>
                <div style="background: #e2e3e5; height: 8px; border-radius: 4px; overflow: hidden;">
                    <div style="background: #d13212; height: 100%; width: {{ 100 - $todayAttendance['percentage'] }}%; transition: width 0.3s;"></div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="{{ route('super-admin.attendance') }}" class="btn btn-primary" 
                style="    
                    position: absolute;
                    
                    bottom: 20px;
                    left: 50%;
                    transform: translateX(-50%);
                ">
                    View Full Attendance
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 3. Top Attendance Employees -->
<div class="card" style="margin-top: 30px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title">Top Attendance - {{ now()->format('F Y') }}</h3>
        <a href="{{ route('super-admin.employee-history') }}" class="btn btn-sm btn-secondary">View All</a>
    </div>
    <div class="card-body">
        @if($topAttendanceEmployees->count() > 0)
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Days Present</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topAttendanceEmployees as $index => $employee)
                        <tr>
                            <td>
                                @if($index === 0)
                                    <span class="badge p-2" style="background: #FFD700; color: #000;"><i class="fas fa-trophy"></i> 1</span>
                                @elseif($index === 1)
                                    <span class="badge p-2" style="background: #C0C0C0; color: #000;"><i class="fas fa-medal"></i> 2</span>
                                @elseif($index === 2)
                                    <span class="badge p-2" style="background: #CD7F32; color: #fff;"><i class="fas fa-medal"></i> 3</span>
                                @else
                                    <span class="badge p-2 text-bg-secondary">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td>{{ $employee->emp_id }}</td>
                            <td>
                                <a href="{{ route('super-admin.employee-history', ['search' => $employee->username]) }}" style="color: #3b82f6; text-decoration: none;">
                                    {{ $employee->username }}
                                </a>
                            </td>
                            <td>{{ $employee->department->name ?? 'N/A' }}</td>
                            <td><span class="badge p-2 text-bg-success">{{ $employee->attendance_count }} days</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted">No attendance data available</p>
        @endif
    </div>
</div>

<!-- 4. Data Tables Section -->
<div style="display: grid; grid-template-columns: 1fr; gap: 20px; margin-top: 30px;">
    
    <!-- Recent Admins -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">Recent Admins</h3>
            <a href="{{ route('super-admin.admins') }}" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="card-body">
            @if($recentAdmins->count() > 0)
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Employees</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentAdmins as $admin)
                            <tr>
                                <td>{{ $admin->emp_id }}</td>
                                <td>{{ $admin->username }}</td>
                                <td><span class="badge p-2 text-bg-info">{{ $admin->assigned_employees_count }}</span></td>
                                <td>
                                    @if($admin->status === 'active')
                                        <span class="badge p-2 text-bg-success">Active</span>
                                    @else
                                        <span class="badge p-2 text-bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('super-admin.admins.show', $admin->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('super-admin.admins.edit', $admin->id) }}" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">No admins found</p>
            @endif
        </div>
    </div>

    <!-- Recent Employees -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">Recent Employees</h3>
            <a href="{{ route('super-admin.employees') }}" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="card-body">
            @if($recentEmployees->count() > 0)
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentEmployees as $employee)
                            <tr>
                                <td>{{ $employee->emp_id }}</td>
                                <td>{{ $employee->username }}</td>
                                <td>{{ $employee->department->name ?? 'N/A' }}</td>
                                <td>
                                    @if($employee->status === 'active')
                                        <span class="badge p-2 text-bg-success">Active</span>
                                    @else
                                        <span class="badge p-2 text-bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('super-admin.employees.show', $employee->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('super-admin.employees.edit', $employee->id) }}" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">No employees found</p>
            @endif
        </div>
    </div>

    <!-- Today's Attendance Details -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">Today's Attendance Details</h3>
            <a href="{{ route('super-admin.attendance') }}" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="card-body">
            @if($todayAttendanceDetails->count() > 0)
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Punch In</th>
                                <th>Punch Out</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($todayAttendanceDetails as $employee)
                            @php
                                $punchIn = $employee->timeEntries->where('entry_type', 'punch_in')->first();
                                $punchOut = $employee->timeEntries->where('entry_type', 'punch_out')->last();
                            @endphp
                            <tr>
                                <td>{{ $employee->username }}</td>
                                <td>{{ $employee->department->name ?? 'N/A' }}</td>
                                <td>{{ $punchIn ? $punchIn->entry_time->format('h:i A') : '-' }}</td>
                                <td>{{ $punchOut ? $punchOut->entry_time->format('h:i A') : '-' }}</td>
                                <td>
                                    @if($punchIn)
                                        <span class="badge p-2 text-bg-success">Present</span>
                                    @else
                                        <span class="badge p-2 text-bg-danger">Absent</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">No attendance records today</p>
            @endif
        </div>
    </div>

    <!-- Recent Salaries -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">Recent Salaries</h3>
            <a href="{{ route('super-admin.salaries') }}" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="card-body">
            @if($recentSalaries->count() > 0)
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Basic Salary</th>
                                <th>Gross Salary</th>
                                <th>Payment Mode</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSalaries as $salary)
                            <tr>
                                <td>{{ $salary->employee->username ?? 'N/A' }}</td>
                                <td>₹{{ number_format($salary->basic_salary, 2) }}</td>
                                <td>₹{{ number_format($salary->gross_salary, 2) }}</td>
                                <td>
                                    <span class="badge p-2 text-bg-{{ $salary->payment_mode === 'bank_transfer' ? 'primary' : 'warning' }}">
                                        {{ ucfirst(str_replace('_', ' ', $salary->payment_mode)) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('super-admin.salaries.edit', $salary->id) }}" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">No salary records found</p>
            @endif
        </div>
    </div>

</div>

<!-- 5. Quick Links -->
<div class="card" style="margin-top: 30px;">
    <div class="card-header">
        <h3 class="card-title">Quick Links</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
            
            <!-- Employee Management -->
            <div>
                <h5 style="color: #c7511f; margin-bottom: 12px; font-size: 14px; font-weight: 600; text-transform: uppercase;">
                    <i class="fas fa-users"></i> Employee Management
                </h5>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="{{ route('super-admin.employees') }}" class="quick-link">
                        <i class="fas fa-list"></i> All Employees
                    </a>
                    <a href="{{ route('super-admin.employees.create') }}" class="quick-link">
                        <i class="fas fa-user-plus"></i> Add Employee
                    </a>
                    <a href="{{ route('super-admin.employees', ['status' => 'active']) }}" class="quick-link">
                        <i class="fas fa-user-check"></i> Active Employees
                    </a>
                    <a href="{{ route('super-admin.employees', ['status' => 'inactive']) }}" class="quick-link">
                        <i class="fas fa-user-times"></i> Inactive Employees
                    </a>
                </div>
            </div>

            <!-- Attendance & Time -->
            <div>
                <h5 style="color: #c7511f; margin-bottom: 12px; font-size: 14px; font-weight: 600; text-transform: uppercase;">
                    <i class="fas fa-clock"></i> Attendance & Time
                </h5>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="{{ route('super-admin.attendance') }}" class="quick-link">
                        <i class="fas fa-calendar-check"></i> Today's Attendance
                    </a>
                    <a href="{{ route('super-admin.employee-history') }}" class="quick-link">
                        <i class="fas fa-history"></i> Employee History
                    </a>
                    <a href="{{ route('super-admin.schedule') }}" class="quick-link">
                        <i class="fas fa-calendar-alt"></i> Schedule & Holidays
                    </a>
                </div>
            </div>

            <!-- Applications & Requests -->
            <div>
                <h5 style="color: #c7511f; margin-bottom: 12px; font-size: 14px; font-weight: 600; text-transform: uppercase;">
                    <i class="fas fa-file-alt"></i> Applications
                </h5>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="{{ route('super-admin.applications') }}" class="quick-link">
                        <i class="fas fa-inbox"></i> All Applications
                    </a>
                    <a href="{{ route('super-admin.applications', ['status' => 'pending']) }}" class="quick-link">
                        <i class="fas fa-hourglass-half"></i> Pending Applications
                    </a>
                    <a href="{{ route('super-admin.applications', ['status' => 'approved']) }}" class="quick-link">
                        <i class="fas fa-check-circle"></i> Approved Applications
                    </a>
                    <a href="{{ route('super-admin.applications', ['status' => 'rejected']) }}" class="quick-link">
                        <i class="fas fa-times-circle"></i> Rejected Applications
                    </a>
                </div>
            </div>

            <!-- Salary Management -->
            <div>
                <h5 style="color: #c7511f; margin-bottom: 12px; font-size: 14px; font-weight: 600; text-transform: uppercase;">
                    <i class="fas fa-money-bill-wave"></i> Salary Management
                </h5>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="{{ route('super-admin.salaries') }}" class="quick-link">
                        <i class="fas fa-list-alt"></i> All Salaries
                    </a>
                    <a href="{{ route('super-admin.salaries.create') }}" class="quick-link">
                        <i class="fas fa-plus-circle"></i> Add Salary
                    </a>
                    <a href="{{ route('super-admin.reports') }}" class="quick-link">
                        <i class="fas fa-file-invoice-dollar"></i> Salary Reports
                    </a>
                </div>
            </div>

            <!-- Organization -->
            <div>
                <h5 style="color: #c7511f; margin-bottom: 12px; font-size: 14px; font-weight: 600; text-transform: uppercase;">
                    <i class="fas fa-building"></i> Organization
                </h5>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="{{ route('super-admin.departments') }}" class="quick-link">
                        <i class="fas fa-sitemap"></i> Departments
                    </a>
                    <a href="{{ route('super-admin.regions') }}" class="quick-link">
                        <i class="fas fa-map-marked-alt"></i> Regions
                    </a>
                    <a href="{{ route('super-admin.admins') }}" class="quick-link">
                        <i class="fas fa-user-shield"></i> Admins
                    </a>
                </div>
            </div>

            <!-- Settings & Configuration -->
            <div>
                <h5 style="color: #c7511f; margin-bottom: 12px; font-size: 14px; font-weight: 600; text-transform: uppercase;">
                    <i class="fas fa-cog"></i> Settings
                </h5>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="{{ route('super-admin.settings') }}" class="quick-link">
                        <i class="fas fa-sliders-h"></i> System Settings
                    </a>
                    <a href="{{ route('super-admin.location-settings.index') }}" class="quick-link">
                        <i class="fas fa-map-marker-alt"></i> Location Settings
                    </a>
                    <a href="{{ route('super-admin.tax-slabs') }}" class="quick-link">
                        <i class="fas fa-percentage"></i> Tax Settings
                    </a>
                    <a href="{{ route('super-admin.profile') }}" class="quick-link">
                        <i class="fas fa-user-cog"></i> My Profile
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
.quick-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: #f8f9fa;
    border: 1px solid #e2e3e5;
    border-radius: 6px;
    color: #0f1111;
    text-decoration: none;
    font-size: 13px;
    transition: all 0.2s;
}

.quick-link:hover {
    background: #fff;
    border-color: #c7511f;
    color: #c7511f;
    transform: translateX(4px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.quick-link i {
    width: 16px;
    text-align: center;
    font-size: 14px;
}
</style>
@endsection

@push('scripts')
<script>
function updateApplicationStatus(applicationId, status) {
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to ${status} this application?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: status === 'approved' ? '#067d62' : '#d13212',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Yes, ${status} it!`
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/super-admin/applications/${applicationId}/status`,
                method: 'POST',
                data: {
                    status: status,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#ff9900'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Something went wrong. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#d13212'
                    });
                }
            });
        }
    });
}
</script>
@endpush
