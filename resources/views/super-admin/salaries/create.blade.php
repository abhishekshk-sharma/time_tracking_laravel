@extends('super-admin.layouts.app')

@section('title', 'Add New Salary')

@section('content')
<div class="page-header">
    <h1 class="page-title">Add New Salary</h1>
    <p class="page-subtitle">Create salary structure for an employee</p>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Salary Details</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('super-admin.salaries.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="emp_id" class="form-label">Employee</label>
                <select name="emp_id" id="emp_id" class="form-control" required>
                    <option value="">Select Employee</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->emp_id }}" {{ (old('emp_id') == $employee->emp_id || request('emp_id') == $employee->emp_id) ? 'selected' : '' }}>
                            {{ $employee->emp_id }} - {{ $employee->name }}
                        </option>
                    @endforeach
                </select>
                @error('emp_id')
                    <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="basic_salary" class="form-label">Basic Salary</label>
                    <input type="number" name="basic_salary" id="basic_salary" class="form-control" 
                           value="{{ old('basic_salary') }}" step="0.01" min="0" required>
                    @error('basic_salary')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="hra" class="form-label">HRA (House Rent Allowance)</label>
                    <input type="number" name="hra" id="hra" class="form-control" 
                           value="{{ old('hra', 0) }}" step="0.01" min="0">
                    @error('hra')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="ta" class="form-label">TA (Travel Allowance)</label>
                    <input type="number" name="ta" id="ta" class="form-control" 
                           value="{{ old('ta', 0) }}" step="0.01" min="0">
                    @error('ta')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="conveyance_allowance" class="form-label">Conveyance Allowance</label>
                    <input type="number" name="conveyance_allowance" id="conveyance_allowance" class="form-control" 
                           value="{{ old('conveyance_allowance', 0) }}" step="0.01" min="0">
                    @error('conveyance_allowance')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="pf" class="form-label">PF (Provident Fund)</label>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                        <input type="checkbox" id="auto_pf" name="auto_pf" style="transform: scale(1.2);">
                        <label for="auto_pf" style="margin: 0; font-size: 14px; color: #6b7280;">Auto calculate PF (12% of basic salary)</label>
                    </div>
                    <input type="number" name="pf" id="pf" class="form-control" 
                           value="{{ old('pf', 0) }}" step="0.01" min="0">
                    @error('pf')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="pt" class="form-label">PT (Professional Tax)</label>
                    <input type="number" name="pt" id="pt" class="form-control" 
                           value="{{ old('pt', 0) }}" step="0.01" min="0">
                    @error('pt')
                        <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="effective_from" class="form-label">Effective From</label>
                <input type="date" name="effective_from" id="effective_from" class="form-control" 
                       value="{{ old('effective_from', date('Y-m-d')) }}" required>
                @error('effective_from')
                    <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; gap: 12px; margin-top: 32px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Salary
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
            $('#pf').val(pf);
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
            $('form').append('<div id="gross_display" style="background: #f0fdf4; padding: 16px; border-radius: 8px; margin-top: 20px; text-align: center;"><strong>Gross Salary: ₹<span id="gross_amount">0.00</span></strong></div>');
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