@extends('super-admin.layouts.app')

@section('title', 'Tax & Payroll Settings')

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Tax & Payroll Settings</h1>
            <p class="page-subtitle">Manage tax slabs and payroll configuration</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <button class="btn btn-secondary" id="taxSlabsTab" onclick="showSection('taxSlabs')">
                <i class="fas fa-percentage"></i> Tax Slabs
            </button>
            <button class="btn btn-secondary" id="payrollTab" onclick="showSection('payroll')">
                <i class="fas fa-cog"></i> Payroll Settings
            </button>
        </div>
    </div>
</div>

<!-- Tax Slabs Section -->
<div id="taxSlabsSection">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Income Tax Slabs</h3>
        <button class="btn btn-primary" onclick="showAddTaxSlabModal()">
            <i class="fas fa-plus"></i> Add Tax Slab
        </button>
    </div>
    
    <div class="card">
        <div class="card-body" style="padding: 0;">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Income From</th>
                            <th>Income To</th>
                            <th>Tax Rate (%)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($taxSlabs as $slab)
                        <tr>
                            <td>₹{{ number_format($slab->income_from) }}</td>
                            <td>{{ $slab->income_to ? '₹' . number_format($slab->income_to) : 'Above' }}</td>
                            <td>{{ $slab->tax_rate }}%</td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <button class="btn btn-sm btn-secondary" onclick="editTaxSlab({{ $slab->id }})" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteTaxSlab({{ $slab->id }})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Payroll Settings Section -->
<div id="payrollSection" style="display: none;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Payroll Configuration</h3>
        <button class="btn btn-primary" onclick="savePayrollSettings()">
            <i class="fas fa-save"></i> Save Settings
        </button>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form id="payrollForm">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">Health & Education Cess</label>
                        <input type="text" name="health_&_education_cess" class="form-control" 
                               value="{{ $payrollSettings['health_&_education_cess'] ?? '4%' }}" placeholder="e.g., 4%">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Professional Tax (PT)</label>
                        <input type="number" name="pt" class="form-control" 
                               value="{{ $payrollSettings['pt'] ?? 200 }}" placeholder="200" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Senior Conveyance Allowance</label>
                        <input type="number" name="senior_ca" class="form-control" 
                               value="{{ $payrollSettings['senior_ca'] ?? 2000 }}" placeholder="2000" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Junior Conveyance Allowance</label>
                        <input type="number" name="junior_ca" class="form-control" 
                               value="{{ $payrollSettings['junior_ca'] ?? 1600 }}" placeholder="1600" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Metro HRA (%)</label>
                        <input type="number" name="metro_hra" class="form-control" 
                               value="{{ $payrollSettings['metro_hra'] ?? 50 }}" placeholder="50" min="0" max="100">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Default HRA (%)</label>
                        <input type="number" name="default_hra" class="form-control" 
                               value="{{ $payrollSettings['default_hra'] ?? 40 }}" placeholder="40" min="0" max="100">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Metro Basic Salary (%)</label>
                        <input type="number" name="metro_basic" class="form-control" 
                               value="{{ $payrollSettings['metro_basic'] ?? 50 }}" placeholder="50" min="0" max="100">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Default Basic Salary (%)</label>
                        <input type="number" name="default_basic" class="form-control" 
                               value="{{ $payrollSettings['default_basic'] ?? 50 }}" placeholder="50" min="0" max="100">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Standard Deduction</label>
                    <input type="number" name="standard_deduction" class="form-control" 
                           value="{{ $payrollSettings['standard_deduction'] ?? 50000 }}" placeholder="50000" min="0">
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showSection(section) {
    if (section === 'taxSlabs') {
        document.getElementById('taxSlabsSection').style.display = 'block';
        document.getElementById('payrollSection').style.display = 'none';
        document.getElementById('taxSlabsTab').className = 'btn btn-primary';
        document.getElementById('payrollTab').className = 'btn btn-secondary';
    } else {
        document.getElementById('taxSlabsSection').style.display = 'none';
        document.getElementById('payrollSection').style.display = 'block';
        document.getElementById('taxSlabsTab').className = 'btn btn-secondary';
        document.getElementById('payrollTab').className = 'btn btn-primary';
    }
}

function savePayrollSettings() {
    const formData = new FormData(document.getElementById('payrollForm'));
    const data = Object.fromEntries(formData);
    
    fetch('/super-admin/payroll-settings', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success!', data.message, 'success');
        } else {
            Swal.fire('Error!', data.message || 'Failed to update settings.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error!', 'Failed to update settings.', 'error');
    });
}

