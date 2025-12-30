@extends('admin.layouts.app')

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
<script>
function showAddDepartmentModal() {
    Swal.fire({
        title: 'Add Department',
        html: `
            <div style="text-align: left;">
                <div class="form-group">
                    <label class="form-label">Department Name</label>
                    <input type="text" id="dept_name" class="form-control" placeholder="Enter department name">
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea id="dept_description" class="form-control" rows="3" placeholder="Enter description (optional)"></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Add Department',
        confirmButtonColor: '#ff9900',
        preConfirm: () => {
            const name = document.getElementById('dept_name').value;
            if (!name) {
                Swal.showValidationMessage('Department name is required');
                return false;
            }
            return {
                name: name,
                description: document.getElementById('dept_description').value
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('/admin/departments', {
                ...result.value,
                _token: $('meta[name="csrf-token"]').attr('content')
            }).done(() => {
                Swal.fire('Success!', 'Department added successfully.', 'success').then(() => {
                    location.reload();
                });
            }).fail(() => {
                Swal.fire('Error!', 'Failed to add department.', 'error');
            });
        }
    });
}

function editDepartment(id) {
    Swal.fire('Info', 'Edit functionality will be implemented', 'info');
}

function deleteDepartment(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will delete the department permanently.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d13212',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/departments/${id}`,
                method: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') }
            }).done(() => {
                Swal.fire('Deleted!', 'Department has been deleted.', 'success').then(() => {
                    location.reload();
                });
            }).fail(() => {
                Swal.fire('Error!', 'Failed to delete department.', 'error');
            });
        }
    });
}
</script>
@endpush