@extends('layouts.user')

@section('page-title', 'Notifications')
@section('page-subtitle', 'View and manage your notifications')

@push('page-styles')
<style>
    .notification-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 1rem;
        overflow: hidden;
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }
    
    .notification-card.unread {
        border-left-color: #3b82f6;
        background: rgba(59, 130, 246, 0.02);
    }
    
    .notification-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }
    
    .notification-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: between;
        align-items: center;
    }
    
    .notification-content {
        padding: 1.5rem;
        display: none;
        color: #1f2937;
    }
    
    .notification-content h6 {
        color: #374151;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }
    
    .notification-content p {
        color: #4b5563;
        margin-bottom: 0.5rem;
    }
    
    .notification-content strong {
        color: #1f2937;
    }
    
    .notification-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .notification-title {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }
    
    .notification-subtitle {
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    .notification-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-approved { background: #dcfce7; color: #166534; }
    .status-rejected { background: #fef2f2; color: #dc2626; }
    .status-pending { background: #fef3c7; color: #d97706; }
    
    .btn-delete {
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 0.5rem;
        cursor: pointer;
        transition: all 0.2s;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .btn-delete:hover {
        background: #dc2626;
        transform: scale(1.05);
    }
    
    .clear-all-btn {
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .clear-all-btn:hover {
        background: #dc2626;
        transform: translateY(-1px);
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #6b7280;
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
</style>
@endpush

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4>All Notifications</h4>
        <p class="text-muted mb-0">{{ $notifications->count() }} notification(s)</p>
    </div>
    @if($notifications->count() > 0)
    <button class="clear-all-btn" onclick="clearAllNotifications()">
        <i class="fas fa-trash"></i>
        Clear All
    </button>
    @endif
</div>

@if($notifications->count() > 0)
    @foreach($notifications as $notification)
    <div class="notification-card {{ $notification->status === 'pending' ? 'unread' : '' }}" data-id="{{ $notification->id }}">
        <div class="notification-header" onclick="toggleNotification({{ $notification->id }})">
            <div class="notification-meta">
                <div>
                    <div class="notification-title">
                        {{ ucwords(str_replace('_', ' ', $notification->application->req_type ?? 'Application')) }} Request #{{ $notification->App_id }}
                    </div>
                    <div class="notification-subtitle">
                        {{-- Action by: {{ $notification->created_by ?? 'Admin' }} • {{ $notification->created_at->format('M d, Y') }} --}}
                    </div>
                </div>
                <div class="notification-actions">
                    @if($notification->application)
                        <span class="status-badge status-{{ $notification->application->status }}">
                            {{ $notification->application->status }}
                        </span>
                    @endif
                    <button class="btn-delete" onclick="deleteNotification({{ $notification->id }}, event)" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="notification-content" id="content-{{ $notification->id }}">
            @if($notification->application)
            <div class="row">
                <div class="col-md-6">
                    <h6>Application Details</h6>
                    <p><strong>Type:</strong> {{ ucwords(str_replace('_', ' ', $notification->application->req_type)) }}</p>
                    <p><strong>Start Date:</strong> {{ $notification->application->start_date }}</p>
                    @if($notification->application->end_date)
                    <p><strong>End Date:</strong> {{ $notification->application->end_date }}</p>
                    @endif
                    @if($notification->application->subject)
                    <p><strong>Subject:</strong> {{ $notification->application->subject }}</p>
                    @endif
                </div>
                <div class="col-md-6">
                    <h6>Status Information</h6>
                    <p><strong>Status:</strong> 
                        <span class="status-badge status-{{ $notification->application->status }}">
                            {{ $notification->application->status }}
                        </span>
                    </p>
                    <p><strong>Action By:</strong> {{ $notification->action_by ?? 'Admin' }}</p>
                    <p><strong>Applied On:</strong> {{ \Carbon\Carbon::parse($notification->application->created_at)->format('M d, Y H:i') }}</p>
                </div>
            </div>
            @if($notification->application->description)
            <div class="mt-3">
                <h6>Description</h6>
                <p>{{ $notification->application->description }}</p>
            </div>
            @endif
            @else
            <p>Application details not available.</p>
            @endif
        </div>
    </div>
    @endforeach
@else
    <div class="empty-state">
        <i class="fas fa-bell-slash"></i>
        <h5>No Notifications</h5>
        <p>You don't have any notifications yet.</p>
    </div>
@endif
@endsection

@push('page-scripts')
<script>
function toggleNotification(id) {
    const content = document.getElementById('content-' + id);
    const isVisible = content.style.display === 'block';
    
    // Hide all other notifications
    document.querySelectorAll('.notification-content').forEach(el => {
        el.style.display = 'none';
    });
    
    // Toggle current notification
    content.style.display = isVisible ? 'none' : 'block';
    
    // Mark as read
    if (!isVisible) {
        markAsRead(id);
    }
}

function markAsRead(id) {
    $.post(`/notifications/${id}/read`, {
        _token: '{{ csrf_token() }}'
    }).done(function() {
        $(`[data-id="${id}"]`).removeClass('unread');
    });
}

function deleteNotification(id, event) {
    event.stopPropagation();
    
    Swal.fire({
        title: 'Delete Notification?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Delete'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/notifications/${id}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function() {
                    $(`[data-id="${id}"]`).fadeOut(300, function() {
                        $(this).remove();
                        if ($('.notification-card').length === 0) {
                            location.reload();
                        }
                    });
                    Swal.fire('Deleted!', 'Notification has been deleted.', 'success');
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to delete notification.', 'error');
                }
            });
        }
    });
}

function clearAllNotifications() {
    Swal.fire({
        title: 'Clear All Notifications?',
        text: 'This will delete all your notifications permanently.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Clear All'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/notifications/clear-all',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function() {
                    Swal.fire('Cleared!', 'All notifications have been deleted.', 'success')
                        .then(() => location.reload());
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to clear notifications.', 'error');
                }
            });
        }
    });
}
</script>
@endpush