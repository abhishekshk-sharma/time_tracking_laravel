@extends('super-admin.layouts.app')

@section('title', 'Branch Management')

@section('content')
<div class="page-header">
    <h1 class="page-title">Branch Management</h1>
    <p class="page-subtitle">Manage office locations and Branch</p>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add New Branch</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('super-admin.regions.store') }}">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Branch Name</label>
                    <input type="text" name="name" class="form-control" required placeholder='Branch Name'> 
                </div>
                <div class="form-group">
                    <label class="form-label">Pin Code</label>
                    <input type="text" name="pin_code" class="form-control" required placeholder='Pin Code'>
                </div>
                <div class="form-group">
                    <label class="form-label">IP Address</label>
                    <input type="text" name="ip_address" class="form-control" placeholder='IP Address'>
                </div>
                <div class="form-group">
                    <label class="form-label">Latitude</label>
                    <input type="number" name="latitude" class="form-control" step="0.00000001" placeholder='Latitude'>
                </div>
                <div class="form-group">
                    <label class="form-label">Longitude</label>
                    <input type="number" name="longitude" class="form-control" step="0.00000001" placeholder='Longitude'>
                </div>
                <div class="form-group" style="display: flex; align-items: end;">
                    <button type="submit" class="btn btn-primary">Add Branch</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card" style="margin-top: 30px;">
    <div class="card-header">
        <h3 class="card-title">Existing Branch</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Branch</th>
                        <th>Pin Code</th>
                        <th>IP Address</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Employees</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($regions as $region)
                    <tr>
                        <td>{{ $region->id }}</td>
                        <td>{{ $region->name }}</td>
                        <td>{{ $region->pin_code }}</td>
                        <td>{{ $region->ip_address }}</td>
                        <td>{{ $region->latitude ?? 'N/A' }}</td>
                        <td>{{ $region->longitude ?? 'N/A' }}</td>
                        <td>{{ $region->employees_count }}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editRegion({{ $region->id }}, '{{ $region->name }}', '{{ $region->pin_code }}', '{{ $region->ip_address }}', '{{ $region->latitude }}', '{{ $region->longitude }}')">Edit</button>
                            <form method="POST" action="{{ route('super-admin.regions.destroy', $region->id) }}" style="display: inline; margin-left: 8px;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" style="display: none; swal-high-z-index;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Branch Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required placeholder='Enter Branch Name'>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pin Code</label>
                        <input type="text" name="pin_code" id="edit_pin_code" class="form-control" required placeholder='Enter Pin Code'>
                    </div>
                    <div class="form-group">
                        <label class="form-label">IP Address</label>
                        <input type="text" name="ip_address" id="edit_ip_address" class="form-control" placeholder='Enter IP Address'>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Latitude</label>
                        <input type="number" name="latitude" id="edit_latitude" class="form-control" step="0.00000001" placeholder='Enter Latitude'>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Longitude</label>
                        <input type="number" name="longitude" id="edit_longitude" class="form-control" step="0.00000001" placeholder='Enter Longitude'>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Branch</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function editRegion(id, name, pinCode, ipAddress, latitude, longitude) {
    document.getElementById('editForm').action = `/super-admin/regions/${id}`;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_pin_code').value = pinCode;
    document.getElementById('edit_ip_address').value = ipAddress;
    document.getElementById('edit_latitude').value = latitude === 'null' ? '' : latitude;
    document.getElementById('edit_longitude').value = longitude === 'null' ? '' : longitude;
    
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
<style>
.swal-high-z-index {
    z-index: 10000 !important;
}
.swal2-container.swal-high-z-index {
    z-index: 10000 !important;
}
</style>
@endpush
@endsection