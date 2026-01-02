@extends('admin.layouts.app')

@section('title', 'Applications')

@section('content')
<div class="page-header">
    <h1 class="page-title">Applications</h1>
    <p class="page-subtitle">Manage employee leave and other requests</p>
</div>



<!-- Filters -->
<div class="card">
    <div class="card-body" >
        <form method="GET" action="{{ route('admin.applications') }}" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(155 px, 5fr)); gap: 15px; align-items: end; " class="filter-form">
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
                                <span class="badge badge-secondary">{{ ucfirst(str_replace('_', ' ', $application->req_type)) }}</span>
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
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($application->status === 'approved')
                                    <span class="badge badge-success">Approved</span>
                                @else
                                    <span class="badge badge-danger">Rejected</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <button class="btn btn-sm btn-secondary" onclick="viewApplication({{ $application->id }})" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($application->status === 'pending')
                                        <button class="btn btn-sm btn-success" onclick="updateStatus({{ $application->id }}, 'approved')" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="updateStatus({{ $application->id }}, 'rejected')" title="Reject">
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
function viewApplication(applicationId) {
    $.get(`/admin/applications/${applicationId}`, function(data) {
        const statusColors = {
            'pending': '#f59e0b',
            'approved': '#059669',
            'rejected': '#dc2626'
        };
        
        const statusColor = statusColors[data.status] || '#6b7280';
        
        let html = `
            <div style="text-align: left;">
                <div style="margin-bottom: 15px;">
                    <strong>Employee:</strong> ${data.employee.name} (${data.employee.emp_id})
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Type:</strong> <span class="badge badge-secondary">${data.req_type.replace('_', ' ')}</span>
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
                <div style="margin-bottom: 15px;">
                    <strong>Status:</strong> <span style="background: ${statusColor}; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px;">${data.status.toUpperCase()}</span>
                </div>
                ${data.admin_remarks ? `<div style="margin-bottom: 15px;"><strong>Admin Remarks:</strong><br><div style="background: #f9fafb; padding: 10px; border-radius: 4px; margin-top: 5px;">${data.admin_remarks}</div></div>` : ''}
            </div>
        `;
        
        Swal.fire({
            title: 'Application Details',
            html: html,
            width: 600,
            showCloseButton: true,
            showConfirmButton: false
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
        confirmButtonText: `Yes, ${status} it!`,
        input: 'textarea',
        inputPlaceholder: 'Add remarks (optional)...',
        inputAttributes: {
            'aria-label': 'Admin remarks'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/applications/${applicationId}/status`,
                method: 'POST',
                data: {
                    status: status,
                    admin_remarks: result.value || '',
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
                    Swal.fire({
                        title: 'Error!',
                        text: 'Something went wrong. Please try again.',
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