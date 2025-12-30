@extends('layouts.app')

@section('title', 'Employee Schedule')

@section('content')
<div class="container">
    <div style="display: grid; grid-template-columns: 1fr 3fr; gap: 20px;">
        <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
            <a href="{{ route('dashboard') }}" style="display: flex; align-items: center; padding: 12px 15px; margin-bottom: 8px; border-radius: 8px; text-decoration: none; color: #333;">
                <i class="fas fa-user" style="margin-right: 10px;"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('applications.index') }}" style="display: flex; align-items: center; padding: 12px 15px; margin-bottom: 8px; border-radius: 8px; text-decoration: none; color: #333;">
                <i class="fas fa-file-alt" style="margin-right: 10px;"></i>
                <span>Applications</span>
            </a>
            <a href="{{ route('applications.history') }}" style="display: flex; align-items: center; padding: 12px 15px; margin-bottom: 8px; border-radius: 8px; text-decoration: none; color: #333;">
                <i class="fas fa-history" style="margin-right: 10px;"></i>
                <span>History</span>
            </a>
            <a href="{{ route('schedule') }}" style="display: flex; align-items: center; padding: 12px 15px; margin-bottom: 8px; border-radius: 8px; text-decoration: none; color: #333; background-color: #e1ebff; font-weight: 600;">
                <i class="fas fa-calendar-alt" style="margin-right: 10px;"></i>
                <span>Schedule</span>
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" style="display: flex; align-items: center; padding: 12px 15px; margin-bottom: 8px; border-radius: 8px; background: none; border: none; width: 100%; text-align: left; color: #333; cursor: pointer;">
                    <i class="fas fa-sign-out-alt" style="margin-right: 10px;"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>

        <div style="background: white; border-radius: 10px; padding: 25px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
            <h2 style="margin-bottom: 20px;">Employee Schedule</h2>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <button id="prevMonth" style="background: #3498db; color: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer;">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div id="currentMonth" style="font-size: 20px; font-weight: 600; min-width: 180px; text-align: center;">{{ now()->format('F Y') }}</div>
                    <button id="nextMonth" style="background: #3498db; color: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer;">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            
            <div id="calendar" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px; margin-bottom: 20px;">
                <div style="text-align: center; padding: 10px; font-weight: 600; background: #f8f9fa; border-radius: 5px;">Mon</div>
                <div style="text-align: center; padding: 10px; font-weight: 600; background: #f8f9fa; border-radius: 5px;">Tue</div>
                <div style="text-align: center; padding: 10px; font-weight: 600; background: #f8f9fa; border-radius: 5px;">Wed</div>
                <div style="text-align: center; padding: 10px; font-weight: 600; background: #f8f9fa; border-radius: 5px;">Thu</div>
                <div style="text-align: center; padding: 10px; font-weight: 600; background: #f8f9fa; border-radius: 5px;">Fri</div>
                <div style="text-align: center; padding: 10px; font-weight: 600; background: #f8f9fa; border-radius: 5px;">Sat</div>
                <div style="text-align: center; padding: 10px; font-weight: 600; background: #f8f9fa; border-radius: 5px;">Sun</div>
                <div id="calendarDays"></div>
            </div>
            
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 5px;">
                    <div style="width: 15px; height: 15px; border-radius: 3px; background: #e7f6e9;"></div>
                    <span>Present</span>
                </div>
                <div style="display: flex; align-items: center; gap: 5px;">
                    <div style="width: 15px; height: 15px; border-radius: 3px; background: #fbebec;"></div>
                    <span>Absent</span>
                </div>
                <div style="display: flex; align-items: center; gap: 5px;">
                    <div style="width: 15px; height: 15px; border-radius: 3px; background: #f0f0f0;"></div>
                    <span>Weekend</span>
                </div>
            </div>
        </div>
    </div>
</div>

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
        $.post('{{ route("api.schedule.data") }}', {
            month: currentMonth,
            year: currentYear,
            _token: '{{ csrf_token() }}'
        }).done(function(data) {
            renderCalendar(data);
        }).fail(function() {
            $('#calendarDays').html('<div style="grid-column: span 7; text-align: center; color: red; padding: 20px;">Error loading calendar</div>');
        });
    }
    
    function renderCalendar(data) {
        const firstDay = new Date(currentYear, currentMonth - 1, 1).getDay();
        const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
        const today = new Date();
        const isCurrentMonth = currentMonth === today.getMonth() + 1 && currentYear === today.getFullYear();
        
        let html = '';
        
        // Empty days for first week
        for (let i = 1; i < firstDay; i++) {
            html += '<div style="background: #f5f5f5; opacity: 0.7; border-radius: 8px; padding: 10px; min-height: 80px;"></div>';
        }
        
        // Days of the month
        for (let day = 1; day <= daysInMonth; day++) {
            const date = currentYear + '-' + String(currentMonth).padStart(2, '0') + '-' + String(day).padStart(2, '0');
            const dayOfWeek = new Date(currentYear, currentMonth - 1, day).getDay();
            const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
            const isToday = day === today.getDate() && isCurrentMonth;
            
            let bgColor = '#f9f9f9';
            let status = 'No data';
            let statusColor = '#e74c3c';
            
            if (isWeekend) {
                bgColor = '#f0f0f0';
                status = 'Weekend';
                statusColor = '#95a5a6';
            }
            
            if (data[date]) {
                if (data[date].status === 'present') {
                    status = 'Present';
                    statusColor = '#2ecc71';
                    bgColor = '#e7f6e9';
                } else if (data[date].status === 'absent') {
                    status = 'Absent';
                    statusColor = '#e74c3c';
                    bgColor = '#fbebec';
                }
            }
            
            html += '<div style="background: ' + bgColor + '; border-radius: 8px; padding: 10px; min-height: 80px; cursor: pointer;">';
            html += '<div style="font-size: 16px; font-weight: 600; margin-bottom: 5px;' + (isToday ? 'background: #3498db; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center;' : '') + '">' + day + '</div>';
            html += '<span style="font-size: 11px; padding: 3px 6px; border-radius: 4px; color: ' + statusColor + ';">' + status + '</span>';
            html += '</div>';
        }
        
        $('#calendarDays').html(html);
    }
});
</script>
@endsection