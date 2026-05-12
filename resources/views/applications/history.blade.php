@extends('layouts.user')

@section('page-title', 'Time History')
@section('page-subtitle', 'View your attendance and time tracking history')

@push('page-styles')
<style>
    .card {
        border: 1px solid rgba(100, 116, 139, 0.16);
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
        border-radius: 22px;
        overflow: hidden;
    }

    .card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.5rem 1.75rem;
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.08), rgba(99, 102, 241, 0.04));
    }

    .header-title {
        flex: 1;
        min-width: 240px;
    }

    .header-title .card-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.35rem;
        margin-bottom: 0.35rem;
        color: var(--slate-900);
    }

    .header-title .card-title i {
        color: var(--indigo-600);
    }

    .header-title .card-subtitle {
        margin: 0;
        color: var(--slate-600);
        font-size: 0.95rem;
    }

    .card-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
        justify-content: flex-end;
    }

    .filter-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
    }

    .form-input {
        border: 1px solid rgba(100, 116, 139, 0.2);
        border-radius: 0.75rem;
        padding: 0.8rem 1rem;
        min-width: 170px;
        background: #ffffff;
        color: var(--slate-700);
        box-shadow: inset 0 0 0 1px transparent;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .form-input:focus,
    .form-input:hover {
        border-color: rgba(79, 70, 229, 0.4);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.08);
        outline: none;
    }

    #customDateInputs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
    }

    .table-container {
        overflow-x: auto;
        background: #ffffff;
        border-top: 1px solid rgba(148, 163, 184, 0.2);
        
    }

    .table {
        width: 100%;
        min-width: 760px;
        border-collapse: separate;
        border-spacing: 0 0.45rem;
    }

    .table th,
    .table td {
        text-align: left;
        padding: 0.9rem 1rem;
        vertical-align: middle;
        border: none;
        background: transparent;
        color: var(--slate-700);
        font-size: 0.95rem;
    }

    .table thead th {
        color: var(--slate-900);
        font-weight: 700;
        padding-bottom: 0.9rem;
        border-bottom: 1px solid rgba(148, 163, 184, 0.2);
    }

    .table tbody tr {
        background: rgba(99, 102, 241, 0.04);
        border-radius: 1rem;
        box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.08);
    }

    .table tbody tr td {
        border-radius: 0.85rem;
        background: #ffffff;
    }

    .table .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.45rem 0.8rem;
        border-radius: 999px;
        font-size: 0.80rem;
        font-weight: 600;
        min-width: 96px;
    }

    .table .badge-present {
        background: rgba(34, 197, 94, 0.12);
        color: #047857;
    }

    .table .badge-late {
        background: rgba(245, 158, 11, 0.12);
        color: #92400e;
    }

    .table .badge-absent {
        background: rgba(239, 68, 68, 0.12);
        color: #b91c1c;
    }

    .table .badge-weekend {
        background: rgba(59, 130, 246, 0.12);
        color: #1d4ed8;
    }

    .table td .value-muted {
        color: var(--slate-500);
    }

    @media (max-width: 900px) {
        .card-header {
            flex-direction: column;
            align-items: stretch;
        }

        .card-actions {
            justify-content: center;
        }

        .table {
            min-width: 700px;
        }
    }

    @media (max-width: 640px) {
        .card-header {
            padding: 1.25rem 1rem;
        }

        .header-title .card-title {
            font-size: 1.15rem;
        }

        .filter-group,
        #customDateInputs {
            flex-direction: column;
            align-items: stretch;
        }

        .form-input {
            width: 100%;
            min-width: unset;
        }

        .table {
            min-width: 620px;
        }
    }

    @media (max-width: 480px) {
        .table {
            min-width: 560px;
        }
    }
</style>
@endpush

