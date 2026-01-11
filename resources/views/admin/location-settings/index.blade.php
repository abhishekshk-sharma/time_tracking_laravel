@extends('admin.layouts.app')

@section('title', 'Location Settings')

@section('content')
<div class="page-header">
    <h1 class="page-title">Location Settings</h1>
    <p class="page-subtitle">Manage admin location settings for tracking purposes</p>
</div>

<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-number">{{ $locations[Auth::user()->emp_id]->ip_address ?? 'Not Set' }}</div>
        <div class="stat-label">IP Address</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $locations[Auth::user()->emp_id]->latitude ?? 'Not Set' }}</div>
        <div class="stat-label">Latitude</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $locations[Auth::user()->emp_id]->longitude ?? 'Not Set' }}</div>
        <div class="stat-label">Longitude</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $locations[Auth::user()->emp_id]->range ?? 100 }} m</div>
        <div class="stat-label">Range</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Update Location Settings</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.location-settings.update') }}" method="POST" id="location-form">
            @csrf
            <input type="hidden" name="emp_id" value="{{ Auth::user()->emp_id }}">
            
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label">IP Address</label>
                        <input type="text" 
                               name="ip_address" 
                               class="form-control" 
                               value="{{ $locations[Auth::user()->emp_id]->ip_address ?? '' }}"
                               placeholder="192.168.1.1">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label">Latitude</label>
                        <input type="number" 
                               name="latitude" 
                               class="form-control" 
                               step="0.00000001"
                               value="{{ $locations[Auth::user()->emp_id]->latitude ?? '' }}"
                               placeholder="40.7128">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label">Longitude</label>
                        <input type="number" 
                               name="longitude" 
                               class="form-control" 
                               step="0.00000001"
                               value="{{ $locations[Auth::user()->emp_id]->longitude ?? '' }}"
                               placeholder="-74.0060">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label">Range (meters)</label>
                        <input type="number" 
                               name="range" 
                               class="form-control" 
                               min="1"
                               value="{{ $locations[Auth::user()->emp_id]->range ?? 100 }}"
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
@endsection

@push('styles')
<style>
.row { display: flex; flex-wrap: wrap; margin: 0 -15px; }
.col-md-3 { padding: 0 15px; flex: 0 0 25%; max-width: 25%; }
.col-md-4 { padding: 0 15px; flex: 0 0 33.333333%; max-width: 33.333333%; }
.mb-4 { margin-bottom: 1.5rem; }
.mt-4 { margin-top: 1.5rem; }
.text-center { text-align: center; }
@media (max-width: 768px) {
    .col-md-3, .col-md-4 { flex: 0 0 100%; max-width: 100%; }
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
});
</script>
@endpush