@extends('super-admin.layouts.app')

@section('title', 'Settings')

@section('content')
<div class="page-header">
    <h1 class="page-title">System Settings</h1>
    <p class="page-subtitle">Configure your time tracking system</p>
</div>

<form action="{{ route('super-admin.settings.update') }}" method="POST">
    @csrf
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        
        <!-- Work Hours Settings -->
        <div class="card workhourssetting" >
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
                    <label class="form-label">Lunch Duration</label>
                    <input type="number" name="lunch_duration" class="form-control" 
                           value="{{ $settings['lunch_duration']->setting_value ?? '60' }}" min="0" max="120">
                    <small style="color: #565959;">Minutes for lunch break</small>
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
                    <label class="form-label">Sick Leave Days</label>
                    <input type="number" name="sick_leave_days" class="form-control" 
                           value="{{ $settings['sick_leave']->setting_value ?? '10' }}" min="0" max="365">
                    <small style="color: #565959;">Total sick leave days per employee</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Casual Leave Days</label>
                    <input type="number" name="casual_leave_days" class="form-control" 
                           value="{{ $settings['casual_leave']->setting_value ?? '12' }}" min="0" max="365">
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
                
                <div class="form-group">
                    <label class="form-label">Weekend Policy</label>
                    <select name="weekend_policy" id="weekendPolicy" class="form-control" required>
                        @php
                            $currentPolicy = 'sunday_only';
                            if(isset($settings['weekend_policy'])) {
                                $policyData = json_decode($settings['weekend_policy']->setting_value, true);
                                if($policyData['recurring_days'] == [0, 6]) $currentPolicy = 'sat_sun';
                                elseif(isset($policyData['specific_pattern'][6]) && $policyData['specific_pattern'][6] == [2, 4]) $currentPolicy = 'sun_2_4_sat';
                                elseif(isset($policyData['specific_pattern'][6])) $currentPolicy = 'sun_custom_sat';
                            }
                        @endphp
                        <option value="sunday_only" {{ $currentPolicy == 'sunday_only' ? 'selected' : '' }}>Sunday Only</option>
                        <option value="sat_sun" {{ $currentPolicy == 'sat_sun' ? 'selected' : '' }}>Saturday & Sunday</option>
                        <option value="sun_2_4_sat" {{ $currentPolicy == 'sun_2_4_sat' ? 'selected' : '' }}>Sunday & 2nd/4th Saturday</option>
                        <option value="sun_custom_sat" {{ $currentPolicy == 'sun_custom_sat' ? 'selected' : '' }}>Sunday & Custom Saturday</option>
                    </select>
                    <small style="color: #565959;">Configure which days are considered weekends</small>
                    <input type="hidden" name="custom_saturday_weeks" id="customSaturdayWeeks">
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Handle weekend policy change
    $('#weekendPolicy').on('change', function() {
        if ($(this).val() === 'sun_custom_sat') {
            showCustomSaturdayModal();
        }
    });
    
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

function showCustomSaturdayModal() {
    Swal.fire({
        title: '<i class="fas fa-calendar-week"></i> Custom Saturday Configuration',
        html: `
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-info-circle me-2"></i>Select which Saturdays should be holidays:</label>
                <div class="mt-3">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" value="1" id="week1">
                        <label class="form-check-label" for="week1">
                            <i class="fas fa-calendar-day me-2"></i>1st Saturday of the month
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" value="2" id="week2">
                        <label class="form-check-label" for="week2">
                            <i class="fas fa-calendar-day me-2"></i>2nd Saturday of the month
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" value="3" id="week3">
                        <label class="form-check-label" for="week3">
                            <i class="fas fa-calendar-day me-2"></i>3rd Saturday of the month
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" value="4" id="week4">
                        <label class="form-check-label" for="week4">
                            <i class="fas fa-calendar-day me-2"></i>4th Saturday of the month
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="5" id="week5">
                        <label class="form-check-label" for="week5">
                            <i class="fas fa-calendar-day me-2"></i>5th Saturday of the month (if exists)
                        </label>
                    </div>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-save me-2"></i>Save Configuration',
        cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
        buttonsStyling: false,
        customClass: {
            confirmButton: 'btn btn-primary me-2',
            cancelButton: 'btn btn-secondary'
        },
        width: '500px',
        preConfirm: () => {
            const selectedWeeks = [];
            document.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
                selectedWeeks.push(parseInt(checkbox.value));
            });
            
            if (selectedWeeks.length === 0) {
                Swal.showValidationMessage('<i class="fas fa-exclamation-triangle"></i> Please select at least one Saturday');
                return false;
            }
            
            return selectedWeeks;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('customSaturdayWeeks').value = JSON.stringify(result.value);
            
            Swal.fire({
                title: 'Configuration Saved!',
                text: `Selected Saturdays: ${result.value.map(w => w + getOrdinalSuffix(w)).join(', ')}`,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            // Reset to previous selection if cancelled
            document.getElementById('weekendPolicy').value = 'sunday_only';
        }
    });
}

function getOrdinalSuffix(num) {
    const suffixes = ['th', 'st', 'nd', 'rd'];
    const v = num % 100;
    return suffixes[(v - 20) % 10] || suffixes[v] || suffixes[0];
}
</script>
@endpush