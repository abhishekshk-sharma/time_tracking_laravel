@extends('super-admin.layouts.app')

@section('title', 'Edit Admin')

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Edit Administrator</h1>
            <p class="page-subtitle">{{ $admin->name }} ({{ $admin->emp_id }})</p>
        </div>
        <a href="{{ route('super-admin.admins') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<form method="POST" action="{{ route('super-admin.admins.update', $admin) }}">
    @csrf
    @method('PUT')
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        <!-- Admin Details -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Admin Information</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $admin->name }}" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="{{ $admin->username }}" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ $admin->email }}" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ $admin->phone }}">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Employee ID</label>
                    <input type="text" class="form-control" value="{{ $admin->emp_id }}" readonly style="background: #f5f5f7;">
                </div>
            </div>
        </div>

        <!-- Employee Assignment -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Assign Employees</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Select Employees to Assign</label>
                    <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 12px;">
                        @foreach($unassignedEmployees as $employee)
                            <div style="display: flex; align-items: center; margin-bottom: 8px;">
                                <input type="checkbox" 
                                       name="assigned_employees[]" 
                                       value="{{ $employee->emp_id }}" 
                                       id="emp_{{ $employee->emp_id }}"
                                       {{ $employee->referrance == $admin->emp_id ? 'checked' : '' }}
                                       style="margin-right: 8px;">
                                <label for="emp_{{ $employee->emp_id }}" style="margin: 0; cursor: pointer; flex: 1;">
                                    <div style="display: flex; align-items: center;">
                                        <div style="width: 24px; height: 24px; background: #ff6b35; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px; font-weight: 600; margin-right: 8px;">
                                            {{ strtoupper(substr($employee->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div style="font-weight: 500; font-size: 14px;">{{ $employee->name }}</div>
                                            <div style="font-size: 11px; color: #86868b;">{{ $employee->emp_id }} - {{ $employee->department }}</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                        
                        @if($unassignedEmployees->count() == 0)
                            <div style="text-align: center; color: #86868b; padding: 20px;">
                                <i class="fas fa-users" style="font-size: 24px; margin-bottom: 8px; opacity: 0.3;"></i>
                                <p>No employees available for assignment</p>
                            </div>
                        @endif
                    </div>
                    <small style="color: #86868b;">Select employees to assign to this administrator</small>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 30px; text-align: center;">
        <button type="submit" class="btn btn-primary" style="padding: 12px 40px;">
            <i class="fas fa-save"></i> Update Administrator
        </button>
    </div>
</form>
@endsection