@extends('super-admin.layouts.app')

@section('title', 'Edit Salary Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #ff6b35 0%, #ff9900 100%); color: white;">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Edit Salary Report - {{ $salaryReport->emp_name }}
                        <small class="ms-2 opacity-75">({{ date('F Y', mktime(0, 0, 0, $salaryReport->month, 1, $salaryReport->year)) }})</small>
                    </h4>
                </div>
                <div class="card-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="position: relative;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Validation Error:</strong>
                            <ul class="mb-0 mt-2"   style="list-style-type: disc; list-style-position: inside;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" style="position: absolute; top: 50%; right: 10px;">X</button>
                        </div>
                    @endif
                    
                    <form action="{{ route('super-admin.salary-reports.update', $salaryReport->id) }}" method="POST" id="salaryForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-lg-8">
                                <!-- Employee Information Card -->
                                <div class="card shadow-sm border-0 mb-4">
                                    <div class="card-header bg-light border-0">
                                        <h6 class="card-title text-dark fw-bold mb-0">
                                            <i class="fas fa-user me-2 text-primary"></i>Employee Information
                                        </h6>
                                    </div>
                                    <div class="card-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                        <div>
                                            <label class="form-label fw-bold">Employee Name</label>
                                            <input type="text" class="form-control" name="emp_name" value="{{ old('emp_name', $salaryReport->emp_name) }}" required>
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold">Designation</label>
                                            <input type="text" class="form-control" name="designation" value="{{ old('designation', $salaryReport->designation) }}" required>
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold">Department</label>
                                            <input type="text" class="form-control" name="department" value="{{ old('department', $salaryReport->department) }}" required>
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold">Employee ID</label>
                                            <input type="text" class="form-control" value="{{ $salaryReport->emp_id }}" readonly>
                                        </div>
                                    </div>
                                </div>

                                <!-- Attendance Summary Card -->
                                <div class="card shadow-sm border-0 mb-4">
                                    <div class="card-header bg-light border-0">
                                        <h6 class="card-title text-dark fw-bold mb-0">
                                            <i class="fas fa-calendar-check me-2 text-success"></i>Attendance Summary
                                        </h6>
                                    </div>
                                    <div class="card-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                        <div>
                                            <label class="form-label fw-bold">Total Working Days</label>
                                            <input type="number" class="form-control" id="total_working_days" name="total_working_days" value="{{ old('total_working_days', $salaryReport->total_working_days) }}" required min="1">
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold">Present Days</label>
                                            <input type="number" class="form-control" name="present_days" value="{{ old('present_days', $salaryReport->present_days) }}" required min="0">
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold">Absent Days</label>
                                            <input type="number" class="form-control" name="absent_days" value="{{ old('absent_days', $salaryReport->absent_days) }}" required min="0">
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold">Half Days</label>
                                            <input type="number" class="form-control" name="half_days" value="{{ old('half_days', $salaryReport->half_days) }}" required min="0">
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold">Sick Leave</label>
                                            <input type="number" class="form-control" name="sick_leave" value="{{ old('sick_leave', $salaryReport->sick_leave) }}" required min="0">
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold">Casual Leave</label>
                                            <input type="number" class="form-control" name="casual_leave" value="{{ old('casual_leave', $salaryReport->casual_leave) }}" required min="0">
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold">Regularization</label>
                                            <input type="number" class="form-control" name="regularization" value="{{ old('regularization', $salaryReport->regularization) }}" required min="0">
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold">Holidays</label>
                                            <input type="number" class="form-control" name="holidays" value="{{ old('holidays', $salaryReport->holidays) }}" required min="0">
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold">Short Attendance</label>
                                            <input type="number" class="form-control" name="short_attendance" value="{{ old('short_attendance', $salaryReport->short_attendance) }}" required min="0">
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold text-primary">Payable Days</label>
                                            <input type="number" step="0.5" class="form-control border-primary" id="payable_days" name="payable_days" value="{{ old('payable_days', $salaryReport->payable_days) }}" required min="0">
                                        </div>
                                    </div>
                                </div>

                                <!-- Salary Details Card -->
                                <div class="card shadow-sm border-0 mb-4">
                                    <div class="card-header bg-light border-0">
                                        <h6 class="card-title text-dark fw-bold mb-0">
                                            <i class="fas fa-money-bill-wave me-2 text-warning"></i>Salary & Allowances
                                        </h6>
                                    </div>
                                    <div class="card-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                        <div>
                                            <label class="form-label fw-bold">Basic Salary</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" step="0.01" class="form-control" id="basic_salary" name="basic_salary" value="{{ old('basic_salary', $salaryReport->basic_salary) }}" required min="0">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold">HRA</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" step="0.01" class="form-control" id="hra" name="hra" value="{{ old('hra', $salaryReport->hra) }}" required min="0">
                                            </div>
                                        </div>
                                        <div style="grid-column: 1 / -1;">
                                            <label class="form-label fw-bold">Conveyance Allowance</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" step="0.01" class="form-control" id="conveyance_allowance" name="conveyance_allowance" value="{{ old('conveyance_allowance', $salaryReport->conveyance_allowance) }}" required min="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Deductions Card -->
                                <div class="card shadow-sm border-0 mb-4">
                                    <div class="card-header bg-light border-0">
                                        <h6 class="card-title text-dark fw-bold mb-0">
                                            <i class="fas fa-minus-circle me-2 text-danger"></i>Deductions
                                        </h6>
                                    </div>
                                    <div class="card-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                        <div>
                                            <label class="form-label fw-bold">Provident Fund (PF)</label>
                                            <div class="input-group">
                                                <span class="input-group-text text-danger">₹</span>
                                                <input type="number" step="0.01" class="form-control" id="pf" name="pf" value="{{ old('pf', $salaryReport->pf) }}" required min="0">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold">Professional Tax (PT)</label>
                                            <div class="input-group">
                                                <span class="input-group-text text-danger">₹</span>
                                                <input type="number" step="0.01" class="form-control" id="pt" name="pt" value="{{ old('pt', $salaryReport->pt) }}" required min="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bank & Statutory Details Card -->
                                <div class="card shadow-sm border-0 mb-4">
                                    <div class="card-header bg-light border-0">
                                        <h6 class="card-title text-dark fw-bold mb-0">
                                            <i class="fas fa-university me-2 text-info"></i>Bank & Statutory Details
                                        </h6>
                                    </div>
                                    <div class="card-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                        <div>
                                            <label class="form-label fw-bold">Bank Name</label>
                                            <input type="text" class="form-control" name="bank_name" value="{{ old('bank_name', $salaryReport->bank_name) }}" maxlength="255">
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold">Bank Account</label>
                                            <input type="text" class="form-control" name="bank_account" value="{{ old('bank_account', $salaryReport->bank_account) }}" maxlength="255">
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold">IFSC Code</label>
                                            <input type="text" class="form-control" name="ifsc_code" value="{{ old('ifsc_code', $salaryReport->ifsc_code) }}" maxlength="11">
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold">Bank Branch</label>
                                            <input type="text" class="form-control" name="bank_branch" value="{{ old('bank_branch', $salaryReport->bank_branch) }}" maxlength="255">
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold">UAN</label>
                                            <input type="text" class="form-control" name="uan" value="{{ old('uan', $salaryReport->uan) }}" maxlength="12">
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold">PF Number</label>
                                            <input type="text" class="form-control" name="pf_no" value="{{ old('pf_no', $salaryReport->pf_no) }}" maxlength="255">
                                        </div>
                                        <div style="grid-column: 1 / -1;">
                                            <label class="form-label fw-bold">ESIC Number</label>
                                            <input type="text" class="form-control" name="esic_no" value="{{ old('esic_no', $salaryReport->esic_no) }}" maxlength="17">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Calculation Summary Sidebar -->
                            <div class="col-lg-4">
                                <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                                    <div class="card-header bg-light border-0">
                                        <h6 class="card-title text-dark fw-bold mb-0">
                                            <i class="fas fa-calculator me-2 text-primary"></i>Salary Calculation
                                        </h6>
                                    </div>
                                    <div class="card-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                        <div>
                                            <label class="form-label small text-muted">Payable Basic</label>
                                            <input type="text" class="form-control form-control-sm text-success fw-bold" id="payable_basic_display" value="₹0.00" readonly>
                                        </div>
                                        <div>
                                            <label class="form-label small text-muted">HRA</label>
                                            <input type="text" class="form-control form-control-sm text-success fw-bold" id="hra_display" value="₹0.00" readonly>
                                        </div>
                                        <div>
                                            <label class="form-label small text-muted">Conveyance</label>
                                            <input type="text" class="form-control form-control-sm text-success fw-bold" id="conveyance_display" value="₹0.00" readonly>
                                        </div>
                                        <div>
                                            <label class="form-label small text-success fw-bold">Gross Salary</label>
                                            <input type="text" class="form-control form-control-sm text-success fw-bold border-success" id="gross_salary_display" value="₹0.00" readonly>
                                        </div>
                                        <div>
                                            <label class="form-label small text-muted">PF</label>
                                            <input type="text" class="form-control form-control-sm text-danger fw-bold" id="pf_display" value="₹0.00" readonly>
                                        </div>
                                        <div>
                                            <label class="form-label small text-muted">PT</label>
                                            <input type="text" class="form-control form-control-sm text-danger fw-bold" id="pt_display" value="₹0.00" readonly>
                                        </div>
                                        <div style="grid-column: 1 / -1;">
                                            <label class="form-label small text-danger fw-bold">Total Deductions</label>
                                            <input type="text" class="form-control form-control-sm text-danger fw-bold border-danger" id="total_deductions_display" value="₹0.00" readonly>
                                        </div>
                                        <div style="grid-column: 1 / -1;">
                                            <div class="bg-primary bg-opacity-10 border border-primary rounded p-3 text-center mt-2">
                                                <label class="form-label small text-primary fw-bold mb-2">NET PAYABLE SALARY</label>
                                                <input type="text" class="form-control form-control-lg text-center text-primary fw-bold border-primary" id="net_salary_display" value="₹0.00" readonly style="font-size: 1.25rem;">
                                            </div>
                                        </div>
                                        <div style="grid-column: 1 / -1;" class="mt-3">
                                            <div class="d-grid gap-2">
                                                <button type="submit" class="btn btn-success btn-lg">
                                                    <i class="fas fa-save me-2"></i>Update Report
                                                </button>
                                                <a href="{{ route('super-admin.reports') }}" class="btn btn-secondary">
                                                    <i class="fas fa-arrow-left me-2"></i>Back to Reports
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const totalWorkingDays = parseFloat(document.getElementById('total_working_days').value) || 1;
    
    function updateCalculations() {
        const basicSalary = parseFloat(document.getElementById('basic_salary').value) || 0;
        const presentDays = parseFloat(document.querySelector('input[name="present_days"]').value) || 0;
        const absentDays = parseFloat(document.querySelector('input[name="absent_days"]').value) || 0;
        const halfDays = parseFloat(document.querySelector('input[name="half_days"]').value) || 0;
        const sickLeave = parseFloat(document.querySelector('input[name="sick_leave"]').value) || 0;
        const casualLeave = parseFloat(document.querySelector('input[name="casual_leave"]').value) || 0;
        const regularization = parseFloat(document.querySelector('input[name="regularization"]').value) || 0;
        const holidays = parseFloat(document.querySelector('input[name="holidays"]').value) || 0;
        const shortAttendance = parseFloat(document.querySelector('input[name="short_attendance"]').value) || 0;
        const hra = parseFloat(document.getElementById('hra').value) || 0;
        const conveyanceAllowance = parseFloat(document.getElementById('conveyance_allowance').value) || 0;
        const pf = parseFloat(document.getElementById('pf').value) || 0;
        const pt = parseFloat(document.getElementById('pt').value) || 0;

        // Calculate payable days
        const payableDays = presentDays + holidays + sickLeave + casualLeave + regularization + (halfDays * 0.5) + (shortAttendance * 0.5);
        document.getElementById('payable_days').value = payableDays.toFixed(1);
        
        // Calculate payable basic salary
        const payableBasicSalary = (basicSalary / totalWorkingDays) * payableDays;
        const grossSalary = payableBasicSalary + hra + conveyanceAllowance;
        const totalDeductions = pf + pt;
        const netSalary = grossSalary - totalDeductions;

        const formatCurrency = (amount) => '₹' + amount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        document.getElementById('payable_basic_display').value = formatCurrency(payableBasicSalary);
        document.getElementById('hra_display').value = formatCurrency(hra);
        document.getElementById('conveyance_display').value = formatCurrency(conveyanceAllowance);
        document.getElementById('gross_salary_display').value = formatCurrency(grossSalary);
        document.getElementById('pf_display').value = formatCurrency(pf);
        document.getElementById('pt_display').value = formatCurrency(pt);
        document.getElementById('total_deductions_display').value = formatCurrency(totalDeductions);
        document.getElementById('net_salary_display').value = formatCurrency(netSalary);
    }

    function adjustAttendance(changedField, oldValue, newValue) {
        const presentField = document.querySelector('input[name="present_days"]');
        const absentField = document.querySelector('input[name="absent_days"]');
        const totalWorkingDaysValue = parseFloat(document.getElementById('total_working_days').value) || 1;
        const difference = newValue - oldValue;
        
        if (changedField === 'present_days') {
            // Calculate current payable days without present days
            const halfDays = parseFloat(document.querySelector('input[name="half_days"]').value) || 0;
            const sickLeave = parseFloat(document.querySelector('input[name="sick_leave"]').value) || 0;
            const casualLeave = parseFloat(document.querySelector('input[name="casual_leave"]').value) || 0;
            const regularization = parseFloat(document.querySelector('input[name="regularization"]').value) || 0;
            const holidays = parseFloat(document.querySelector('input[name="holidays"]').value) || 0;
            const shortAttendance = parseFloat(document.querySelector('input[name="short_attendance"]').value) || 0;
            
            const otherPayableDays = holidays + sickLeave + casualLeave + regularization + (halfDays * 0.5) + (shortAttendance * 0.5);
            const maxPresentDays = totalWorkingDaysValue - otherPayableDays;
            
            // Limit present days to not exceed month days
            if (newValue > maxPresentDays) {
                presentField.value = Math.max(0, maxPresentDays);
                return;
            }
        } else if (changedField === 'absent_days') {
            // Adjust present days when absent days change
            const currentPresent = parseFloat(presentField.value) || 0;
            presentField.value = Math.max(0, currentPresent - difference);
        } else if (['short_attendance', 'sick_leave', 'casual_leave', 'regularization', 'holidays'].includes(changedField)) {
            // Adjust present days when other fields change
            const currentPresent = parseFloat(presentField.value) || 0;
            presentField.value = Math.max(0, currentPresent - difference);
        }
    }

    // Store original values for comparison
    const originalValues = {};
    const attendanceFields = ['present_days', 'absent_days', 'half_days', 'sick_leave', 'casual_leave', 'regularization', 'holidays', 'short_attendance'];
    
    attendanceFields.forEach(fieldName => {
        const element = document.querySelector(`input[name="${fieldName}"]`);
        if (element) {
            originalValues[fieldName] = parseFloat(element.value) || 0;
            
            element.addEventListener('input', function() {
                const newValue = parseFloat(this.value) || 0;
                const oldValue = originalValues[fieldName];
                
                // Prevent negative values
                if (newValue < 0) {
                    this.value = 0;
                    return;
                }
                
                adjustAttendance(fieldName, oldValue, newValue);
                originalValues[fieldName] = parseFloat(this.value) || 0;
                
                updateCalculations();
            });
        }
    });

    // Add event listeners to salary fields
    ['basic_salary', 'hra', 'conveyance_allowance', 'pf', 'pt'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', updateCalculations);
        }
    });

    // Initial calculation
    updateCalculations();
});
</script>
@endsection