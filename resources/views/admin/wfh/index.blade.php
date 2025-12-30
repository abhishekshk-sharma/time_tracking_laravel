@extends('admin.layouts.app')

@section('title', 'Work From Home')

@section('content')
<div class="page-header">
    <h1 class="page-title">Work From Home Requests</h1>
    <p class="page-subtitle">Manage employee WFH applications</p>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-body">
        <form method="GET" style="display: grid; grid-template-columns: 200px 200px 200px 120px; gap: 15px; align-items: end;">
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
                <label class="form-label">From Date</label>
                <input type="date" name="fromdate" class="form-control" value="{{ request('fromdate') }}">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">To Date</label>
                <input type="date" name="todate" class="form-control" value="{{ request('todate') }}">
            </div>
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Filter
            </button>
        </form>
    </div>
</div>

<!-- WFH Requests Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">WFH Requests ({{ $wfhRequests->total() }} total)</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        @if($wfhRequests->count() > 0)
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Reason</th>
                            <th>Applied On</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($wfhRequests as $wfh)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <div style="width: 32px; height: 32px; background: #ff9900; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; margin-right: 10px;">
                                        {{ strtoupper(substr($wfh->employee->username ?? 'N', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 500;">{{ $wfh->employee->username ?? 'Unknown' }}</div>
                                        <div style="font-size: 12px; color: #565959;">{{ $wfh->employee_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 500;">
                                    {{ $wfh->start_date instanceof \Carbon\Carbon ? $wfh->start_date->format('d') : " " }}
                                    To
                                    {{ $wfh->end_date instanceof \Carbon\Carbon ? $wfh->end_date->format('d M, Y') : " " }}
                                </div>
                                <div style="font-size: 12px; color: #565959;">{{ $wfh->start_date instanceof \Carbon\Carbon ? $wfh->start_date->format('l') : '' }}</div>
                            </td>
                            <td>
                                <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $wfh->description ?: 'No reason provided' }}
                                </div>
                            </td>
                            <td>{{ $wfh->created_at instanceof \Carbon\Carbon ? $wfh->created_at->format('M d, Y') : $wfh->created_at }}</td>
                            <td>
                                @if($wfh->status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($wfh->status === 'approved')
                                    <span class="badge badge-success">Approved</span>
                                @else
                                    <span class="badge badge-danger">Rejected</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <button class="btn btn-sm btn-secondary" onclick="viewWfhDetails({{ $wfh->id }})" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($wfh->status === 'pending')
                                        <button class="btn btn-sm btn-success" onclick="updateWfhStatus({{ $wfh->id }}, 'approved')" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="updateWfhStatus({{ $wfh->id }}, 'rejected')" title="Reject">
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
            
            @if($wfhRequests->hasPages())
                <div style="padding: 20px; border-top: 1px solid #eee;">
                    {{ $wfhRequests->links() }}
                </div>
            @endif
        @else
            <div style="text-align: center; padding: 40px; color: #565959;">
                <i class="fas fa-laptop-house" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                <h3>No WFH requests found</h3>
                <p>{{ request()->hasAny(['status', 'date']) ? 'Try adjusting your filter criteria' : 'No work from home requests have been submitted yet' }}</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewWfhDetails(wfhId) {
    fetch(`/admin/applications/${wfhId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const fromDate = new Date(data.from_date).toLocaleDateString('en-US', { 
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
        });
        const toDate = data.to_date ? new Date(data.to_date).toLocaleDateString('en-US', { 
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
        }) : fromDate;
        
        const appliedDate = new Date(data.created_at).toLocaleDateString('en-US', { 
            year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
        });
        
        let statusBadge = '';
        if (data.status === 'pending') {
            statusBadge = '<span class="badge badge-warning">Pending</span>';
        } else if (data.status === 'approved') {
            statusBadge = '<span class="badge badge-success">Approved</span>';
        } else {
            statusBadge = '<span class="badge badge-danger">Rejected</span>';
        }
        
        Swal.fire({
            title: 'WFH Request Details',
            html: `<div style="text-align: left;">
                <div style="margin-bottom: 15px;">
                    <strong>Employee:</strong> ${data.employee.username} (${data.employee_id})
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Subject:</strong> ${data.subject || 'Work From Home Request'}
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Date:</strong> ${fromDate}${data.to_date && data.to_date !== data.from_date ? ' to ' + toDate : ''}
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Reason:</strong><br>
                    <div style="background: #f8f9fa; padding: 10px; border-radius: 4px; margin-top: 5px;">
                        ${data.description || 'No reason provided'}
                    </div>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Applied On:</strong> ${appliedDate}
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Status:</strong> ${statusBadge}
                </div>
                ${data.admin_remarks ? `<div style="margin-bottom: 15px;">
                    <strong>Admin Remarks:</strong><br>
                    <div style="background: #fff3cd; padding: 10px; border-radius: 4px; margin-top: 5px;">
                        ${data.admin_remarks}
                    </div>
                </div>` : ''}
                ${data.action_by ? `<div style="margin-bottom: 15px;">
                    <strong>Action By:</strong> ${data.action_by}
                </div>` : ''}
            </div>`,
            width: '600px',
            confirmButtonColor: '#ff9900'
        });
    })
    .catch(error => {
        Swal.fire({
            title: 'Error',
            text: 'Failed to load WFH request details. Please try again.',
            icon: 'error',
            confirmButtonColor: '#d13212'
        });
    });
}

function updateWfhStatus(wfhId, status) {
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to ${status} this WFH request?`,
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
                url: `/admin/wfh/${wfhId}/status`,
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