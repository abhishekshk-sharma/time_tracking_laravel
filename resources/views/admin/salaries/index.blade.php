@extends('admin.layouts.app')

@section('title', 'Salary Management')

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Salary Management</h1>
            <p class="page-subtitle">Manage salary details for your assigned employees</p>
        </div>
        <a href="{{ route('admin.salaries.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Salary
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Employee Salaries</h3>
        
    </div>
    <div class="card-body">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Basic Salary</th>
                        <th>HRA</th>
                        <th>PF</th>
                        <th>PT</th>
                        <th>Conveyance</th>
                        <th>Payment Mode</th>
                        <th>Gross Salary</th>
                        <th>Effective From</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salaries as $salary)
                    <tr>
                        <td>{{ $salary->emp_id }}</td>
                        <td>{{ $salary->employee->name ?? 'N/A' }}</td>
                        <td>₹{{ number_format($salary->basic_salary, 2) }}</td>
                        <td>₹{{ number_format($salary->hra, 2) }}</td>
                        <td>₹{{ number_format($salary->pf, 2) }}</td>
                        <td>₹{{ number_format($salary->pt, 2) }}</td>
                        <td>₹{{ number_format($salary->conveyance_allowance, 2) }}</td>
                        <td>
                            <span class="badge {{ $salary->payment_mode == 'cash' ? 'badge-warning' : 'badge-success' }}">
                                {{ $salary->payment_mode == 'cash' ? 'Cash' : 'Bank Transfer' }}
                            </span>
                        </td>
                        <td><strong>₹{{ number_format($salary->gross_salary, 2) }}</strong></td>
                        <td>{{ $salary->effective_from->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('admin.salaries.edit', $salary) }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 40px; color: #86868b;">
                            <i class="fas fa-money-bill-wave fa-3x" style="margin-bottom: 16px; opacity: 0.3;"></i>
                            <div>No salary records found for your employees</div>
                            <div style="margin-top: 8px;">
                                <a href="{{ route('admin.salaries.create') }}" class="btn btn-primary">Add First Salary</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($salaries->hasPages())
            {{ $salaries->links() }}
        @endif
    </div>
</div>
@endsection