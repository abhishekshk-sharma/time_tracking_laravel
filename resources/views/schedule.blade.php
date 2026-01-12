@extends('layouts.user')

@section('page-title', 'Schedule')
@section('page-subtitle', 'View your attendance calendar and schedule')

@push('page-styles')
<style>
    .calendar-controls {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .current-month-display {
        margin: 0;
        min-width: 200px;
        text-align: center;
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-800);
    }
    
    .btn-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }
    
    .calendar-container {
        margin: 1.5rem 0;
    }
    
    .calendar-days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background: var(--gray-200);
        border-radius: var(--radius);
        overflow: scroll;
        margin-top: 1px;
    }

    .calendar-days::-webkit-scrollbar {
        display: none;
    }
    
    .calendar-day {
        background: white;
        padding: 1rem;
        min-height: 80px;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid transparent;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    
    .calendar-day:hover {
        background: var(--gray-50);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    
    .calendar-day.today {
        border-color: var(--primary);
        box-shadow: 0 0 0 1px var(--primary);
    }
    
    .calendar-day.present { 
        background: linear-gradient(135deg, #dcfce7, #bbf7d0); 
        color: #166534;
    }
    
    .calendar-day.absent { 
        background: linear-gradient(135deg, #fef2f2, #fecaca); 
        color: #dc2626;
    }
    
    .calendar-day.weekend { 
        background: linear-gradient(135deg, var(--gray-100), var(--gray-200)); 
        color: var(--gray-600);
    }
    
    .calendar-day.leave {
        background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
        color: #3730a3;
    }
    
    .calendar-legend {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--gray-200);
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .legend-color {
        width: 16px;
        height: 16px;
        border-radius: var(--radius-sm);
        box-shadow: var(--shadow-sm);
    }
    
    .legend-color.present {
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
    }
    
    .legend-color.absent {
        background: linear-gradient(135deg, #fef2f2, #fecaca);
    }
    
    .legend-color.weekend {
        background: linear-gradient(135deg, var(--gray-100), var(--gray-200));
    }
    
    .legend-color.leave {
        background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
    }
    
    .day-number {
        font-weight: 600;
        font-size: 1rem;
    }
    
    .day-status {
        font-size: 0.75rem;
        font-weight: 500;
        opacity: 0.8;
    }
</style>
@endpush

@section('page-content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-calendar-alt"></i>
            {{ Auth::user()->full_name }} - Monthly Schedule
        </h2>
        <div class="calendar-controls">
            <button id="prevMonth" class="btn btn-secondary btn-icon">
                <i class="fas fa-chevron-left"></i>
            </button>
            <h3 id="currentMonth" class="current-month-display">{{ now()->format('F Y') }}</h3>
            <button id="nextMonth" class="btn btn-secondary btn-icon">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
    
    <div class="calendar-container">
        <div class="calendar-grid" id="calendar">
            <div class="calendar-header">Mon</div>
            <div class="calendar-header">Tue</div>
            <div class="calendar-header">Wed</div>
            <div class="calendar-header">Thu</div>
            <div class="calendar-header">Fri</div>
            <div class="calendar-header">Sat</div>
            <div class="calendar-header">Sun</div>
        </div>
        
        <div id="calendarDays" class="calendar-days"></div>
    </div>
    
    <div class="calendar-legend">
        <div class="legend-item">
            <div class="legend-color present"></div>
            <span>Present</span>
        </div>
        <div class="legend-item">
            <div class="legend-color absent"></div>
            <span>Absent</span>
        </div>
        <div class="legend-item">
            <div class="legend-color weekend"></div>
            <span>Weekend</span>
        </div>
        <div class="legend-item">
            <div class="legend-color leave"></div>
            <span>Leave</span>
        </div>
    </div>
</div>
@endsection

@push('page-scripts')
<script>
$(document).ready(function() {
    let currentMonth = new Date().getMonth() + 1;
    let currentYear = new Date().getFullYear();
    
    loadCalendar();
    
    $('#prevMonth').click(function() {
        currentMonth--;
        if (currentMonth < 1) {
            currentMonth = 12;
            currentYear--;
        }
        updateCalendar();
    });
    
    $('#nextMonth').click(function() {
        currentMonth++;
        if (currentMonth > 12) {
            currentMonth = 1;
            currentYear++;
        }
        updateCalendar();
    });
    
    function updateCalendar() {
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $('#currentMonth').text(months[currentMonth - 1] + ' ' + currentYear);
        loadCalendar();
    }
    
    function loadCalendar() {
        $('#calendarDays').html('<div style="grid-column: span 7; text-align: center; padding: 20px;">Loading...</div>');
        
        $.ajax({
            url: '{{ route("api.schedule.data") }}',
            type: 'POST',
            data: {
                month: currentMonth,
                year: currentYear,
                _token: '{{ csrf_token() }}'
            },
            success: function(data) {
                renderCalendar(data);
            },
            error: function(xhr, status, error) {
                console.error('Error loading calendar:', error);
                $('#calendarDays').html('<div style="grid-column: span 7; text-align: center; color: red; padding: 20px;">Error loading calendar: ' + error + '</div>');
            }
        });
    }
    
    function renderCalendar(data) {
        const firstDay = new Date(currentYear, currentMonth - 1, 1).getDay();
        const adjustedFirstDay = firstDay === 0 ? 6 : firstDay - 1; // Convert Sunday=0 to Monday=0
        const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
        const today = new Date();
        
        let html = '';
        
        // Empty days for first week
        for (let i = 0; i < adjustedFirstDay; i++) {
            html += '<div class="calendar-day" style="opacity: 0.3;"></div>';
        }
        
        // Days of the month
        for (let day = 1; day <= daysInMonth; day++) {
            const date = currentYear + '-' + String(currentMonth).padStart(2, '0') + '-' + String(day).padStart(2, '0');
            const dayOfWeek = new Date(currentYear, currentMonth - 1, day).getDay();
            const isToday = day === today.getDate() && currentMonth === today.getMonth() + 1 && currentYear === today.getFullYear();
            
            let dayClass = 'calendar-day';
            let status = 'No data';
            
            if (data && data[date]) {
                if (data[date].status === 'future') {
                    // Future dates - no status shown
                    status = '';
                } else if (data[date].status === 'present') {
                    status = 'Present';
                    dayClass += ' present';
                } else if (data[date].status === 'absent') {
                    status = 'Absent';
                    dayClass += ' absent';
                } else if (data[date].status === 'weekend') {
                    status = 'Weekend';
                    dayClass += ' weekend';
                } else {
                    status = data[date].status;
                    dayClass += ' leave';
                }
            } else {
                // Check if it's a future date
                const currentDate = new Date(currentYear, currentMonth - 1, day);
                const todayDate = new Date();
                todayDate.setHours(0, 0, 0, 0);
                currentDate.setHours(0, 0, 0, 0);
                
                if (currentDate > todayDate) {
                    status = ''; // No status for future dates
                }
            }
            
            if (isToday) {
                dayClass += ' today';
            }
            
            html += '<div class="' + dayClass + '" onclick="showDayDetails(\'' + date + '\')">';
            html += '<div class="day-number">' + day + '</div>';
            html += '<div class="day-status">' + status + '</div>';
            html += '</div>';
        }
        
        $('#calendarDays').html(html);
    }
    
    window.showDayDetails = function(date) {
        $.ajax({
            url: '{{ route("api.schedule.details") }}',
            type: 'POST',
            data: {
                date: date,
                _token: '{{ csrf_token() }}'
            },
            success: function(data) {
                let entriesHtml = '';
                if (data.entries && data.entries.length > 0) {
                    entriesHtml = '<div style="text-align: left; margin-top: 15px;">';
                    entriesHtml += '<h4 style="margin-bottom: 10px; color: #374151;">Time Entries:</h4>';
                    console.log(data);
                    data.entries.forEach(function(entry) {
                        const iconMap = {
                            'punch_in': 'fa-sign-in-alt',
                            'punch_out': 'fa-sign-out-alt',
                            'lunch_start': 'fa-utensils',
                            'lunch_end': 'fa-utensils',
                            'holiday': 'fa-calendar-day'
                        };
                        const icon = iconMap[entry.entry_type] || 'fa-clock';
                        
                        let timeDisplay = '';
                        if (entry.entry_type === 'holiday') {
                            timeDisplay = entry.notes ;
                        } else {
                            const time = new Date(entry.entry_time);
                            if (!isNaN(time.getTime())) {
                                timeDisplay = time.toLocaleTimeString('en-US', {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    hour12: true
                                });
                            } else {
                                timeDisplay = entry.entry_time || 'N/A';
                            }
                        }
                        
                        entriesHtml += '<div style="display: flex; align-items: center; margin-bottom: 8px; padding: 8px; background: #f9fafb; border-radius: 6px;">';
                        entriesHtml += '<i class="fas ' + icon + '" style="margin-right: 10px; color: #6b7280; width: 16px;"></i>';
                        entriesHtml += '<span style="font-weight: 500; text-transform: capitalize; margin-right: 10px;">' + entry.entry_type.replace('_', ' ') + ':</span>';
                        entriesHtml += '<span style="color: #374151;">' + timeDisplay + '</span>';
                        entriesHtml += '</div>';
                    });
                    entriesHtml += '</div>';
                } else {
                    entriesHtml = '<div style="text-align: center; margin-top: 15px; color: #6b7280;">No time entries found</div>';
                }
                
                // Remove separate holiday note section since it's now in entries
                
                const statusColors = {
                    'present': '#059669',
                    'absent': '#dc2626',
                    'weekend': '#6b7280',
                    'leave': '#7c3aed',
                    'holiday': '#f59e0b'
                };
                const statusColor = statusColors[data.status.toLowerCase()] || '#6b7280';
                
                Swal.fire({
                    title: '<strong>Day Details</strong>',
                    html: '<div style="text-align: center;">' +
                          '<div style="font-size: 18px; margin-bottom: 10px; color: #374151;">' + new Date(date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) + '</div>' +
                          '<div style="display: inline-block; padding: 6px 12px; background: ' + statusColor + '; color: white; border-radius: 20px; font-weight: 500; text-transform: capitalize;">' + data.status + '</div>' +
                          '</div>' + entriesHtml,
                    width: 500,
                    padding: '2rem',
                    showCloseButton: true,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'day-details-modal'
                    }
                });
            },
            error: function() {
                Swal.fire({
                    title: 'Error',
                    text: 'Error loading day details',
                    icon: 'error',
                    confirmButtonColor: '#dc2626'
                });
            }
        });
    };
});
</script>
@endpush