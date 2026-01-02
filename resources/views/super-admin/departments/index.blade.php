@extends('super-admin.layouts.app')

@section('title', 'Departments')

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Departments</h1>
            <p class="page-subtitle">Manage company departments</p>
        </div>
        <button class="btn btn-primary" onclick="showAddDepartmentModal()">
            <i class="fas fa-plus"></i> Add Department
        </button>
    </div>
</div>

<!-- Departments Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Department List</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Employees</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($departments as $department)
                    <tr>
                        <td>
                            <div style="font-weight: 500;">{{ $department->name }}</div>
                        </td>
                        <td>{{ $department->description ?: '-' }}</td>
                        <td>
                            <span class="badge badge-secondary">{{ $department->employees_count ?? 0 }} employees</span>
                        </td>
                        <td>{{ $department->created_at instanceof \Carbon\Carbon ? $department->created_at->format('M d, Y') : $department->created_at }}</td>
                        <td>
                            <div style="display: flex; gap: 5px;">
                                <button class="btn btn-sm btn-secondary" onclick="editDepartment({{ $department->id }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteDepartment({{ $department->id }})" title="Delete">
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
@endsection

@push('scripts')
<style>
.swal2-html-container .form-group {
    margin-bottom: 20px;
    text-align: left;
}

.swal2-html-container .form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #374151;
    font-size: 14px;
}

.swal2-html-container .form-control {
    width: 100%;
    height: 42px;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    background: #ffffff;
    transition: all 0.15s ease;
    box-sizing: border-box;
}

.swal2-html-container textarea.form-control {
    height: auto;
    min-height: 80px;
    resize: vertical;
}

.swal2-html-container .form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
</style>
<script>
function showAddDepartmentModal() {
    Swal.fire({
        title: 'Add Department',
        html: `
            <div class="form-group">
                <label class="form-label">Department Name</label>
                <input type="text" id="dept_name" class="form-control" placeholder="Enter department name">
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea id="dept_description" class="form-control" rows="3" placeholder="Enter description (optional)"></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Add Department',
        cancelButtonText: 'Cancel',
        buttonsStyling: false,
        customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-secondary'
        },
        preConfirm: () => {
            const name = document.getElementById('dept_name').value.trim();
            if (!name) {
                Swal.showValidationMessage('Department name is required');
                return false;
            }
            return {
                name: name,
                description: document.getElementById('dept_description').value.trim()
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            console.log('Sending data:', result.value);
            
            fetch('/super-admin/departments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(result.value)
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    Swal.fire('Success!', data.message || 'Department added successfully.', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', data.message || 'Failed to add department.', 'error');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                Swal.fire('Error!', 'Network error occurred. Please try again.', 'error');
            });
        }
    });
}

function editDepartment(id) {
    fetch(`/super-admin/departments/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const dept = data.department;
                Swal.fire({
                    title: 'Edit Department',
                    html: `
                        <div class="form-group">
                            <label class="form-label">Department Name</label>
                            <input type="text" id="edit_dept_name" class="form-control" value="${dept.name}" placeholder="Enter department name">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea id="edit_dept_description" class="form-control" rows="3" placeholder="Enter description (optional)">${dept.description || ''}</textarea>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Update Department',
                    cancelButtonText: 'Cancel',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-secondary'
                    },
                    preConfirm: () => {
                        const name = document.getElementById('edit_dept_name').value.trim();
                        if (!name) {
                            Swal.showValidationMessage('Department name is required');
                            return false;
                        }
                        return {
                            name: name,
                            description: document.getElementById('edit_dept_description').value.trim()
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/super-admin/departments/${id}`, {
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
                                Swal.fire('Success!', 'Department updated successfully.', 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error!', data.message || 'Failed to update department.', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error!', 'Failed to update department.', 'error');
                        });
                    }
                });
            } else {
                Swal.fire('Error!', 'Failed to load department data.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error!', 'Failed to load department data.', 'error');
        });
}

function deleteDepartment(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will delete the department permanently.',
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
            fetch(`/super-admin/departments/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', 'Department has been deleted.', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', data.error || 'Failed to delete department.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'Failed to delete department.', 'error');
            });
        }
    });
}
</script>
@endpush