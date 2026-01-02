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
                <div style="position: relative;">
                    <input type="text" id="employee_display" class="form-control" placeholder="Click to select employee" readonly 
                           style="cursor: pointer; background: #f8f9fa;" onclick="openEmployeeModal()">
                    <input type="hidden" name="emp_id" id="emp_id" required>
                    <div style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #6b7280;">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
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
let currentPage = 1;
let searchTimeout;

// Modal Functions
function openEmployeeModal() {
    document.getElementById('employeeModal').style.display = 'block';
    loadEmployees();
}

function closeEmployeeModal() {
    document.getElementById('employeeModal').style.display = 'none';
}

function selectEmployee(empId, empName) {
    document.getElementById('emp_id').value = empId;
    document.getElementById('employee_display').value = empId + ' - ' + empName;
    closeEmployeeModal();
}

function loadEmployees(page = 1) {
    const search = document.getElementById('modal_search').value;
    const perPage = document.getElementById('modal_per_page').value;
    
    fetch(`{{ route('super-admin.salaries.pending-employees') }}?page=${page}&search=${search}&per_page=${perPage}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        displayEmployees(data.employees);
        displayPagination(data.pagination);
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('employee_list').innerHTML = '<div style="text-align: center; padding: 40px; color: #ef4444;"><p>Error loading employees</p></div>';
    });
}

function displayEmployees(employees) {
    const container = document.getElementById('employee_list');
    
    if (employees.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 40px; color: #6b7280;"><i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i><h3>No employees found</h3><p>No employees without salary found matching your search</p></div>';
        return;
    }
    
    let html = '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px;">';
    
    employees.forEach(employee => {
        html += `
            <div onclick="selectEmployee('${employee.emp_id}', '${employee.username}')" 
                 style="padding: 15px; border: 1px solid #e5e7eb; border-radius: 8px; cursor: pointer; transition: all 0.2s; background: white;"
                 onmouseover="this.style.background='#f3f4f6'; this.style.borderColor='#ff6b35';"
                 onmouseout="this.style.background='white'; this.style.borderColor='#e5e7eb';">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; background: #ff6b35; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                        ${employee.username.charAt(0).toUpperCase()}
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 16px;">${employee.username}</div>
                        <div style="color: #6b7280; font-size: 14px;">${employee.emp_id}</div>
                        <div style="color: #6b7280; font-size: 12px;">${employee.department ? employee.department.name : 'No Department'}</div>
                    </div>
                    <div style="color: #10b981;">
                        <i class="fas fa-plus-circle" style="font-size: 20px;"></i>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function displayPagination(pagination) {
    const container = document.getElementById('modal_pagination');
    
    if (pagination.last_page <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<div style="display: flex; justify-content: center; align-items: center; gap: 8px;">';
    
    // Previous button
    if (pagination.current_page > 1) {
        html += `<button onclick="loadEmployees(${pagination.current_page - 1})" style="padding: 8px 12px; border: 1px solid #d1d5db; background: white; border-radius: 6px; cursor: pointer;">Previous</button>`;
    }
    
    // Page numbers
    for (let i = Math.max(1, pagination.current_page - 2); i <= Math.min(pagination.last_page, pagination.current_page + 2); i++) {
        const isActive = i === pagination.current_page;
        html += `<button onclick="loadEmployees(${i})" style="padding: 8px 12px; border: 1px solid ${isActive ? '#ff6b35' : '#d1d5db'}; background: ${isActive ? '#ff6b35' : 'white'}; color: ${isActive ? 'white' : '#374151'}; border-radius: 6px; cursor: pointer;">${i}</button>`;
    }
    
    // Next button
    if (pagination.current_page < pagination.last_page) {
        html += `<button onclick="loadEmployees(${pagination.current_page + 1})" style="padding: 8px 12px; border: 1px solid #d1d5db; background: white; border-radius: 6px; cursor: pointer;">Next</button>`;
    }
    
    html += '</div>';
    html += `<div style="margin-top: 10px; color: #6b7280; font-size: 14px;">Showing ${pagination.current_page} of ${pagination.last_page} pages (${pagination.total} total employees)</div>`;
    
    container.innerHTML = html;
}

function clearModalSearch() {
    document.getElementById('modal_search').value = '';
    loadEmployees(1);
}

// Search with debounce
document.getElementById('modal_search').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadEmployees(1);
    }, 300);
});

// Per page change
document.getElementById('modal_per_page').addEventListener('change', function() {
    loadEmployees(1);
});

// Close modal on outside click
document.getElementById('employeeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEmployeeModal();
    }
});

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
        const conveyance = parseFloat($('#conveyance_allowance').val()) || 0;
        const pf = parseFloat($('#pf').val()) || 0;
        const pt = parseFloat($('#pt').val()) || 0;
        
        const gross = basic + hra + conveyance - pf - pt;
        
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
    
    $('#hra, #conveyance_allowance, #pf, #pt').on('input', calculateGrossSalary);
    calculateGrossSalary();
});
</script>
@endpush
@endsection

<!-- Employee Selection Modal -->
<div id="employeeModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: white; overflow-y: auto;">
        <div style="padding: 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; background: #f9fafb;">
            <div>
                <h2 style="margin: 0; font-size: 24px; font-weight: 600;">Select Employee</h2>
                <p style="margin: 5px 0 0 0; color: #6b7280;">Choose an employee to create salary structure</p>
            </div>
            <button onclick="closeEmployeeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Search and Controls -->
        <div style="padding: 20px; border-bottom: 1px solid #e5e7eb; background: #f9fafb;">
            <div style="display: flex; gap: 15px; align-items: center;">
                <div style="flex: 1;">
                    <input type="text" id="modal_search" placeholder="Search by Employee ID or Name" 
                           style="width: 100%; padding: 10px 15px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 16px;">
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <label style="font-size: 14px; color: #6b7280;">Per Page:</label>
                    <select id="modal_per_page" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
                <button onclick="clearModalSearch()" style="padding: 10px 15px; background: #6b7280; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>
        </div>
        
        <!-- Employee List -->
        <div id="employee_list" style="padding: 20px; min-height: 400px;">
            <div style="text-align: center; padding: 40px; color: #6b7280;">
                <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i>
                <p style="margin-top: 10px;">Loading employees...</p>
            </div>
        </div>
        
        <!-- Pagination -->
        <div id="modal_pagination" style="padding: 20px; border-top: 1px solid #e5e7eb; background: #f9fafb; text-align: center;">
        </div>
    </div>
</div>