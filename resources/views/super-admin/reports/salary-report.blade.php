@extends('super-admin.layouts.app')

@section('title', 'Salary Report - ' . $employee->username)

@section('content')
<div class="page-header">
    <h1 class="page-title">Salary Report</h1>
    <p class="page-subtitle">Review and edit salary details before generating final report</p>
</div>

<!-- Debug Info -->
{{-- <div class="alert alert-info" style="margin-bottom: 20px;">
    <strong>Debug Info:</strong><br>
    Basic Salary: {{ $salary->basic_salary ?? 'NULL' }}<br>
    HRA: {{ $salary->hra ?? 'NULL' }}<br>
    PF: {{ $salary->pf ?? 'NULL' }}<br>
    PT: {{ $salary->pt ?? 'NULL' }}<br>
    Conveyance: {{ $salary->conveyance_allowance ?? 'NULL' }}<br>
    Calculated Basic: {{ $calculatedBasicSalary ?? 'NULL' }}<br>
    Gross Earnings: {{ $grossEarnings ?? 'NULL' }}<br>
</div> --}}

<form id="salaryReportForm" method="POST" action="{{ route('super-admin.salary-slip.generate') }}" target="_blank">
    @csrf
    <input type="hidden" name="emp_id" value="{{ $employee->emp_id }}">
    <input type="hidden" name="month" value="{{ $month }}">
    <input type="hidden" name="format" id="exportFormat" value="pdf">

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        
        <!-- Personal Information -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Personal Information</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Employee Name</label>
                    <input type="text" name="employee_name" class="form-control" value="{{ $employee->username }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Employee ID</label>
                    <input type="text" name="employee_id" class="form-control" value="{{ $employee->emp_id }}" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Designation</label>
                    <input type="text" name="designation" class="form-control" value="{{ $employee->position ?? 'N/A' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <input type="text" name="department" class="form-control" value="{{ $employee->department->name ?? 'IT' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Month/Year</label>
                    <input type="text" name="month_year" class="form-control" value="{{ $monthName }}" readonly>
                </div>
            </div>
        </div>

        <!-- Attendance Information -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Attendance Summary</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Total Working Days</label>
                    <input type="number" name="total_working_days" class="form-control" value="{{ $salaryCalculation['filtered_total_days'] }}" step="0.5">
                </div>
                <div class="form-group">
                    <label class="form-label">Payable Attendance Days</label>
                    <input type="number" name="payable_days" class="form-control" value="{{ $salaryCalculation['payable_attendance_days'] }}" step="0.5">
                </div>
                <div class="form-group">
                    <label class="form-label">Per Day Rate</label>
                    <input type="number" name="per_day_rate" class="form-control" value="{{ $salaryCalculation['per_day_rate'] }}" step="0.01" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Original Basic Salary</label>
                    <input type="number" name="original_basic" class="form-control" value="{{ $salaryCalculation['original_basic_salary'] }}" step="0.01" readonly>
                </div>
                
                <!-- Attendance Breakdown -->
                <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
                    <h5 style="margin-bottom: 15px; color: #374151;">Attendance Breakdown</h5>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                        <div class="form-group">
                            <label class="form-label">Holidays</label>
                            <div style="position: relative;">
                                <input type="number" name="holidays" class="form-control" value="{{ $attendanceBreakdown['holidays_count'] }}" readonly>
                                @if($attendanceBreakdown['holidays_count'] > 0)
                                <button type="button" class="btn btn-sm btn-outline-info" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); padding: 2px 8px; font-size: 12px;" onclick="toggleDetails('holidays')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sick Leave</label>
                            <div style="position: relative;">
                                <input type="number" name="sick_leave" class="form-control" value="{{ $attendanceBreakdown['sick_leave_count'] }}" readonly>
                                @if($attendanceBreakdown['sick_leave_count'] > 0)
                                <button type="button" class="btn btn-sm btn-outline-info" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); padding: 2px 8px; font-size: 12px;" onclick="toggleDetails('sickLeave')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Casual Leave</label>
                            <div style="position: relative;">
                                <input type="number" name="casual_leave" class="form-control" value="{{ $attendanceBreakdown['casual_leave_count'] }}" readonly>
                                @if($attendanceBreakdown['casual_leave_count'] > 0)
                                <button type="button" class="btn btn-sm btn-outline-info" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); padding: 2px 8px; font-size: 12px;" onclick="toggleDetails('casualLeave')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Half Days</label>
                            <div style="position: relative;">
                                <input type="number" name="half_days" class="form-control" value="{{ $attendanceBreakdown['half_days_count'] }}" readonly>
                                @if($attendanceBreakdown['half_days_count'] > 0)
                                <button type="button" class="btn btn-sm btn-outline-info" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); padding: 2px 8px; font-size: 12px;" onclick="toggleDetails('halfDays')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Week Off</label>
                            <div style="position: relative;">
                                <input type="number" name="week_off" class="form-control" value="{{ $attendanceBreakdown['week_off_count'] }}" readonly>
                                @if($attendanceBreakdown['week_off_count'] > 0)
                                <button type="button" class="btn btn-sm btn-outline-info" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); padding: 2px 8px; font-size: 12px;" onclick="toggleDetails('weekOff')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Absent Days</label>
                            <div style="position: relative;">
                                <input type="number" name="absent_days" class="form-control" value="{{ $attendanceBreakdown['absent_days_count'] }}" readonly>
                                @if($attendanceBreakdown['absent_days_count'] > 0)
                                <button type="button" class="btn btn-sm btn-outline-info" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); padding: 2px 8px; font-size: 12px;" onclick="toggleDetails('absentDays')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Short Attendance</label>
                            <div style="position: relative;">
                                <input type="number" name="short_attendance" class="form-control" value="{{ $attendanceBreakdown['short_attendance_count'] }}" readonly>
                                @if($attendanceBreakdown['short_attendance_count'] > 0)
                                <button type="button" class="btn btn-sm btn-outline-info" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); padding: 2px 8px; font-size: 12px;" onclick="toggleDetails('shortAttendance')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Detail sections for all attendance types -->
                    @if($attendanceBreakdown['holidays_count'] > 0)
                    <div id="holidaysDetails" style="display: none; margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                        <h6 style="margin-bottom: 10px; color: #6b7280;">Holidays Details:</h6>
                        <div style="max-height: 150px; overflow-y: auto;">
                            @foreach($attendanceBreakdown['holidays'] as $item)
                            <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #e5e7eb;">
                                <span>{{ $item['date'] }}</span>
                                <span>({{ $item['attendance'] }} day)</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    @if($attendanceBreakdown['sick_leave_count'] > 0)
                    <div id="sickLeaveDetails" style="display: none; margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                        <h6 style="margin-bottom: 10px; color: #6b7280;">Sick Leave Details:</h6>
                        <div style="max-height: 150px; overflow-y: auto;">
                            @foreach($attendanceBreakdown['sick_leave'] as $item)
                            <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #e5e7eb;">
                                <span>{{ $item['date'] }}</span>
                                <span>({{ $item['attendance'] }} day)</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    @if($attendanceBreakdown['casual_leave_count'] > 0)
                    <div id="casualLeaveDetails" style="display: none; margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                        <h6 style="margin-bottom: 10px; color: #6b7280;">Casual Leave Details:</h6>
                        <div style="max-height: 150px; overflow-y: auto;">
                            @foreach($attendanceBreakdown['casual_leave'] as $item)
                            <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #e5e7eb;">
                                <span>{{ $item['date'] }}</span>
                                <span>({{ $item['attendance'] }} day)</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    @if($attendanceBreakdown['half_days_count'] > 0)
                    <div id="halfDaysDetails" style="display: none; margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                        <h6 style="margin-bottom: 10px; color: #6b7280;">Half Days Details:</h6>
                        <div style="max-height: 150px; overflow-y: auto;">
                            @foreach($attendanceBreakdown['half_days'] as $item)
                            <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #e5e7eb;">
                                <span>{{ $item['date'] }}</span>
                                <span>({{ $item['attendance'] }} day)</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    @if($attendanceBreakdown['week_off_count'] > 0)
                    <div id="weekOffDetails" style="display: none; margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                        <h6 style="margin-bottom: 10px; color: #6b7280;">Week Off Details:</h6>
                        <div style="max-height: 150px; overflow-y: auto;">
                            @foreach($attendanceBreakdown['week_off'] as $item)
                            <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #e5e7eb;">
                                <span>{{ $item['date'] }}</span>
                                <span>({{ $item['attendance'] }} day)</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    @if($attendanceBreakdown['absent_days_count'] > 0)
                    <div id="absentDaysDetails" style="display: none; margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                        <h6 style="margin-bottom: 10px; color: #6b7280;">Absent Days Details:</h6>
                        <div style="max-height: 150px; overflow-y: auto;">
                            @foreach($attendanceBreakdown['absent_days'] as $item)
                            <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #e5e7eb;">
                                <span>{{ $item['date'] }}</span>
                                <span>({{ $item['attendance'] }} day)</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    @if($attendanceBreakdown['short_attendance_count'] > 0)
                    <div id="shortAttendanceDetails" style="display: none; margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                        <h6 style="margin-bottom: 10px; color: #6b7280;">Short Attendance Details:</h6>
                        <div style="max-height: 150px; overflow-y: auto;">
                            @foreach($attendanceBreakdown['short_attendance'] as $item)
                            <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #e5e7eb;">
                                <span>{{ $item['date'] }}</span>
                                <span>{{ $item['hours'] }} hrs ({{ $item['attendance'] }} day)</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Salary Details -->
    <div class="card" style="margin-top: 30px;">
        <div class="card-header">
            <h3 class="card-title">Salary Details</h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                
                <!-- Earnings -->
                <div>
                    <h4 style="color: #10b981; margin-bottom: 15px;">Earnings</h4>
                    <div class="form-group">
                        <label class="form-label">Basic Salary</label>
                        <input type="number" name="basic_salary" id="basic_salary" class="form-control" value="{{ $calculatedBasicSalary }}" step="0.01">
                    </div>
                    <div class="form-group">
                        <label class="form-label">HRA</label>
                        <input type="number" name="hra" id="hra" class="form-control" value="{{ $salary->hra }}" step="0.01">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Conveyance Allowance</label>
                        <input type="number" name="conveyance" id="conveyance" class="form-control" value="{{ $salary->conveyance_allowance }}" step="0.01">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Special Allowance</label>
                        <input type="number" name="special_allowance" id="special_allowance" class="form-control" value="{{ $salary->special_allowance }}" step="0.01">
                    </div>
                </div>

                <!-- Deductions -->
                <div>
                    <h4 style="color: #ef4444; margin-bottom: 15px;">Deductions</h4>
                    <div class="form-group">
                        <label class="form-label">Professional Tax (PT)</label>
                        <input type="number" name="pt" id="pt" class="form-control" value="{{ $salary->pt }}" step="0.01">
                    </div>
                    <div class="form-group">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                            <input type="checkbox" id="auto_pf" name="auto_pf" {{ $salary->is_pf ? 'checked' : '' }} style="transform: scale(1.2);">
                            <label for="auto_pf" style="margin: 0; font-size: 14px; color: #6b7280;">Auto calculate PF (24% of basic salary)</label>
                        </div>
                        <label class="form-label">Provident Fund (PF)</label>
                        <input type="number" name="pf" id="pf" class="form-control" value="{{ (($salary->pf)* 2 ) }}" step="0.01">
                    </div>
                    <div class="form-group">
                        <label class="form-label">TDS</label>
                        <input type="number" name="tds" id="tds" class="form-control" value="{{ $salary->tds }}" step="0.01">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Healthcare Cess</label>
                        <input type="number" name="healthcare_cess" id="healthcare_cess" class="form-control" value="{{ $salary->healthcare_cess }}" step="0.01">
                    </div>
                </div>

                <!-- Summary -->
                <div>
                    <h4 style="color: #6366f1; margin-bottom: 15px;">Summary</h4>
                    <div class="form-group">
                        <label class="form-label">Gross Earnings</label>
                        <input type="number" name="gross_earnings" id="gross_earnings" class="form-control" value="{{ $grossEarnings }}" step="0.01" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Total Deductions</label>
                        <input type="number" name="total_deductions" id="total_deductions" class="form-control" value="{{ $totalDeductions }}" step="0.01" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Net Salary Payable</label>
                        <input type="number" name="net_salary" id="net_salary" class="form-control" value="{{ $netSalary }}" step="0.01" readonly style="font-weight: bold; background: #f0fdf4;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div style="display: flex; gap: 15px; margin-top: 30px; justify-content: center;">
        <button type="button" class="btn btn-primary" onclick="generateReport()">
            <i class="fas fa-file-pdf"></i> Generate PDF
        </button>
        <a href="{{ route('super-admin.reports') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Reports
        </a>
    </div>
