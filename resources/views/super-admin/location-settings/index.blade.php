@extends('super-admin.layouts.app')

@section('title', 'Location Settings')

@section('content')
<div class="page-header">
    <h1 class="page-title">Location Settings</h1>
    <p class="page-subtitle">Manage admin location settings for tracking purposes</p>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Update Location Settings</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('super-admin.location-settings.update') }}" method="POST" id="location-form">
            @csrf
            
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="form-label">Select Admin</label>
                        <select name="emp_id" class="form-control" required>
                            <option value="">Choose Admin</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->emp_id }}">{{ $admin->full_name }} ({{ $admin->emp_id }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="form-label">IP Address</label>
                        <input type="text" 
                               name="ip_address" 
                               class="form-control" 
                               placeholder="192.168.1.1">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="form-label">Latitude</label>
                        <input type="number" 
                               name="latitude" 
                               class="form-control" 
                               step="0.00000001"
                               placeholder="40.7128">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="form-label">Longitude</label>
                        <input type="number" 
                               name="longitude" 
                               class="form-control" 
                               step="0.00000001"
                               placeholder="-74.0060">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="form-label">Range (meters)</label>
                        <input type="number" 
                               name="range" 
                               class="form-control" 
                               min="1"
                               placeholder="100">
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Location Settings
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">Admin Location Settings</h3>
    </div>
    <div class="card-body">
        <!-- Filter Section -->
        <div class="filter-section mb-4">
            <form method="GET" class="filter-form">
                <div class="filter-grid">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-search me-2"></i>Search Admin</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by username or employee ID" value="{{ request('search') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-list me-2"></i>Per Page</label>
                        <select name="per_page" class="form-control">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('super-admin.location-settings.index') }}" class="btn btn-secondary">
                        <i class="fas fa-refresh"></i> Reset
                    </a>
                </div>
            </form>
        </div>
        
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Admin Name</th>
                        <th>Employee ID</th>
                        <th>IP Address</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Range</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $admin)
                    <tr>
                        <td>{{ $admin->full_name }}</td>
                        <td>{{ $admin->emp_id }}</td>
                        <td>{{ $locations[$admin->emp_id]->ip_address ?? 'Not set' }}</td>
                        <td>{{ $locations[$admin->emp_id]->latitude ?? 'Not set' }}</td>
                        <td>{{ $locations[$admin->emp_id]->longitude ?? 'Not set' }}</td>
                        <td>{{ $locations[$admin->emp_id]->range ?? 100 }} m</td>
                        <td>
                            <button type="button" class="btn btn-primary btn-sm edit-btn" data-emp-id="{{ $admin->emp_id }}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-users fa-2x mb-2"></i><br>
                            No admins found matching your criteria
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                Showing {{ $admins->firstItem() ?? 0 }} to {{ $admins->lastItem() ?? 0 }} of {{ $admins->total() }} results
            </div>
            {{ $admins->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.row { display: flex; flex-wrap: wrap; margin: 0 -15px; }
.col-md-2 { padding: 0 15px; flex: 0 0 20%; max-width: 20%; }
.col-md-3 { padding: 0 15px; flex: 0 0 25%; max-width: 25%; }
.mt-4 { margin-top: 1.5rem; }
.text-center { text-align: center; }

/* Filter Form Styling */
.filter-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.filter-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.filter-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-start;
}

.filter-form .form-group {
    margin-bottom: 0;
}

.filter-form .form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #374151;
    font-size: 14px;
}

.filter-form .form-control {
    height: 42px;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    background: #ffffff;
    transition: all 0.15s ease;
    box-sizing: border-box;
}

.filter-form .form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.filter-form .btn {
    height: 42px;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.filter-form .btn-primary {
    background: #3b82f6;
    color: white;
}

.filter-form .btn-primary:hover {
    background: #2563eb;
}

.filter-form .btn-secondary {
    background: #6b7280;
    color: white;
}

.filter-form .btn-secondary:hover {
    background: #4b5563;
}

@media (max-width: 768px) {
    .col-md-2, .col-md-3 { flex: 0 0 100%; max-width: 100%; }
    .filter-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $('#location-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Location settings updated successfully.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while updating location settings.';
                
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join('\n');
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    $('.edit-btn').on('click', function() {
        const empId = $(this).data('emp-id');
        
        $.ajax({
            url: `/super-admin/location-settings/${empId}/edit`,
            method: 'GET',
            success: function(response) {
                // Fill the form with admin data
                $('select[name="emp_id"]').val(empId);
                $('input[name="ip_address"]').val(response.location?.ip_address || '');
                $('input[name="latitude"]').val(response.location?.latitude || '');
                $('input[name="longitude"]').val(response.location?.longitude || '');
                $('input[name="range"]').val(response.location?.range || 100);
                
                // Scroll to form
                $('html, body').animate({
                    scrollTop: $('#location-form').offset().top - 100
                }, 500);
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to load admin data.'
                });
            }
        });
    });
});
</script>
@endpush