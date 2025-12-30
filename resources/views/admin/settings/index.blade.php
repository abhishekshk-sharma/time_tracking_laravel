@extends('admin.layouts.app')

@section('title', 'Settings')

@section('content')
<div class="page-header">
    <h1 class="page-title">System Settings</h1>
    <p class="page-subtitle">Configure your time tracking system</p>
</div>

<form action="{{ route('admin.settings.update') }}" method="POST">
    @csrf
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        
        <!-- Work Hours Settings -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clock" style="color: #ff9900; margin-right: 10px;"></i>
                    Work Hours Configuration
                </h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Work Start Time</label>
                    <input type="time" name="work_start_time" class="form-control" 
                           value="{{ $settings['work_start_time']->setting_value ?? '09:00' }}">
                    <small style="color: #565959;">Default time when employees should start work</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Work End Time</label>
                    <input type="time" name="work_end_time" class="form-control" 
                           value="{{ $settings['work_end_time']->setting_value ?? '18:00' }}">
                    <small style="color: #565959;">Default time when employees should end work</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Half Day Time</label>
                    <input type="time" name="half_day_time" class="form-control" 
                           value="{{ $settings['half_day_time']->setting_value ?? '13:00' }}">
                    <small style="color: #565959;">Time limit for half day consideration</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Late Threshold (minutes)</label>
                    <input type="number" name="late_threshold" class="form-control" 
                           value="{{ $settings['late_threshold']->setting_value ?? '15' }}" min="0" max="60">
                    <small style="color: #565959;">Minutes after start time to consider as late</small>
                </div>
            </div>
        </div>

        <!-- Leave Settings -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-alt" style="color: #067d62; margin-right: 10px;"></i>
                    Leave Management
                </h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Annual Leave Days</label>
                    <input type="number" name="annual_leave_days" class="form-control" 
                           value="{{ $settings['annual_leave_days']->setting_value ?? '21' }}" min="0" max="365">
                    <small style="color: #565959;">Total annual leave days per employee</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Sick Leave Days</label>
                    <input type="number" name="sick_leave_days" class="form-control" 
                           value="{{ $settings['sick_leave_days']->setting_value ?? '10' }}" min="0" max="365">
                    <small style="color: #565959;">Total sick leave days per employee</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Casual Leave Days</label>
                    <input type="number" name="casual_leave_days" class="form-control" 
                           value="{{ $settings['casual_leave_days']->setting_value ?? '12' }}" min="0" max="365">
                    <small style="color: #565959;">Total casual leave days per employee</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Auto-approve Leave</label>
                    <select name="auto_approve_leave" class="form-control">
                        <option value="0" {{ ($settings['auto_approve_leave']->setting_value ?? '0') == '0' ? 'selected' : '' }}>No</option>
                        <option value="1" {{ ($settings['auto_approve_leave']->setting_value ?? '0') == '1' ? 'selected' : '' }}>Yes</option>
                    </select>
                    <small style="color: #565959;">Automatically approve leave applications</small>
                </div>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bell" style="color: #c7511f; margin-right: 10px;"></i>
                    Notifications
                </h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Email Notifications</label>
                    <select name="email_notifications" class="form-control">
                        <option value="0" {{ ($settings['email_notifications']->setting_value ?? '1') == '0' ? 'selected' : '' }}>Disabled</option>
                        <option value="1" {{ ($settings['email_notifications']->setting_value ?? '1') == '1' ? 'selected' : '' }}>Enabled</option>
                    </select>
                    <small style="color: #565959;">Send email notifications for applications</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Late Arrival Notifications</label>
                    <select name="late_notifications" class="form-control">
                        <option value="0" {{ ($settings['late_notifications']->setting_value ?? '1') == '0' ? 'selected' : '' }}>Disabled</option>
                        <option value="1" {{ ($settings['late_notifications']->setting_value ?? '1') == '1' ? 'selected' : '' }}>Enabled</option>
                    </select>
                    <small style="color: #565959;">Notify admins about late arrivals</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Absence Notifications</label>
                    <select name="absence_notifications" class="form-control">
                        <option value="0" {{ ($settings['absence_notifications']->setting_value ?? '1') == '0' ? 'selected' : '' }}>Disabled</option>
                        <option value="1" {{ ($settings['absence_notifications']->setting_value ?? '1') == '1' ? 'selected' : '' }}>Enabled</option>
                    </select>
                    <small style="color: #565959;">Notify admins about employee absences</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Admin Email</label>
                    <input type="email" name="admin_email" class="form-control" 
                           value="{{ $settings['admin_email']->setting_value ?? 'admin@company.com' }}">
                    <small style="color: #565959;">Email address for admin notifications</small>
                </div>
            </div>
        </div>

        <!-- System Settings -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cog" style="color: #7c3aed; margin-right: 10px;"></i>
                    System Configuration
                </h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-control" 
                           value="{{ $settings['company_name']->setting_value ?? 'Your Company' }}">
                    <small style="color: #565959;">Name displayed in the system</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Timezone</label>
                    <select name="timezone" class="form-control">
                        <option value="UTC" {{ ($settings['timezone']->setting_value ?? 'UTC') == 'UTC' ? 'selected' : '' }}>UTC</option>
                        <option value="America/New_York" {{ ($settings['timezone']->setting_value ?? 'UTC') == 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                        <option value="America/Chicago" {{ ($settings['timezone']->setting_value ?? 'UTC') == 'America/Chicago' ? 'selected' : '' }}>Central Time</option>
                        <option value="America/Denver" {{ ($settings['timezone']->setting_value ?? 'UTC') == 'America/Denver' ? 'selected' : '' }}>Mountain Time</option>
                        <option value="America/Los_Angeles" {{ ($settings['timezone']->setting_value ?? 'UTC') == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                        <option value="Asia/Kolkata" {{ ($settings['timezone']->setting_value ?? 'UTC') == 'Asia/Kolkata' ? 'selected' : '' }}>India Standard Time</option>
                    </select>
                    <small style="color: #565959;">System timezone for all operations</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Date Format</label>
                    <select name="date_format" class="form-control">
                        <option value="Y-m-d" {{ ($settings['date_format']->setting_value ?? 'Y-m-d') == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                        <option value="m/d/Y" {{ ($settings['date_format']->setting_value ?? 'Y-m-d') == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY</option>
                        <option value="d/m/Y" {{ ($settings['date_format']->setting_value ?? 'Y-m-d') == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY</option>
                        <option value="M d, Y" {{ ($settings['date_format']->setting_value ?? 'Y-m-d') == 'M d, Y' ? 'selected' : '' }}>Mon DD, YYYY</option>
                    </select>
                    <small style="color: #565959;">Date display format throughout the system</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Session Timeout (minutes)</label>
                    <input type="number" name="session_timeout" class="form-control" 
                           value="{{ $settings['session_timeout']->setting_value ?? '120' }}" min="30" max="480">
                    <small style="color: #565959;">Auto-logout after inactivity</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Button -->
    <div style="margin-top: 30px; text-align: center;">
        <button type="submit" class="btn btn-primary" style="padding: 12px 40px; font-size: 16px;">
            <i class="fas fa-save"></i> Save All Settings
        </button>
    </div>
</form>

<!-- System Information -->
<div class="card" style="margin-top: 30px;">
    <div class="card-header">
        <h3 class="card-title">System Information</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 18px; font-weight: 600; color: #c7511f; margin-bottom: 5px;">
                    Laravel {{ app()->version() }}
                </div>
                <div style="color: #565959; font-size: 14px;">Framework Version</div>
            </div>
            <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 18px; font-weight: 600; color: #c7511f; margin-bottom: 5px;">
                    PHP {{ PHP_VERSION }}
                </div>
                <div style="color: #565959; font-size: 14px;">PHP Version</div>
            </div>
            <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 18px; font-weight: 600; color: #c7511f; margin-bottom: 5px;">
                    {{ now()->format('M d, Y H:i') }}
                </div>
                <div style="color: #565959; font-size: 14px;">Server Time</div>
            </div>
            <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 18px; font-weight: 600; color: #c7511f; margin-bottom: 5px;">
                    v2.0.0
                </div>
                <div style="color: #565959; font-size: 14px;">System Version</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('form').on('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Saving Settings...',
            text: 'Please wait while we update your settings.',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Submit the form
        setTimeout(() => {
            this.submit();
        }, 1000);
    });
});
</script>
@endpush