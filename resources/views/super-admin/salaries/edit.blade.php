@extends('super-admin.layouts.app')

@section('title', 'Edit Salary')

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Salary</h1>
    <p class="page-subtitle">Update salary structure for {{ $salary->employee->name ?? $salary->emp_id }}</p>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Salary Details</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('super-admin.salaries.update', $salary) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label class="form-label">Employee</label>
                <div style="background: #f9fafb; padding: 12px 16px; border-radius: 8px; color: #6b7280;">
                    {{ $salary->emp_id }} - {{ $salary->employee->name ?? 'N/A' }}
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="basic_salary" class="form-label">Basic Salary</label>
                    <input type="number" name="basic_salary" id="basic_salary" class="form-control" 
                           value="{{ old('basic_salary', $salary->basic_salary) }}" step="0.01" min="0" required>
                    @error('basic_salary')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="hra" class="form-label">HRA (House Rent Allowance)</label>
                    <input type="number" name="hra" id="hra" class="form-control" 
                           value="{{ old('hra', $salary->hra) }}" step="0.01" min="0">
                    @error('hra')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="conveyance_allowance" class="form-label">Conveyance Allowance</label>
                    <input type="number" name="conveyance_allowance" id="conveyance_allowance" class="form-control" 
                           value="{{ old('conveyance_allowance', $salary->conveyance_allowance) }}" step="0.01" min="0">
                    @error('conveyance_allowance')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="pt" class="form-label">PT (Professional Tax)</label>
                    <input type="number" name="pt" id="pt" class="form-control" 
                           value="{{ old('pt', $salary->pt) }}" step="0.01" min="0">
                    @error('pt')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="pf" class="form-label">PF (Provident Fund)</label>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                        <input type="checkbox" id="auto_pf" name="auto_pf" {{ old('auto_pf', $salary->is_pf) ? 'checked' : '' }} style="transform: scale(1.2);">
                        <label for="auto_pf" style="margin: 0; font-size: 14px; color: #6b7280;">Auto calculate PF (12% of basic salary + 12% Company Contribution)</label>
                    </div>
                    <input type="number" name="pf" id="pf" class="form-control" 
                           value="{{ old('pf', $salary->pf) }}" step="0.01" min="0">
                    @error('pf')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Bank Details Section -->
            <div class="card" style="margin-top: 30px;">
                <div class="card-header">
                    <h4 class="card-title">Bank & Statutory Details</h4>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="bank_name" class="form-label">Bank Name</label>
                            <input type="text" name="bank_name" id="bank_name" class="form-control" 
                                   value="{{ old('bank_name', $salary->bank_name) }}" maxlength="255">
                            @error('bank_name')
                                <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="bank_account" class="form-label">Bank Account Number</label>
                            <input type="text" name="bank_account" id="bank_account" class="form-control" 
                                   value="{{ old('bank_account', $salary->bank_account) }}" maxlength="255">
                            @error('bank_account')
                                <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="ifsc_code" class="form-label">IFSC Code</label>
                            <input type="text" name="ifsc_code" id="ifsc_code" class="form-control" 
                                   value="{{ old('ifsc_code', $salary->ifsc_code) }}" maxlength="11" style="text-transform: uppercase;">
                            @error('ifsc_code')
                                <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="bank_branch" class="form-label">Bank Branch</label>
                            <input type="text" name="bank_branch" id="bank_branch" class="form-control" 
                                   value="{{ old('bank_branch', $salary->bank_branch) }}" maxlength="255">
                            @error('bank_branch')
                                <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="uan" class="form-label">UAN (Universal Account Number)</label>
                            <input type="text" name="uan" id="uan" class="form-control" 
                                   value="{{ old('uan', $salary->uan) }}" maxlength="12">
                            @error('uan')
                                <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="pf_no" class="form-label">PF Number</label>
                            <input type="text" name="pf_no" id="pf_no" class="form-control" 
                                   value="{{ old('pf_no', $salary->pf_no) }}" maxlength="255">
                            @error('pf_no')
                                <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="esic_no" class="form-label">ESIC Number</label>
                            <input type="text" name="esic_no" id="esic_no" class="form-control" 
                                   value="{{ old('esic_no', $salary->esic_no) }}" maxlength="17">
                            @error('esic_no')
                                <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="payment_mode" class="form-label">Payment Mode</label>
                            <select name="payment_mode" id="payment_mode" class="form-control" required>
                                <option value="bank_transfer" {{ old('payment_mode', $salary->payment_mode ?? 'bank_transfer') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="cash" {{ old('payment_mode', $salary->payment_mode) == 'cash' ? 'selected' : '' }}>Cash</option>
                            </select>
                            @error('payment_mode')
                                <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="effective_from" class="form-label">Effective From</label>
                <input type="date" name="effective_from" id="effective_from" class="form-control" 
                       value="{{ old('effective_from', $salary->effective_from->format('Y-m-d')) }}" required>
                @error('effective_from')
                    <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; gap: 12px; margin-top: 32px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Salary
                </button>
                <a href="{{ route('super-admin.salaries') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    function calculatePF() {
        if ($('#auto_pf').is(':checked')) {
            const basicSalary = parseFloat($('#basic_salary').val()) || 0;
            const pf = (basicSalary * 0.12).toFixed(2);
            $('#pf').val(pf*2);
        }
    }
    
    function calculateGrossSalary() {
        const basic = parseFloat($('#basic_salary').val()) || 0;
        const hra = parseFloat($('#hra').val()) || 0;
        const ta = parseFloat($('#ta').val()) || 0;
        const conveyance = parseFloat($('#conveyance_allowance').val()) || 0;
        const pf = parseFloat($('#pf').val()) || 0;
        const pt = parseFloat($('#pt').val()) || 0;
        
        const gross = basic + hra + ta + conveyance - pf - pt;
        
        if ($('#gross_display').length === 0) {
            $('form').append('<div id="gross_display" style="background: #f0fdf4; padding: 16px; border-radius: 8px; margin-top: 20px; text-align: center;"><strong>Gross Salary: ₹<span id="gross_amount">'+gross+'</span></strong></div>');
        }
        
        $('#gross_amount').text(gross.toFixed(2));
    }
    
    $('#auto_pf').on('change', function() {
        if ($(this).is(':checked')) {
            calculatePF();
        } else {
            $('#pf').val(0);
        }
        calculateGrossSalary();
    });
    
    $('#basic_salary').on('input', function() {
        calculatePF();
        calculateGrossSalary();
    });
    
    $('#hra, #ta, #conveyance_allowance, #pf, #pt').on('input', calculateGrossSalary);
    calculateGrossSalary();
});
</script>
@endpush
@endsection