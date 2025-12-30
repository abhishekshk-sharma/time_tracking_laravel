@extends('super-admin.layouts.app')

@section('title', 'Admin Management')

@section('content')
<div class="page-header">
    <h1 class="page-title">Admin Management</h1>
    <p class="page-subtitle">Manage administrators and their assigned employees</p>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Administrators ({{ $admins->total() }} total)</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        @if($admins->count() > 0)
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Admin Details</th>
                            <th>Contact</th>
                            <th>Assigned Employees</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admins as $admin)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <div style="width: 40px; height: 40px; background: #ff6b35; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; margin-right: 12px;">
                                        {{ strtoupper(substr($admin->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 600;">{{ $admin->name }}</div>
                                        <div style="font-size: 12px; color: #86868b;">{{ $admin->username }} ({{ $admin->emp_id }})</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $admin->email }}</div>
                                <div style="font-size: 12px; color: #86868b;">{{ $admin->phone ?: 'No phone' }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #ff6b35;">{{ $admin->assigned_employees_count }}</div>
                                <div style="font-size: 12px; color: #86868b;">employees assigned</div>
                            </td>
                            <td>
                                @if($admin->status === 'active')
                                    <span style="background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600;">Active</span>
                                @else
                                    <span style="background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600;">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="{{ route('super-admin.admins.show', $admin) }}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('super-admin.admins.edit', $admin) }}" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($admins->hasPages())
                <div style="padding: 20px;">
                    {{ $admins->links() }}
                </div>
            @endif
        @else
            <div style="text-align: center; padding: 60px; color: #86868b;">
                <i class="fas fa-user-shield" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
                <h3>No administrators found</h3>
                <p>No admin users are currently in the system</p>
            </div>
        @endif
    </div>
</div>
@endsection