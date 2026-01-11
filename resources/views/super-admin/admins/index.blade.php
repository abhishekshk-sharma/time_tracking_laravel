@extends('super-admin.layouts.app')

@section('title', 'Admin Management')

@section('content')

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Admin Management</h1>
            <p class="page-subtitle">Manage administrators and their assigned employees</p>
        </div>
        <div>
            <a href="{{ route('super-admin.admins.create') }}" class="btn btn-primary" >
                <i class="fas fa-plus"></i> Create Admin
            </a>
        </div>
    </div>
</div>
{{-- <div class="page-header" style="position:relative;">
    <h1 class="page-title">Admin Management</h1>
    <p class="page-subtitle">Manage administrators and their assigned employees</p>
    <a href="{{ route('super-admin.admins.create') }}" class="btn btn-primary" >
            <i class="fas fa-plus"></i> Create Admin
    </a>
</div> --}}

<!-- Search & Filter -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title">Search & Filter Admins</h3>
        <div style="display: flex; align-items: center; gap: 10px;">
            <label style="font-size: 12px; color: #666; margin: 0;">Per Page:</label>
            <select id="per-page-select" style="padding: 4px 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 15px; align-items: end;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 5px;">Search Admin</label>
                <input type="text" id="admin-search" placeholder="Admin ID or Username" 
                       style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; width: 100%;" 
                       value="{{ request('search') }}">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 5px;">Status</label>
                <select id="status-filter" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; width: 100%;">
                    <option value="" {{ request('status') == '' ? 'selected' : '' }}>All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div style="display: flex; gap: 8px;">
                <button onclick="applyFilters()" class="btn btn-primary" style="padding: 8px 20px; font-size: 14px;">
                    <i class="fas fa-search"></i> Search
                </button>
                <button onclick="clearFilters()" class="btn btn-secondary" style="padding: 8px 15px; font-size: 14px;">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Administrators ({{ $admins->total() }} total)</h3>
        
    </div>
    <div class="card-body" style="padding: 0;">
        @if($admins->count() > 0)
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Admin Details</th>
                            <th>Contact</th>
                            <th>Assigned Employees</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admins as $admin)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <div style="width: 40px; height: 40px; background: #ff6b35; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; margin-right: 12px;">
                                        {{ strtoupper(substr($admin->username, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 600;">{{ $admin->username }}</div>
                                        <div style="font-size: 12px; color: #86868b;">{{ $admin->emp_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $admin->email }}</div>
                                <div style="font-size: 12px; color: #86868b;">{{ $admin->phone ?: 'No phone' }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #ff6b35;">{{ $admin->assigned_employees_count }}</div>
                                <div style="font-size: 12px; color: #86868b;">employees assigned</div>
                            </td>
                            <td>
                                @if($admin->status === 'active')
                                    <span style="background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600;">Active</span>
                                @else
                                    <span style="background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600;">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="{{ route('super-admin.admins.show', $admin) }}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('super-admin.admins.edit', $admin) }}" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($admins->hasPages())
                <div style="padding: 20px;">
                    {{ $admins->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <div style="text-align: center; padding: 60px; color: #86868b;">
                <i class="fas fa-user-shield" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
                <h3>No administrators found</h3>
                <p>{{ request('search') || request('status') ? 'No admins match your search criteria' : 'No admin users are currently in the system' }}</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function applyFilters() {
    const search = document.getElementById('admin-search').value;
    const status = document.getElementById('status-filter').value;
    const perPage = document.getElementById('per-page-select').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (perPage) params.append('per_page', perPage);
    
    window.location.href = '{{ route("super-admin.admins") }}?' + params.toString();
}

function clearFilters() {
    document.getElementById('admin-search').value = '';
    document.getElementById('status-filter').value = '';
    document.getElementById('per-page-select').value = '10';
    window.location.href = '{{ route("super-admin.admins") }}';
}

// Per page change handler
document.getElementById('per-page-select').addEventListener('change', function() {
    applyFilters();
});

// Enter key handler for search input
document.getElementById('admin-search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});
</script>
@endpush