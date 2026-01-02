@extends('super-admin.layouts.app')

@section('title', 'Edit Employee')

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Edit Employee</h1>
            <p class="page-subtitle">{{ $employee->username }} ({{ $employee->emp_id }})</p>
        </div>
        <a href="{{ route('super-admin.employees') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            {{-- <li>{{$errors}}</li> --}}
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST" action="{{ route('super-admin.employees.update', $employee) }}">
    @csrf
    @method('PUT')
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        <!-- Employee Details -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Employee Information</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Employee ID</label>
                    <input type="text" class="form-control" value="{{ $employee->emp_id }}" readonly style="background: #f5f5f7;">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="username" class="form-control" value="{{ old('username', $employee->username) }}" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $employee->email) }}" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select class="form-control" name="role" required>
                        <option value="employee" {{ $employee->role == 'employee' ? 'selected' : '' }}>Employee</option>
                        <option value="admin" {{ $employee->role == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status" required>
                        <option value="active" {{ $employee->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $employee->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Additional Details -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Additional Information</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <select class="form-control" name="department_id">
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ $employee->department_id == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Region</label>
                    <select class="form-control" name="region_id">
                        <option value="">Select Region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->id }}" {{ $employee->region_id == $region->id ? 'selected' : '' }}>
                                {{ $region->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Position</label>
                    <input type="text" name="position" class="form-control" value="{{ old('position', $employee->position) }}">
                </div>
             
                <div class="form-group">
                    <label class="form-label">Referrance Admin</label>
                    <select class="form-control" name="referrance">
                        <option value="">Select Admin</option>
                        
                        @foreach($admins as $admin)
                            <option value="{{ $admin->emp_id }}" {{ $employee->referrance == $admin->emp_id ? 'selected' : '' }}>
                                {{ $admin->username }} --  {{ $admin->department?->name ?? 'No Department Assigned' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 30px; text-align: center;">
        <button type="submit" class="btn btn-primary" style="padding: 12px 40px;">
            <i class="fas fa-save"></i> Update Employee
        </button>
    </div>
</form>

@endsection