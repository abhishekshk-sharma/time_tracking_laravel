@extends('super-admin.layouts.app')

@section('title', 'Applications')

@section('content')
<div class="page-header">
    <h1 class="page-title">Applications</h1>
    <p class="page-subtitle">Manage employee leave and other requests</p>
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
                                    <div style="width: 32px; height: 32px; background: #ff6b35; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; margin-right: 10px;">
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
                <p>No applications have been submitted yet</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewApplication(applicationId) {
    // Simple view functionality for super admin
    Swal.fire({
        title: 'Application Details',
        text: 'Application viewing functionality',
        icon: 'info'
    });
}
</script>
@endpush