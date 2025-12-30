@extends('layouts.app')

@section('title', 'Application Details')

@section('content')
<div class="container">
    <div class="main-content">
        <div class="content-area">
            <div class="application-header">
                <h2>Application Details</h2>
                <a href="{{ route('applications.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Applications
                </a>
            </div>

            <div class="application-details">
                <div class="detail-card">
                    <div class="detail-item">
                        <label>Application ID:</label>
                        <span>{{ $application->id }}</span>
                    </div>
                    
                    <div class="detail-item">
                        <label>Request Type:</label>
                        <span>{{ ucfirst(str_replace('_', ' ', $application->req_type)) }}</span>
                    </div>
                    
                    <div class="detail-item">
                        <label>Subject:</label>
                        <span>{{ $application->subject }}</span>
                    </div>
                    
                    <div class="detail-item">
                        <label>Start Date:</label>
                        <span>{{ $application->start_date }}</span>
                    </div>
                    
                    @if($application->end_date)
                    <div class="detail-item">
                        <label>End Date:</label>
                        <span>{{ $application->end_date }}</span>
                    </div>
                    @endif
                    
                    <div class="detail-item">
                        <label>Description:</label>
                        <span>{{ $application->description }}</span>
                    </div>
                    
                    @if($application->file)
                    <div class="detail-item">
                        <label>Attachment:</label>
                        <a href="{{ route('applications.download', $application) }}" class="attachment-link">
                            <i class="fas fa-paperclip"></i> Download Attachment
                        </a>
                    </div>
                    @endif
                    
                    <div class="detail-item">
                        <label>Status:</label>
                        <span class="status-badge status-{{ $application->status }}">
                            {{ ucfirst($application->status) }}
                        </span>
                    </div>
                    
                    <div class="detail-item">
                        <label>Applied On:</label>
                        <span>{{ $application->created_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.application-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.detail-card {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.detail-item {
    display: flex;
    margin-bottom: 20px;
    align-items: flex-start;
}

.detail-item label {
    width: 150px;
    font-weight: 600;
    color: var(--dark);
    flex-shrink: 0;
}

.detail-item span {
    flex: 1;
    color: var(--gray);
}

.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background-color: #fff4e6;
    color: var(--warning);
}

.status-approved {
    background-color: #e6f7ee;
    color: var(--success);
}

.status-rejected {
    background-color: #ffebee;
    color: var(--danger);
}

.attachment-link {
    color: var(--secondary);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 5px;
}

.attachment-link:hover {
    text-decoration: underline;
}
</style>
@endsection