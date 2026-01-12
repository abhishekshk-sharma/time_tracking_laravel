@extends('super-admin.layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Notifications</h1>
            <p class="page-subtitle">View all system notifications</p>
        </div>
        <button class="btn btn-danger" onclick="clearAllNotifications()">
            <i class="fas fa-trash"></i> Clear All
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div id="notifications-list">
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                <p class="mt-2 text-muted">Loading notifications...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    loadNotifications();
    
    function loadNotifications() {
        $.get('{{ route("super-admin.notifications") }}')
            .done(function(notifications) {
                displayNotifications(notifications);
            })
            .fail(function() {
                $('#notifications-list').html('<div class="text-center py-4"><i class="fas fa-exclamation-triangle fa-2x text-danger"></i><p class="mt-2 text-danger">Failed to load notifications</p></div>');
            });
    }
    
    function displayNotifications(notifications) {
        if (notifications.length === 0) {
            $('#notifications-list').html('<div class="text-center py-4"><i class="fas fa-bell-slash fa-2x text-muted"></i><p class="mt-2 text-muted">No notifications found</p></div>');
            return;
        }
        
        let html = '';
        notifications.forEach(function(notification) {
            const isUnread = notification.status !== 'checked';
            const createdAt = new Date(notification.created_at).toLocaleString();
            
            html += `
                <div class="notification-item ${isUnread ? 'unread' : ''}" data-id="${notification.id}">
                    <div class="d-flex align-items-start">
                        <div class="notification-icon me-3">
                            <i class="fas fa-file-alt text-primary"></i>
                        </div>
                        <div class="flex-grow-1" style="cursor: pointer;" onclick="showApplicationModal(${notification.App_id})">
                            <h6 class="mb-1">${notification.application?.req_type || 'Application'} Request #${notification.App_id}</h6>
                            <p class="mb-1 text-muted">From: ${notification.created_by?.username || notification.created_by} (${notification.created_by?.emp_id || 'N/A'})</p>
                            <small class="text-muted">${createdAt}</small>
                        </div>
                        <div class="notification-actions">
                            ${isUnread ? '<span class="badge bg-primary me-2">New</span>' : ''}
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteNotification(${notification.id}, this.closest('.notification-item'))" title="Delete notification">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        $('#notifications-list').html(html);
        
        
        // Remove the click handler since it's now inline
    }
    
    function deleteNotification(notificationId, element) {
        if (confirm('Are you sure you want to delete this notification?')) {
            $.ajax({
                url: `{{ route("super-admin.notifications.read", ":id") }}`.replace(':id', notificationId),
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function() {
                    $(element).fadeOut(300, function() {
                        $(this).remove();
                    });
                },
                error: function() {
                    alert('Failed to delete notification');
                }
            });
        }
    }
    
    window.deleteNotification = deleteNotification;
    
    function showApplicationModal(applicationId) {
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
                    <div style="text-align: center; margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <a href="{{ route('super-admin.applications') }}" style="color: #007bff; text-decoration: none; font-weight: 500;">
                            <i class="fas fa-external-link-alt me-2"></i>Go to application page for more actions
                        </a>
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
    
    window.showApplicationModal = showApplicationModal;
    
    function clearAllNotifications() {
        if (confirm('Are you sure you want to delete all notifications?')) {
            $.ajax({
                url: '{{ route("super-admin.notifications") }}/clear-all',
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function() {
                    location.reload();
                },
                error: function() {
                    alert('Failed to clear notifications');
                }
            });
        }
    }
    
    window.clearAllNotifications = clearAllNotifications;
    
    function markAsRead(notificationId, element) {
        // Create a form and submit it to trigger the POST route with redirect
        const form = $('<form>', {
            method: 'POST',
            action: `{{ route("super-admin.notifications.read", ":id") }}`.replace(':id', notificationId)
        });
        form.append($('<input>', { type: 'hidden', name: '_token', value: '{{ csrf_token() }}' }));
        $('body').append(form);
        form.submit();
    }
});
</script>
@endpush

@push('styles')
<style>
.notification-item {
    padding: 15px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    transition: background-color 0.2s;
}
.notification-item:hover {
    background-color: #f8f9fa;
}
.notification-item.unread {
    background-color: #e3f2fd;
    border-left: 4px solid #2196f3;
}
.notification-item:last-child {
    border-bottom: none;
}
.notification-icon {
    width: 40px;
    height: 40px;
    background: #f0f0f0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.notification-badge {
    margin-left: auto;
}
</style>
@endpush