@extends('super-admin.layouts.app')

@section('title', 'Applications')

@section('content')
<div class="page-header">
    <h1 class="page-title">Applications</h1>
    <p class="page-subtitle">Manage employee leave and other requests</p>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('super-admin.applications') }}" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(155px, 5fr)); gap: 15px; align-items: end;" class="filter-form">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Type</label>
                <select name="type" class="form-control">
                    <option value="">All Types</option>
                    <option value="casual_leave" {{ request('type') === 'casual_leave' ? 'selected' : '' }}>Casual Leave</option>
                    <option value="sick_leave" {{ request('type') === 'sick_leave' ? 'selected' : '' }}>Sick Leave</option>
                    <option value="half_day" {{ request('type') === 'half_day' ? 'selected' : '' }}>Half Day</option>
                    <option value="regularization" {{ request('type') === 'regularization' ? 'selected' : '' }}>Regularization</option>
                    <option value="complaint" {{ request('type') === 'complaint' ? 'selected' : '' }}>Complaint</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Search Employee</label>
                <input type="text" name="search" class="form-control" placeholder="Name or ID" value="{{ request('search') }}">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Employee Status</label>
                <select name="employee_status" class="form-control">
                    <option value="all" {{ request('employee_status', 'all') === 'all' ? 'selected' : '' }}>All</option>
                    <option value="active" {{ request('employee_status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('employee_status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Filter
            </button>
        </form>
    </div>
</div>

<!-- Applications Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Applications ({{ $applications->total() }} total)</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        @if($applications->count() > 0)
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Subject</th>
                            <th>Date Range</th>
                            <th>Applied On</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($applications as $application)
                        <tr>
                            <td>
                                <span class="badge bg-primary">#{{ $application->id }}</span>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <div style="width: 32px; height: 32px; background: #ff9900; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; margin-right: 10px;">
                                        {{ strtoupper(substr($application->employee->username ?? 'N', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 500;">{{ $application->employee->username ?? 'Unknown' }}</div>
                                        <div style="font-size: 12px; color: #565959;">{{ $application->employee->emp_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge p-2 text-bg-secondary">{{ ucfirst(str_replace('_', ' ', $application->req_type)) }}</span>
                            </td>
                            <td>
                                <div style="max-width: 200px;">
                                    <div style="font-weight: 500; margin-bottom: 4px;">{{ $application->subject ?: 'No subject' }}</div>
                                    @if($application->description)
                                        <div style="font-size: 12px; color: #565959; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            {{ Str::limit($application->description, 50) }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 14px;">
                                    {{ $application->start_date instanceof \Carbon\Carbon ? $application->start_date->format('M d, Y') : $application->start_date }}
                                    @if($application->end_date && $application->end_date != $application->start_date)
                                        <br><span style="color: #565959;">to {{ $application->end_date instanceof \Carbon\Carbon ? $application->end_date->format('M d, Y') : $application->end_date }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $application->created_at instanceof \Carbon\Carbon ? $application->created_at->format('M d, Y') : $application->created_at }}</td>
                            <td>
                                @if($application->status === 'pending')
                                    <span class="badge p-2 text-bg-warning">Pending</span>
                                @elseif($application->status === 'approved')
                                    <span class="badge p-2 text-bg-success">Approved</span>
                                @else
                                    <span class="badge p-2 text-bg-danger">Rejected</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <button class="btn btn-sm btn-secondary" onclick="viewApplication({{ $application->id }})" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($application->status === 'pending')
                                        @if($application->req_type === 'punch_Out_regularization')
                                            <button class="btn btn-sm btn-success" onclick="showApplicationDetails({{ $application->id }}, 'approved')" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-success" onclick="showApplicationDetails({{ $application->id }}, 'approved')" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        <button class="btn btn-sm btn-danger" onclick="showApplicationDetails({{ $application->id }}, 'rejected')" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($applications->hasPages())
                <div style="padding: 20px; border-top: 1px solid #eee;">
                    {{ $applications->links() }}
                </div>
            @endif
        @else
            <div style="text-align: center; padding: 40px; color: #565959;">
                <i class="fas fa-file-alt" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                <h3>No applications found</h3>
                <p>{{ request()->hasAny(['status', 'type']) ? 'Try adjusting your filter criteria' : 'No applications have been submitted yet' }}</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function showApplicationDetails(applicationId, action) {
    $.get(`/super-admin/applications/${applicationId}`, function(data) {
        const statusColors = {
            'pending': '#f59e0b',
            'approved': '#059669',
            'rejected': '#dc2626'
        };
        
        const statusColor = statusColors[data.status] || '#6b7280';
        const fileDisplay = data.file ? `<div style="margin-bottom: 15px;"><strong>Attached File:</strong><br><div style="display: flex; flex-direction: column; gap: 10px;"><img src="/${data.file}" alt="Application attachment" style="max-width: 100%; max-height: 300px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;" onclick="window.open('/${data.file}', '_blank')"><a href="/${data.file}" download style="color: #007bff; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;"><i class="fas fa-download"></i> Download Attachment</a></div></div>` : '';
        
        let html = `
            <div style="text-align: left;">
                <div style="margin-bottom: 15px;">
                    <strong>Employee:</strong> ${data.employee.username} (${data.employee.emp_id})
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Type:</strong> <span class="badge p-2 text-bg-secondary">${data.req_type.replace('_', ' ')}</span>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Subject:</strong> ${data.subject || 'No subject'}
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Description:</strong><br>
                    <div style="background: #f9fafb; padding: 10px; border-radius: 4px; margin-top: 5px;">
                        ${data.description || 'No description provided'}
                    </div>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Date Range:</strong> ${data.start_date} ${data.end_date && data.end_date !== data.start_date ? 'to ' + data.end_date : ''}
                </div>
                ${fileDisplay}
                <div style="margin-bottom: 15px;">
                    <strong>Status:</strong> <span style="background: ${statusColor}; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px;">${data.status.toUpperCase()}</span>
                </div>
            </div>
        `;
        
        const actionText = action === 'approved' ? 'Approve' : 'Reject';
        const actionColor = action === 'approved' ? '#067d62' : '#d13212';
        
        Swal.fire({
            title: 'Application Details',
            html: html,
            width: 600,
            showCancelButton: true,
            confirmButtonText: actionText,
            confirmButtonColor: actionColor,
            cancelButtonText: 'Cancel',
            cancelButtonColor: '#6c757d',
            customClass: {
                container: 'swal-high-z-index'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                if (data.req_type === 'punch_Out_regularization' && action === 'approved') {
                    showPunchOutModal(applicationId, data.end_date);
                } else {
                    updateStatus(applicationId, action);
                }
            }
        });
    }).fail(function() {
        Swal.fire({
            title: 'Error!',
            text: 'Failed to load application details.',
            icon: 'error',
            confirmButtonColor: '#d13212'
        });
    });
}

function viewApplication(applicationId) {
    $.get(`/super-admin/applications/${applicationId}`, function(data) {
        const statusColors = {
            'pending': '#f59e0b',
            'approved': '#059669',
            'rejected': '#dc2626'
        };
        
        const statusColor = statusColors[data.status] || '#6b7280';
        const fileDisplay = data.file ? `<div style="margin-bottom: 15px;"><strong>Attached File:</strong><br><div style="display: flex; flex-direction: column; gap: 10px;"><img src="/${data.file}" alt="Application attachment" style="max-width: 100%; max-height: 300px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;" onclick="window.open('/${data.file}', '_blank')"><a href="/${data.file}" download style="color: #007bff; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;"><i class="fas fa-download"></i> Download Attachment</a></div></div>` : '';
        
        let html = `
            <div style="text-align: left;">
                <div style="margin-bottom: 15px;">
                    <strong>Employee:</strong> ${data.employee.username} (${data.employee.emp_id})
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Type:</strong> <span class="badge p-2 text-bg-secondary">${data.req_type.replace('_', ' ')}</span>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Subject:</strong> ${data.subject || 'No subject'}
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Description:</strong><br>
                    <div style="background: #f9fafb; padding: 10px; border-radius: 4px; margin-top: 5px;">
                        ${data.description || 'No description provided'}
                    </div>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Date Range:</strong> ${data.start_date} ${data.end_date && data.end_date !== data.start_date ? 'to ' + data.end_date : ''}
                </div>
                ${fileDisplay}
                <div style="margin-bottom: 15px;">
                    <strong>Status:</strong> <span style="background: ${statusColor}; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px;">${data.status.toUpperCase()}</span>
                </div>
            </div>
        `;
        
        Swal.fire({
            title: 'Application Details',
            html: html,
            width: 600,
            showCloseButton: true,
            showConfirmButton: false,
            customClass: {
                container: 'swal-high-z-index'
            }
        });
    }).fail(function() {
        Swal.fire({
            title: 'Error!',
            text: 'Failed to load application details.',
            icon: 'error',
            confirmButtonColor: '#d13212'
        });
    });
}

function updateStatus(applicationId, status) {
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to ${status} this application?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: status === 'approved' ? '#067d62' : '#d13212',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Yes, ${status} it!`
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/super-admin/applications/${applicationId}/status`,
                method: 'POST',
                data: {
                    status: status,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#ff9900'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    console.log('Error details:', xhr);
                    let errorMessage = 'Something went wrong. Please try again.';
                    
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                    } else if (xhr.responseText) {
                        errorMessage = xhr.responseText;
                    }
                    
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonColor: '#d13212'
                    });
                }
            });
        }
    });
}

