@extends('admin.layouts.app')

@section('title', 'Schedule Management')

@section('content')
<div class="page-header">
    <h1 class="page-title">Schedule Management</h1>
    <p class="page-subtitle">Manage holidays and employee schedules</p>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<!-- Month Navigation -->
<div class="card">
    <div class="card-body">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3>{{ date('F Y', mktime(0, 0, 0, $currentMonth, 1, $currentYear)) }}</h3>
            <div>
                <a href="{{ route('admin.schedule', ['month' => $currentMonth == 1 ? 12 : $currentMonth - 1, 'year' => $currentMonth == 1 ? $currentYear - 1 : $currentYear]) }}" class="btn btn-secondary">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
                <a href="{{ route('admin.schedule', ['month' => $currentMonth == 12 ? 1 : $currentMonth + 1, 'year' => $currentMonth == 12 ? $currentYear + 1 : $currentYear]) }}" class="btn btn-secondary">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Add Holiday Form -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add Holiday</h3>
    </div>
    <div class="card-body">
        <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 15px; align-items: end;">
            @csrf
            <input type="hidden" name="add_holiday" value="1">
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Holiday Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" required>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">End Date (Optional)</label>
                <input type="date" name="end_date" class="form-control">
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-control">
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Holiday
            </button>
        </form>
    </div>
</div>

<!-- Current Holidays -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Current Month Holidays</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        @if($holidays->count() > 0)
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Holiday</th>
                            <th>Employees Affected</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($holidays as $holiday)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($holiday['entry_time'])->format('M d, Y') }}</td>
                            <td>
                                @php
                                    $notes = $holiday['notes'];
                                    if (strpos($notes, 'Holiday: ') === 0) {
                                        $notes = substr($notes, 9);
                                    }
                                @endphp
                                {{ $notes }}
                            </td>
                            <td>{{ $holiday['employee_count'] }} employees</td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editHoliday('{{ $holiday['entry_time'] }}', '{{ addslashes($notes) }}')" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteHoliday('{{ $holiday['entry_time'] }}')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; padding: 40px; color: #565959;">
                <i class="fas fa-calendar" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                <h3>No holidays found</h3>
                <p>Add holidays for this month using the form above</p>
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
function editHoliday(date, notes) {
    const parts = notes.split(' - ');
    const title = parts[0];
    const description = parts.length > 1 ? parts[1] : '';
    
    Swal.fire({
        title: 'Edit Holiday',
        html: `
            <div style="text-align: left;">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Holiday Title</label>
                    <input type="text" id="swal-title" class="swal2-input" value="${title}" style="width: 100%; margin: 0;">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Description</label>
                    <input type="text" id="swal-description" class="swal2-input" value="${description}" style="width: 100%; margin: 0;">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Update Holiday',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#ff9900',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
            const title = document.getElementById('swal-title').value;
            const description = document.getElementById('swal-description').value;
            
            if (!title) {
                Swal.showValidationMessage('Holiday title is required');
                return false;
            }
            
            return { title, description };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                @csrf
                <input type="hidden" name="update_holiday" value="${date}">
                <input type="hidden" name="title" value="${result.value.title}">
                <input type="hidden" name="description" value="${result.value.description}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function deleteHoliday(date) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will delete the holiday for all employees on this date.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d13212',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                @csrf
                <input type="hidden" name="delete_holiday" value="1">
                <input type="hidden" name="holiday_time" value="${date}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush