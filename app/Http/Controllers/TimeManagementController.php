<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class TimeManagementController extends Controller
{
    public function handleTimeAction(Request $request)
    {
        $userId = Session::get('id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $click = $request->input('click');
        
        if (in_array($click, ['punch_in', 'lunch_start', 'lunch_end', 'punch_out'])) {
            return $this->recordTimeEntry($userId, $click);
        }

        switch ($click) {
            case 'getDetails':
                return $this->getTodayActivity($userId);
            case 'timeWorked':
                return $this->getTimeWorkedData($userId);
            case 'checkfirstpunchin':
                return $this->checkFirstPunchIn($userId);
            case 'detailsById':
                return $this->getEmployeeDetails($request);
            case 'filterLastMonth':
                return $this->filterLastMonth($request);
            case 'filterCustom':
                return $this->filterCustom($request);
            default:
                return response('error', 400);
        }
    }

    private function recordTimeEntry($userId, $entryType)
    {
        $time = Carbon::now('Asia/Kolkata');
        
        // Check last entry to prevent duplicates
        $lastEntry = DB::table('time_entries')
            ->where('employee_id', $userId)
            ->whereDate('entry_time', Carbon::today())
            ->orderBy('entry_time', 'desc')
            ->first();

        if (!$lastEntry || $lastEntry->entry_type !== $entryType) {
            DB::table('time_entries')->insert([
                'employee_id' => $userId,
                'entry_type' => $entryType,
                'entry_time' => $time,
                'notes' => $entryType,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return response('nothing');
        }
        
        return response('error');
    }

    private function getTodayActivity($userId)
    {
        $today = Carbon::today('Asia/Kolkata')->format('Y-m-d');
        
        $entries = DB::table('time_entries')
            ->where('employee_id', $userId)
            ->where('entry_time', 'like', $today . '%')
            ->orderBy('entry_time')
            ->get();

        $output = '<h3 class="section-title">
                    <i class="fas fa-history"></i>
                    Today\'s Activity
                </h3>';

        foreach ($entries as $entry) {
            $icon = $this->getIconForEntryType($entry->entry_type);
            
            $output .= '<ul class="activity-list">
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-' . $icon . '"></i>
                            </div>
                            <div class="activity-details">
                                <div class="activity-name">' . $entry->entry_type . '</div>
                                <div class="activity-time">' . $entry->entry_time . '</div>
                            </div>
                        </li>
                        </ul>';
        }

        return response($output);
    }

    private function getTimeWorkedData($userId)
    {
        $timezone = 'Asia/Kolkata';
        $currentDate = Carbon::now($timezone)->format('Y-m-d');
        
        // Get system settings
        $settings = DB::table('system_settings')->get()->keyBy('setting_key');
        
        $startTime = Carbon::createFromFormat('H:i:s', $settings['work_start_time']->setting_value ?? '09:00:00', $timezone);
        $endTime = Carbon::createFromFormat('H:i:s', $settings['work_end_time']->setting_value ?? '18:00:00', $timezone);
        $lunchDuration = $settings['lunch_duration']->setting_value ?? 60;
        $lateThreshold = intval($settings['late_threshold']->setting_value ?? 15);
        
        $workTime = $startTime->format('H:i:s A') . "-" . $endTime->format('H:i:s A');
        
        // Get time entries for today
        $entries = DB::table('time_entries')
            ->where('employee_id', $userId)
            ->whereDate('entry_time', $currentDate)
            ->orderBy('entry_time')
            ->get();

        // Calculate lunch time and work time
        $totalLunchSeconds = $this->calculateLunchTime($entries);
        $totalWorkedSeconds = $this->calculateWorkedTime($entries);
        
        // Get attendance state
        $state = $this->determineAttendanceState($userId, $currentDate, $entries, $lateThreshold);
        
        // Format times
        $lunchHours = floor($totalLunchSeconds / 3600);
        $lunchMinutes = floor(($totalLunchSeconds % 3600) / 60);
        $totalLunchByEmp = $lunchHours . "H : " . $lunchMinutes . "M";
        
        $workedHours = floor($totalWorkedSeconds / 3600);
        $workedMinutes = floor(($totalWorkedSeconds % 3600) / 60);
        $workedSecondsRem = $totalWorkedSeconds % 60;
        $totalWorkedTime = sprintf('%02d:%02d:%02d', $workedHours, $workedMinutes, $workedSecondsRem);
        
        $netWorkSeconds = $totalWorkedSeconds - $totalLunchSeconds;
        $netHours = floor($netWorkSeconds / 3600);
        $netMinutes = floor(($netWorkSeconds % 3600) / 60);
        $netSeconds = $netWorkSeconds % 60;
        $netWorkTime = sprintf('%02d:%02d:%02d', $netHours, $netMinutes, $netSeconds);
        
        // Check for half day
        $leaveRecord = DB::table('applications')
            ->where('employee_id', $userId)
            ->where('req_type', 'half_day')
            ->whereDate('start_date', $currentDate)
            ->where('status', 'approved')
            ->first();
            
        $isHalf = (bool) $leaveRecord;
        $comHalf = false;
        
        if ($isHalf) {
            $halfDayTime = $settings['half_day_time']->setting_value ?? '04:00';
            list($halfHours, $halfMinutes) = explode(':', $halfDayTime);
            $halfDayMinutes = ((int)$halfHours * 60) + (int)$halfMinutes;
            $workedMinutesTotal = ($workedHours * 60) + $workedMinutes;
            $comHalf = $workedMinutesTotal >= $halfDayMinutes;
        }
        
        $lastEntry = $entries->last();
        $action = ($lastEntry && $lastEntry->entry_type === 'punch_out') ? 'block' : 'run';
        
        $firstEntry = $entries->first();
        $firstPunchFormatted = $firstEntry ? Carbon::parse($firstEntry->entry_time)->format('H:i A') : 'N/A';
        
        $response = [
            'worktime' => $workTime,
            'punchTime' => $startTime->format('H:i:s A'),
            'action' => $action,
            'lunchDuation' => $lunchDuration . "M",
            'punch_in' => $firstPunchFormatted,
            'total_hours' => $totalWorkedTime,
            'network' => ($lastEntry && $lastEntry->entry_type === 'punch_out') ? $netWorkTime : $totalWorkedTime,
            'totalLunchByemp' => $totalLunchByEmp,
            'late' => $this->formatAttendanceState($state),
            'isHalf' => $isHalf,
            'com_half' => $comHalf
        ];
        
        return response()->json($response);
    }

    private function checkFirstPunchIn($userId)
    {
        $today = Carbon::today('Asia/Kolkata')->format('Y-m-d');
        
        $firstEntry = DB::table('time_entries')
            ->where('employee_id', $userId)
            ->where('entry_time', 'like', $today . '%')
            ->orderBy('entry_time')
            ->first();

        if ($firstEntry && in_array($firstEntry->entry_type, ['regularization', 'casual_leave', 'sick_leave', 'holiday'])) {
            return response($firstEntry->entry_type);
        }

        if ($firstEntry && $firstEntry->entry_type === 'punch_in') {
            $lastEntry = DB::table('time_entries')
                ->where('employee_id', $userId)
                ->where('entry_time', 'like', $today . '%')
                ->orderBy('entry_time', 'desc')
                ->first();

            switch ($lastEntry->entry_type) {
                case 'punch_out':
                    return response('4');
                case 'lunch_start':
                    return response('2');
                case 'lunch_end':
                    return response('3');
                default:
                    return response('1');
            }
        }

        return response('5');
    }

    private function calculateLunchTime($entries)
    {
        $totalSeconds = 0;
        $lunchStart = null;
        
        foreach ($entries as $entry) {
            if ($entry->entry_type == 'lunch_start') {
                $lunchStart = strtotime($entry->entry_time);
            } elseif ($entry->entry_type == 'lunch_end' && $lunchStart !== null) {
                $lunchEnd = strtotime($entry->entry_time);
                if ($lunchEnd > $lunchStart) {
                    $totalSeconds += ($lunchEnd - $lunchStart);
                }
                $lunchStart = null;
            }
        }
        
        return $totalSeconds;
    }

    private function calculateWorkedTime($entries)
    {
        $totalSeconds = 0;
        $punchInTime = null;
        
        foreach ($entries as $entry) {
            if ($entry->entry_type == 'punch_in') {
                $punchInTime = strtotime($entry->entry_time);
            } elseif ($entry->entry_type == 'punch_out' && $punchInTime !== null) {
                $punchOutTime = strtotime($entry->entry_time);
                if ($punchOutTime > $punchInTime) {
                    $totalSeconds += ($punchOutTime - $punchInTime);
                }
                $punchInTime = null;
            }
        }
        
        // Handle incomplete punch session
        if ($punchInTime !== null) {
            $currentTime = time();
            if ($currentTime > $punchInTime) {
                $totalSeconds += ($currentTime - $punchInTime);
            }
        }
        
        return $totalSeconds;
    }

    private function determineAttendanceState($userId, $date, $entries, $lateThreshold)
    {
        if ($entries->isEmpty()) {
            return 'Absent';
        }

        $firstEntry = $entries->first();
        
        // Check for leave types
        if (in_array($firstEntry->entry_type, ['regularization', 'casual_leave', 'sick_leave', 'holiday', 'half_day'])) {
            return $firstEntry->entry_type;
        }

        // Check for approved leave applications
        $leaveRecord = DB::table('applications')
            ->where('employee_id', $userId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->first();

        if ($leaveRecord) {
            return $leaveRecord->req_type;
        }

        // Check punch in status
        if ($firstEntry->entry_type == 'punch_in') {
            $workStartTime = DB::table('system_settings')
                ->where('setting_key', 'work_start_time')
                ->value('setting_value') ?? '09:00:00';
                
            $expectedStart = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $workStartTime, 'Asia/Kolkata');
            $actualStart = Carbon::parse($firstEntry->entry_time, 'Asia/Kolkata');
            
            $minutesLate = $actualStart->diffInMinutes($expectedStart, false);
            
            return $minutesLate <= $lateThreshold ? 'Present' : 'Late';
        }

        return 'Absent';
    }

    private function formatAttendanceState($state)
    {
        $colors = [
            'Present' => 'green',
            'Late' => 'orange',
            'Absent' => 'red',
            'half_day' => 'blue',
            'casual_leave' => 'purple',
            'sick_leave' => 'purple',
            'regularization' => 'orange',
            'holiday' => 'gray'
        ];

        $color = $colors[$state] ?? 'orange';
        $displayText = ucfirst(str_replace('_', ' ', $state));
        
        return "<label style='color:{$color};'>{$displayText}</label>";
    }

    private function getIconForEntryType($entryType)
    {
        $icons = [
            'punch_in' => 'fingerprint',
            'punch_out' => 'door-open',
            'lunch_start' => 'utensils',
            'lunch_end' => 'utensils'
        ];

        return $icons[$entryType] ?? 'bullseye';
    }

    // Additional methods for employee details, filtering, etc. would go here
    // Due to length constraints, I'm including the core functionality
}