@extends('layouts.user')

@section('page-title', 'Today\'s Activity')
@section('page-subtitle', 'Your timeline for today')

@push('page-styles')
<style>
    /* Activity Content */
    .activity-content {
        padding: 1.5rem;
    }

    .activity-timeline {
        position: relative;
    }

    .activity-placeholder {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--gray-400);
        gap: 0.75rem;
    }
    
    .activity-placeholder i { font-size: 2.5rem; opacity: 0.6; }

    /* Section Title */
    .section-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 1.25rem;
        padding-bottom: 0.7rem;
        border-bottom: 1.5px solid var(--gray-200);
        display: flex;
        align-items: center;
        gap: 0.6rem;
        letter-spacing: -0.2px;
    }

    .section-title i {
        font-size: 1rem;
        color: var(--primary);
        background: var(--accent-soft);
        padding: 0.35rem;
        border-radius: 0.5rem;
    }

    /* Activity List */
    .activity-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.8rem 1rem;
        background: var(--bg-card);
        border-radius: var(--radius);
        border: 1px solid var(--border-light);
        transition: var(--transition);
        position: relative;
        cursor: default;
        box-shadow: 3px 3px 5px -2px rgb(157, 172, 197);
        margin-bottom: 0.5rem;
    }

    .activity-item:hover {
        background: var(--bg-hover);
        border-color: var(--gray-200);
        transform: translateX(3px);
        box-shadow: var(--shadow-soft-hover);
    }

    .activity-icon {
        width: 42px;
        height: 42px;
        border-radius: var(--radius);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: var(--transition);
    }

    .activity-icon i {
        font-size: 1.2rem;
        transition: var(--transition);
    }

    .activity-item[data-type="punch_in"] .activity-icon { background: var(--punch-in-light); }
    .activity-item[data-type="punch_in"] .activity-icon i { color: var(--punch-in-icon); }
    .activity-item[data-type="punch_out"] .activity-icon { background: var(--punch-out-light); }
    .activity-item[data-type="punch_out"] .activity-icon i { color: var(--punch-out-icon); }
    .activity-item[data-type="lunch_start"] .activity-icon,
    .activity-item[data-type="lunch_end"] .activity-icon { background: var(--lunch-light); }
    .activity-item[data-type="lunch_start"] .activity-icon i,
    .activity-item[data-type="lunch_end"] .activity-icon i { color: var(--lunch-icon); }

    .activity-item:hover .activity-icon i { transform: scale(1.05); }

    .activity-details {
        flex: 1;
        min-width: 0;
    }

    .activity-name {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--text-primary);
        text-transform: capitalize;
        letter-spacing: -0.2px;
        margin-bottom: 0.2rem;
        line-height: 1.4;
    }

    .activity-time {
        font-size: 0.7rem;
        color: var(--text-muted);
        font-weight: 500;
        letter-spacing: 0.2px;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    /* Card */
    .activity-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .card-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        background: var(--bg-card);
    }
    
    .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 0;
    }

    @media (max-width: 640px) {
        .activity-content { padding: 1rem; }
    }
</style>
@endpush

@section('page-content')
<div class="activity-card">
    <div class="card-header">
        <h2 class="card-title"><i class="fas fa-activity"></i>Today's Activity</h2>
    </div>
    <div class="activity-content">
        <div class="activity-timeline" id="activityTimeline">
            <div class="activity-placeholder">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading activity...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-scripts')
<script>
$(document).ready(function() {
    function loadActivityData() {
        $.ajax({
            url: '{{ route("api.time.details") }}',
            method: 'POST',
            data: { click: 'getDetails', _token: '{{ csrf_token() }}' },
            success: function(data) {
                if (data && data.trim() !== '') {
                    $('#activityTimeline').html(data);
                } else {
                    $('#activityTimeline').html(`<div class="activity-placeholder"><i class="fas fa-clock"></i><p>No activity recorded yet today</p></div>`);
                }
            }
        });
    }

    loadActivityData();
    
    // Refresh activity every 5 minutes
    setInterval(loadActivityData, 300000);
});
</script>
@endpush