@section('page-content')
<div class="card">
    <div class="card-header">
        <div class="header-title">
            <h2 class="card-title">
                <i class="fas fa-clock"></i>
                {{ Auth::user()->full_name }} - Time Records
            </h2>
            <p class="card-subtitle">Filter attendance history by month or a custom date range.</p>
        </div>
        <div class="card-actions">
            <div class="filter-group">
                <select id="monthFilter" class="form-input">
                    <option value="current">Current Month</option>
                    <option value="last">Last Month</option>
                    <option value="custom">Custom Range</option>
                </select>
                <div id="customDateInputs" style="display: none;">
                    <input type="date" id="startDate" class="form-input" placeholder="From Date">
                    <input type="date" id="endDate" class="form-input" placeholder="To Date">
                    <button type="button" id="filterBtn" class="btn btn-primary" style="padding: 0.85rem 1.2rem;">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Punch In</th>
                    <th>Lunch Start</th>
                    <th>Lunch End</th>
                    <th>Punch Out</th>
                    <th>Total Hours</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="historyTableBody">
                <tr><td colspan="7" style="padding: 20px; text-align: center;">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('page-scripts')
<script>
$(document).ready(function() {
    loadCurrentMonth();
    
    $('#monthFilter').change(function() {
        const value = $(this).val();
        if (value === 'custom') {
            $('#customDateInputs').show();
        } else {
            $('#customDateInputs').hide();
            if (value === 'last') {
                loadLastMonth();
            } else {
                loadCurrentMonth();
            }
        }
    });
    
    $('#filterBtn').click(function() {
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();
        
        if (!startDate || !endDate) {
            alert('Please select both start and end dates');
            return;
        }
        
        if (new Date(startDate) > new Date(endDate)) {
            alert('Start date cannot be later than end date');
            return;
        }
        
        loadCustomRange(startDate, endDate);
    });
    
    function loadCurrentMonth() {
        $('#historyTableBody').html('<tr><td colspan="7" style="padding: 20px; text-align: center;">Loading...</td></tr>');
        
        $.ajax({
            url: '{{ route("api.time.details-by-id") }}',
            type: 'POST',
            data: {
                click: 'detailsById',
                id: '{{ Auth::user()->emp_id }}',
                _token: '{{ csrf_token() }}'
            },
            success: function(data) {
                if (data && data.trim() !== '') {
                    $('#historyTableBody').html(data);
                } else {
                    $('#historyTableBody').html('<tr><td colspan="7" style="padding: 20px; text-align: center; color: #666;">No data found for current month</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading current month data:', error);
                $('#historyTableBody').html('<tr><td colspan="7" style="padding: 20px; text-align: center; color: red;">Error loading data: ' + (xhr.responseText || error) + '</td></tr>');
            }
        });
    }
    
    function loadLastMonth() {
        $('#historyTableBody').html('<tr><td colspan="7" style="padding: 20px; text-align: center;">Loading...</td></tr>');
        
        $.ajax({
            url: '{{ route("api.time.filter") }}',
            type: 'POST',
            data: {
                click: 'filterLastMonth',
                id: '{{ Auth::user()->emp_id }}',
                _token: '{{ csrf_token() }}'
            },
            success: function(data) {
                if (data && data.trim() !== '') {
                    $('#historyTableBody').html(data);
                } else {
                    $('#historyTableBody').html('<tr><td colspan="7" style="padding: 20px; text-align: center; color: #666;">No data found for last month</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading last month data:', error);
                $('#historyTableBody').html('<tr><td colspan="7" style="padding: 20px; text-align: center; color: red;">Error loading data: ' + (xhr.responseText || error) + '</td></tr>');
            }
        });
    }
    
    function loadCustomRange(startDate, endDate) {
        $('#historyTableBody').html('<tr><td colspan="7" style="padding: 20px; text-align: center;">Loading...</td></tr>');
        
        $.ajax({
            url: '{{ route("api.time.filter") }}',
            type: 'POST',
            data: {
                click: 'filterCustomRange',
                id: '{{ Auth::user()->emp_id }}',
                start_date: startDate,
                end_date: endDate,
                _token: '{{ csrf_token() }}'
            },
            success: function(data) {
                if (data && data.trim() !== '') {
                    $('#historyTableBody').html(data);
                } else {
                    $('#historyTableBody').html('<tr><td colspan="7" style="padding: 20px; text-align: center; color: #666;">No data found for selected date range</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading custom range data:', error);
                $('#historyTableBody').html('<tr><td colspan="7" style="padding: 20px; text-align: center; color: red;">Error loading data: ' + (xhr.responseText || error) + '</td></tr>');
            }
        });
    }
});
</script>
@endpush