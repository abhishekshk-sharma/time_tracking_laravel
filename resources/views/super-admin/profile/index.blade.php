@extends('super-admin.layouts.app')

@section('title', 'Profile')

@section('content')
<div class="page-header">
    <h1 class="page-title">Profile Settings</h1>
    <p class="page-subtitle">Manage your account information and security settings</p>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-edit me-2"></i>
                    Profile Information
                </h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('super-admin.profile.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $superAdmin->name) }}" required>
                                @error('name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" value="{{ old('username', $superAdmin->username) }}" required>
                                @error('username')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $superAdmin->email) }}" required>
                        @error('email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    <h5 class="mb-3">
                        <i class="fas fa-lock me-2"></i>
                        Change Password
                    </h5>
                    <p class="text-muted mb-3">Leave password fields empty if you don't want to change your password.</p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">New Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" class="form-control" placeholder="Enter new password" id="password">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="password-icon"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm new password" id="password_confirmation">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                        <i class="fas fa-eye" id="password_confirmation-icon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Update Profile
                        </button>
                        <a href="{{ route('super-admin.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle me-2"></i>
                    Account Information
                </h3>
            </div>
            <div class="card-body">
                <div class="info-item">
                    <label>Account Type:</label>
                    <span class="value">Super Administrator</span>
                </div>
                <div class="info-item">
                    <label>Account Created:</label>
                    <span class="value">{{ $superAdmin->created_at->format('M d, Y') }}</span>
                </div>
                <div class="info-item">
                    <label>Last Updated:</label>
                    <span class="value">{{ $superAdmin->updated_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="info-item">
                    <label>Status:</label>
                    <span class="badge p-2 bg-success">Active</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shield-alt me-2"></i>
                    Security Tips
                </h3>
            </div>
            <div class="card-body">
                <ul class="security-tips">
                    <li>Use a strong password with at least 8 characters</li>
                    <li>Include uppercase, lowercase, numbers, and symbols</li>
                    <li>Don't share your login credentials</li>
                    <li>Log out when using shared computers</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')


<style>
.info-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e9ecef;
}

.info-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.info-item label {
    font-weight: 500;
    color: #6c757d;
}

.info-item .value {
    font-weight: 600;
    color: #495057;
}

.security-tips {
    list-style: none;
    padding: 0;
}

.security-tips li {
    padding: 8px 0;
    border-bottom: 1px solid #f1f3f4;
    color: #5f6368;
    font-size: 14px;
}

.security-tips li:last-child {
    border-bottom: none;
}

.security-tips li:before {
    content: "✓";
    color: #34a853;
    font-weight: bold;
    margin-right: 8px;
}

.form-actions {
    display: flex;
    gap: 12px;
    align-items: center;
}
</style>
@endpush

@push('scripts')
<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endpush