function showPunchOutModal(applicationId, requestedTime) {
    const date = new Date(requestedTime);
    const dateStr = date.toISOString().split('T')[0];
    const timeStr = date.toTimeString().split(' ')[0].substring(0, 5);
    
    Swal.fire({
        title: 'Approve Punch Out Regularization',
        html: `
            <div style="text-align: left; margin: 20px 0;">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Requested Date & Time:</label>
                    <div style="background: #f3f4f6; padding: 10px; border-radius: 4px; font-family: monospace;">
                        ${date.toLocaleString()}
                    </div>
                </div>
                <div style="margin-bottom: 15px;">
                    <label for="punch-date" style="display: block; margin-bottom: 5px; font-weight: 600;">Date:</label>
                    <input type="date" id="punch-date" value="${dateStr}" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label for="punch-time" style="display: block; margin-bottom: 5px; font-weight: 600;">Time:</label>
                    <input type="time" id="punch-time" value="${timeStr}" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Approve',
        confirmButtonColor: '#067d62',
        cancelButtonColor: '#6c757d',
        width: 500,
        preConfirm: () => {
            const date = document.getElementById('punch-date').value;
            const time = document.getElementById('punch-time').value;
            
            if (!date || !time) {
                Swal.showValidationMessage('Please select both date and time');
                return false;
            }
            
            return { date, time };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const customTime = `${result.value.date} ${result.value.time}:00`;
            
            $.ajax({
                url: `/super-admin/applications/${applicationId}/status`,
                method: 'POST',
                data: {
                    status: 'approved',
                    custom_time: customTime,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Punch out regularization approved successfully!',
                        icon: 'success',
                        confirmButtonColor: '#ff9900'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    console.log('Punch out error:', xhr);
                    let errorMessage = 'Something went wrong. Please try again.';
                    
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                    } else if (xhr.responseText) {
                        errorMessage = xhr.responseText;
                    }
                    
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonColor: '#d13212'
                    });
                }
            });
        }
    });
}
</script>
@endpush

@push('styles')
<style>
.swal-high-z-index {
    z-index: 99999 !important;
}
</style>
@endpush