</form>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-calculate totals when values change
    function calculateTotals() {
        const basic = parseFloat($('#basic_salary').val()) || 0;
        const hra = parseFloat($('#hra').val()) || 0;
        const conveyance = parseFloat($('#conveyance').val()) || 0;
        const specialAllowance = parseFloat($('#special_allowance').val()) || 0;
        const pt = parseFloat($('#pt').val()) || 0;
        const pf = parseFloat($('#pf').val()) || 0;
        const tds = parseFloat($('#tds').val()) || 0;
        const healthcareCess = parseFloat($('#healthcare_cess').val()) || 0;
        
        const grossEarnings = basic + hra + conveyance + specialAllowance;
        const totalDeductions = pt + pf + tds + healthcareCess;
        const netSalary = grossEarnings - totalDeductions;
        
        $('#gross_earnings').val(grossEarnings.toFixed(2));
        $('#total_deductions').val(totalDeductions.toFixed(2));
        $('#net_salary').val(netSalary.toFixed(2));
    }
    
    function calculatePF() {
        if ($('#auto_pf').is(':checked')) {
            const basicSalary = parseFloat($('#basic_salary').val()) || 0;
            const pf = (basicSalary * 0.24).toFixed(2); // 24% (12% employee + 12% employer)
            $('#pf').val(pf);
        }
        calculateTotals();
    }
    
    $('#auto_pf').on('change', function() {
        if ($(this).is(':checked')) {
            calculatePF();
        } else {
            $('#pf').val('{{ number_format($salary->pf, 2) }}');
            calculateTotals();
        }
    });
    
    $('#basic_salary').on('input', function() {
        if ($('#auto_pf').is(':checked')) {
            calculatePF();
        } else {
            calculateTotals();
        }
    });
    
    $('#hra, #conveyance, #special_allowance, #pt, #pf, #tds, #healthcare_cess').on('input', calculateTotals);
    
    // Recalculate basic salary when total working days change
    $('input[name="total_working_days"]').on('input', function() {
        const totalWorkingDays = parseFloat($(this).val()) || 1;
        const originalBasic = parseFloat($('input[name="original_basic"]').val()) || 0;
        const payableDays = parseFloat($('input[name="payable_days"]').val()) || 0;
        
        const perDayRate = originalBasic / totalWorkingDays;
        const newBasicSalary = perDayRate * payableDays;
        
        $('input[name="per_day_rate"]').val(perDayRate.toFixed(2));
        $('#basic_salary').val(newBasicSalary.toFixed(2));
        
        if ($('#auto_pf').is(':checked')) {
            calculatePF();
        } else {
            calculateTotals();
        }
    });
    
    // Recalculate when payable days change
    $('input[name="payable_days"]').on('input', function() {
        const totalWorkingDays = parseFloat($('input[name="total_working_days"]').val()) || 1;
        const originalBasic = parseFloat($('input[name="original_basic"]').val()) || 0;
        const payableDays = parseFloat($(this).val()) || 0;
        
        const perDayRate = originalBasic / totalWorkingDays;
        const newBasicSalary = perDayRate * payableDays;
        
        $('#basic_salary').val(newBasicSalary.toFixed(2));
        
        if ($('#auto_pf').is(':checked')) {
            calculatePF();
        } else {
            calculateTotals();
        }
    });
    
    // Initial calculation
    calculateTotals();
});

function generateReport() {
    $('#salaryReportForm').submit();
    
    Swal.fire({
        title: 'Report Generated!',
        text: 'Your salary report in PDF format is being downloaded.',
        icon: 'success',
        confirmButtonColor: '#ff6b35'
    });
}

function toggleDetails(type) {
    const details = document.getElementById(type + 'Details');
    if (details.style.display === 'none') {
        details.style.display = 'block';
    } else {
        details.style.display = 'none';
    }
}
</script>
@endpush
@endsection