function showAddTaxSlabModal() {
    Swal.fire({
        title: 'Add Tax Slab',
        html: `
            <div style="text-align: left;">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">Income From</label>
                    <input type="number" id="income_from" class="form-control" placeholder="Enter minimum income" min="0">
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">Income To (Optional)</label>
                    <input type="number" id="income_to" class="form-control" placeholder="Enter maximum income (leave empty for above)">
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">Tax Rate (%)</label>
                    <input type="number" id="tax_rate" class="form-control" placeholder="Enter tax rate" step="0.01" min="0" max="100">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Add Tax Slab',
        cancelButtonText: 'Cancel',
        buttonsStyling: false,
        customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-secondary'
        },
        preConfirm: () => {
            const incomeFrom = parseFloat(document.getElementById('income_from').value);
            const incomeTo = document.getElementById('income_to').value ? parseFloat(document.getElementById('income_to').value) : null;
            const taxRate = parseFloat(document.getElementById('tax_rate').value);
            
            if (!incomeFrom || incomeFrom < 0) {
                Swal.showValidationMessage('Please enter a valid income from amount');
                return false;
            }
            if (!taxRate || taxRate < 0 || taxRate > 100) {
                Swal.showValidationMessage('Please enter a valid tax rate (0-100%)');
                return false;
            }
            if (incomeTo && incomeTo <= incomeFrom) {
                Swal.showValidationMessage('Income to must be greater than income from');
                return false;
            }
            
            return {
                income_from: incomeFrom,
                income_to: incomeTo,
                tax_rate: taxRate
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/super-admin/tax-slabs', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(result.value)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success!', 'Tax slab added successfully.', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', data.message || 'Failed to add tax slab.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'Failed to add tax slab.', 'error');
            });
        }
    });
}

function editTaxSlab(id) {
    fetch(`/super-admin/tax-slabs/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const slab = data.taxSlab;
                Swal.fire({
                    title: 'Edit Tax Slab',
                    html: `
                        <div style="text-align: left;">
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label class="form-label">Income From</label>
                                <input type="number" id="edit_income_from" class="form-control" value="${slab.income_from}" min="0">
                            </div>
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label class="form-label">Income To (Optional)</label>
                                <input type="number" id="edit_income_to" class="form-control" value="${slab.income_to || ''}" placeholder="Leave empty for above">
                            </div>
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label class="form-label">Tax Rate (%)</label>
                                <input type="number" id="edit_tax_rate" class="form-control" value="${slab.tax_rate}" step="0.01" min="0" max="100">
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Update Tax Slab',
                    cancelButtonText: 'Cancel',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-secondary'
                    },
                    preConfirm: () => {
                        const incomeFrom = parseFloat(document.getElementById('edit_income_from').value);
                        const incomeTo = document.getElementById('edit_income_to').value ? parseFloat(document.getElementById('edit_income_to').value) : null;
                        const taxRate = parseFloat(document.getElementById('edit_tax_rate').value);
                        
                        if (!incomeFrom || incomeFrom < 0) {
                            Swal.showValidationMessage('Please enter a valid income from amount');
                            return false;
                        }
                        if (!taxRate || taxRate < 0 || taxRate > 100) {
                            Swal.showValidationMessage('Please enter a valid tax rate (0-100%)');
                            return false;
                        }
                        if (incomeTo && incomeTo <= incomeFrom) {
                            Swal.showValidationMessage('Income to must be greater than income from');
                            return false;
                        }
                        
                        return {
                            income_from: incomeFrom,
                            income_to: incomeTo,
                            tax_rate: taxRate
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/super-admin/tax-slabs/${id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(result.value)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Success!', 'Tax slab updated successfully.', 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error!', data.message || 'Failed to update tax slab.', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error!', 'Failed to update tax slab.', 'error');
                        });
                    }
                });
            } else {
                Swal.fire('Error!', 'Failed to load tax slab data.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error!', 'Failed to load tax slab data.', 'error');
        });
}

function deleteTaxSlab(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will delete the tax slab permanently.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        buttonsStyling: false,
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-secondary'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/super-admin/tax-slabs/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', 'Tax slab has been deleted.', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', data.message || 'Failed to delete tax slab.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'Failed to delete tax slab.', 'error');
            });
        }
    });
}

// Initialize with Tax Slabs section
showSection('taxSlabs');
</script>
@endpush