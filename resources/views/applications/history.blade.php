@extends('layouts.user')

@section('page-title', 'Time History')
@section('page-subtitle', 'View your attendance and time tracking history')

@push('page-styles')
<style>
    .card-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    
    .table-container {
        overflow-x: auto;
        border-radius: var(--radius-lg);
    }
</style>
@endpush

@section('page-content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-clock"></i>
            {{ Auth::user()->full_name }} - Time Records
        </h2>
        <div class="card-actions">
            <select id="monthFilter" class="form-input" style="width: auto; min-width: 150px;">
                <option value="current">Current Month</option>
                <option value="last">Last Month</option>
            </select>
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
        if ($(this).val() === 'last') {
            loadLastMonth();
        } else {
            loadCurrentMonth();
        }
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
});
</script>
@endpush