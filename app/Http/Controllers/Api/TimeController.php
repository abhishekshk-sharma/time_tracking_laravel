<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TimeEntry;
use App\Models\SystemSetting;
use App\Models\Application;
use App\Models\Wfh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TimeController extends Controller
{
    public function handleTimeAction(Request $request)
    {
        $userid = Auth::user()->emp_id;
        $click = $request->input('click');

        if (!in_array($click, ['punch_in', 'lunch_start', 'lunch_end', 'punch_out'])) {
            return response('error');
        }

        // Use database transaction to prevent race conditions
        return \DB::transaction(function () use ($userid, $click) {
            $time = Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s');
            $today = Carbon::today()->format('Y-m-d');

            // Lock the table to prevent concurrent access
            $lastentry = TimeEntry::where('employee_id', $userid)
                ->whereDate('entry_time', $today)
                ->orderBy('entry_time', 'desc')
                ->lockForUpdate() // This prevents race conditions
                ->first();

            // Check for duplicate within last 30 seconds (network delay protection)
            $recentDuplicate = TimeEntry::where('employee_id', $userid)
                ->where('entry_type', $click)
                ->where('entry_time', '>=', Carbon::now('Asia/Kolkata')->subSeconds(30))
                ->exists();
                
            if ($recentDuplicate) {
                return response('error'); // Duplicate within 30 seconds
            }

            // Robust validation to prevent duplicate entries
            if ($lastentry) {
                // Prevent same action twice
                if ($lastentry->entry_type === $click) {
                    return response('error');
                }
                
                // Validate proper sequence
                if ($click === 'punch_out' && !in_array($lastentry->entry_type, ['punch_in', 'lunch_end'])) {
                    return response('error');
                }
                
                if ($click === 'lunch_start' && $lastentry->entry_type !== 'punch_in') {
                    return response('error');
                }
                
                if ($click === 'lunch_end' && $lastentry->entry_type !== 'lunch_start') {
                    return response('error');
                }
                
                if ($click === 'punch_in' && in_array($lastentry->entry_type, ['punch_in', 'lunch_start'])) {
                    return response('error');
                }
            } else {
                // First entry of the day must be punch_in
                if ($click !== 'punch_in') {
                    return response('error');
                }
            }

            try {
                $timeEntry = TimeEntry::create([
                    'employee_id' => $userid,
                    'entry_type' => $click,
                    'entry_time' => $time,
                    'notes' => $click
                ]);

                if ($timeEntry) {
                    return response('nothing');
                }
            } catch (\Exception $e) {
                \Log::error('Time entry creation failed: ' . $e->getMessage());
                return response('error');
            }

            return response('error');
        });
    }

    public function getDetails(Request $request)
    {
        $userid = Auth::user()->emp_id;
        $time = Carbon::now('Asia/Kolkata')->format('Y-m-d');
        
        $entries = TimeEntry::where('employee_id', $userid)
            ->where('entry_time', 'like', $time . '%')
            ->orderBy('entry_time')
            ->get();

        $output = '<h3 class="section-title">
                    <i class="fas fa-history"></i>
                    Today\'s Activity
                </h3>';

        foreach ($entries as $row) {
            $icon = "bullseye";
            switch ($row->entry_type) {
                case 'punch_in':
                    $icon = "fingerprint";
                    break;
                case 'lunch_start':
                case 'lunch_end':
                    $icon = "utensils";
                    break;
                case 'punch_out':
                    $icon = "door-open";
                    break;
            }

            $output .= '<ul class="activity-list">
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-' . $icon . '"></i>
                            </div>
                            <div class="activity-details">
                                <div class="activity-name">' . $row->entry_type . '</div>
                                <div class="activity-time">' . $row->entry_time . '</div>
                            </div>
                        </li>
                        </ul>';
        }

        return response($output);
    }

    public function timeWorked(Request $request)
    {
        try {
            $userid = Auth::user()->emp_id;
            $timezone = new \DateTimeZone("Asia/Kolkata");
            $currentDate = Carbon::now($timezone)->format('Y-m-d');

            // Get system settings with defaults - handle missing settings gracefully
            $defaultSettings = [
                'work_start_time' => '09:00:00',
                'work_end_time' => '18:00:00', 
                'lunch_duration' => '60',
                'late_threshold' => '15'
            ];
            
            $systemSettings = [];
            try {
                $settings = SystemSetting::all();
                foreach ($settings as $row) {
                    $systemSettings[$row->setting_key] = $row->setting_value;
                }
            } catch (\Exception $e) {
                \Log::warning('System settings table not accessible, using defaults: ' . $e->getMessage());
            }
            
            // Merge with defaults
            $systemSettings = array_merge($defaultSettings, $systemSettings);
            
            $checkstart = $systemSettings['work_start_time'];
            $checkend = $systemSettings['work_end_time'];
            $lunchduration = intval($systemSettings['lunch_duration']);
            $late = intval($systemSettings['late_threshold']);
            
            // Handle different time formats safely
            try {
                if (strlen($checkstart) == 5) {
                    $checkstart .= ':00'; // Add seconds if missing
                }
                if (strlen($checkend) == 5) {
                    $checkend .= ':00'; // Add seconds if missing
                }
                
                $startTime = Carbon::createFromFormat('H:i:s', $checkstart, $timezone);
                $endTime = Carbon::createFromFormat('H:i:s', $checkend, $timezone);
            } catch (\Exception $e) {
                // Fallback to default times if parsing fails
                $startTime = Carbon::createFromFormat('H:i:s', '09:00:00', $timezone);
                $endTime = Carbon::createFromFormat('H:i:s', '18:00:00', $timezone);
                $checkstart = '09:00:00';
                $checkend = '18:00:00';
            }
            
            $combinedDateTime = $currentDate . ' ' . $checkstart;
            $checkstart11 = Carbon::parse($combinedDateTime, $timezone);
            $checkstartTimestamp = $checkstart11->timestamp;

            $workTime = $startTime->format('H:i:s A') . "-" . $endTime->format('H:i:s A');

            // Get all time entries for the employee on current date
            $entries = TimeEntry::where('employee_id', $userid)
                ->whereDate('entry_time', $currentDate)
                ->orderBy('entry_time', 'asc')
                ->get();

            // Calculate lunch time - handle multiple lunch sessions
            $totalLunchSeconds = 0;
            $lunchStartTime = null;
            
            foreach ($entries as $entry) {
                if ($entry->entry_type == 'lunch_start') {
                    $lunchStartTime = Carbon::parse($entry->entry_time, $timezone);
                } elseif ($entry->entry_type == 'lunch_end' && $lunchStartTime !== null) {
                    $lunchEndTime = Carbon::parse($entry->entry_time, $timezone);
                    if ($lunchEndTime > $lunchStartTime) {
                        $totalLunchSeconds += $lunchEndTime->diffInSeconds($lunchStartTime);
                    }
                    $lunchStartTime = null;
                }
            }
            
            // Handle ongoing lunch (lunch_start without lunch_end)
            if ($lunchStartTime !== null) {
                $currentTime = Carbon::now($timezone);
                if ($currentTime > $lunchStartTime) {
                    $totalLunchSeconds += $currentTime->diffInSeconds($lunchStartTime);
                }
            }
            
            $lunchHours = floor($totalLunchSeconds / 3600);
            $lunchMinutes = floor(($totalLunchSeconds % 3600) / 60);
            $totallunchByemp = sprintf('%dH %dM', $lunchHours, $lunchMinutes);

            // Calculate total work time - handle multiple work sessions
            $totalWorkSeconds = 0;
            $workStartTime = null;
            $isCurrentlyWorking = false;
            
            foreach ($entries as $entry) {
                if ($entry->entry_type == 'punch_in') {
                    $workStartTime = Carbon::parse($entry->entry_time, $timezone);
                    $isCurrentlyWorking = true;
                } elseif ($entry->entry_type == 'punch_out' && $workStartTime !== null) {
                    $workEndTime = Carbon::parse($entry->entry_time, $timezone);
                    if ($workEndTime > $workStartTime) {
                        $totalWorkSeconds += $workEndTime->diffInSeconds($workStartTime);
                    }
                    $workStartTime = null;
                    $isCurrentlyWorking = false;
                }
            }
            
            // Handle ongoing work session (punch_in without punch_out)
            if ($workStartTime !== null && $isCurrentlyWorking) {
                $currentTime = Carbon::now($timezone);
                if ($currentTime > $workStartTime) {
                    $totalWorkSeconds += $currentTime->diffInSeconds($workStartTime);
                }
            }
            
            $workHours = floor($totalWorkSeconds / 3600);
            $workMinutes = floor(($totalWorkSeconds % 3600) / 60);
            $totalWorkedTime = sprintf('%dH %dM', $workHours, $workMinutes);
            
            // Calculate net work time (work time - lunch time)
            $netWorkSeconds = $totalWorkSeconds - $totalLunchSeconds;
            $netWorkSeconds = max(0, $netWorkSeconds); // Ensure non-negative
            $netHours = floor($netWorkSeconds / 3600);
            $netMinutes = floor(($netWorkSeconds % 3600) / 60);
            $netWorkTime = sprintf('%dH %dM', $netHours, $netMinutes);

            // Check for leave/holiday/regularization
            $leaveRecord = Application::where('employee_id', $userid)
                ->whereDate('created_at', $currentDate)
                ->where('status', 'approved')
                ->orderBy('created_at', 'asc')
                ->first();

            // Determine attendance state
            $state = "Absent";
            $firstEntry = $entries->first();
            $lastEntry = $entries->last();

            if ($entries->count() > 0) {
                // Check if employee is on leave/holiday
                if ($firstEntry && in_array($firstEntry->entry_type, ["regularization", "casual_leave", "sick_leave", "holiday"])) {
                    $state = $firstEntry->entry_type;
                }
                // Check if there's an approved leave record
                elseif ($leaveRecord && in_array($leaveRecord->req_type, ["regularization", "casual_leave", "sick_leave", "holiday", "half_day"])) {
                    $state = $leaveRecord->req_type;
                }
                // Check if employee punched in
                elseif ($firstEntry && $firstEntry->entry_type == "punch_in") {
                    $firstPunchTime = Carbon::parse($firstEntry->entry_time, $timezone);
                    $firstPunchTimestamp = $firstPunchTime->timestamp;

                    $minutesLate = ($firstPunchTimestamp - $checkstartTimestamp) / 60;

                    if ($minutesLate <= $late) {
                        $state = "Present";
                    } else {
                        $state = "Late";
                    }
                }
                elseif ($leaveRecord && $leaveRecord->req_type == "half_day") {
                    $state = "half_day";
                }
            }

            // Format state display
            switch ($state) {
                case "Present":
                    $lateresult = "<label style='color:green;'>$state</label>";
                    break;
                case "Late":
                    $lateresult = "<label style='color:orange;'>$state</label>";
                    break;
                case "Absent":
                    $lateresult = "<label style='color:red;'>$state</label>";
                    break;
                case "half_day":
                    $lateresult = "<label style='color:blue;'>Half Day</label>";
                    break;
                case "casual_leave":
                    $lateresult = "<label style='color:purple;'>Casual Leave</label>";
                    break;
                case "sick_leave":
                    $lateresult = "<label style='color:purple;'>Sick Leave</label>";
                    break;
                case "regularization":
                    $lateresult = "<label style='color:orange;'>Regularization</label>";
                    break;
                case "holiday":
                    $lateresult = "<label style='color:gray;'>Holiday</label>";
                    break;
                default:
                    $lateresult = "<label style='color:orange;'>$state</label>";
            }



            // Calculate total defined hours
            $starttime1 = Carbon::createFromFormat('H:i', substr($checkstart, 0, 5));
            $endtime1 = Carbon::createFromFormat('H:i', substr($checkend, 0, 5));
            $interval1 = $starttime1->diff($endtime1);
            $totaldefinedhours = $interval1->h + ($interval1->i / 60);

            // Check for half-day
            $is_half = false;
            $com_half = false;

            if ($leaveRecord && $leaveRecord->req_type == "half_day" && $leaveRecord->status == "approved") {
                $is_half = true;

                $getHalftime = SystemSetting::where('setting_key', 'half_day_time')->first();
                if ($getHalftime) {
                    list($halfHours, $halfMinutes) = explode(':', $getHalftime->setting_value);
                    $halfDayMinutes = ((int)$halfHours * 60) + (int)$halfMinutes;
                    $workedMinutesTotal = ($netHours * 60) + $netMinutes;

                    if ($workedMinutesTotal >= $halfDayMinutes) {
                        $com_half = true;
                    }
                }
            }

            $action = "run";
            if ($lastEntry && $lastEntry->entry_type === 'punch_out') {
                if ($netHours >= $totaldefinedhours) {
                    $action = "block";
                } else {
                    $action = "run";
                }
            }

            // Determine which time to show as "network" (net work time)
            $displayTime = $netWorkTime;
            if ($isCurrentlyWorking && $workStartTime !== null) {
                // Still working, show current net work time
                $displayTime = $netWorkTime;
            } elseif ($lastEntry && $lastEntry->entry_type === 'punch_out') {
                // Finished work, show final net time
                $displayTime = $netWorkTime;
            } else {
                // No work recorded yet or other states
                $displayTime = '0H 0M';
            }

            $firstPunchFormatted = $firstEntry ? Carbon::parse($firstEntry->entry_time, $timezone)->format('H:i A') : 'N/A';

            return response()->json([
                'worktime' => $workTime,
                'punchTime' => $startTime->format('H:i:s A'),
                'action' => $action,
                'lunchDuation' => $lunchduration . "M",
                'punch_in' => $firstPunchFormatted,
                'total_hours' => $totalWorkedTime,
                'network' => $displayTime,
                'totalLunchByemp' => $totallunchByemp,
                'late' => $lateresult,
                'isHalf' => $is_half,
                'com_half' => $com_half
            ]);
        } catch (\Exception $e) {
            \Log::error('TimeWorked API Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'worktime' => '09:00:00 AM - 06:00:00 PM',
                'punchTime' => '09:00:00 AM',
                'action' => 'run',
                'lunchDuation' => '60M',
                'punch_in' => 'N/A',
                'total_hours' => '0H 0M',
                'network' => '0H 0M',
                'totalLunchByemp' => '0H 0M',
                'late' => '<label style="color:red;">Not Available</label>',
                'isHalf' => false,
                'com_half' => false,
                'debug_error' => $e->getMessage()
            ], 200);
        }
    }

    public function checkFirstPunchIn(Request $request)
    {
        $userid = Auth::user()->emp_id;
        $time = Carbon::now('Asia/Kolkata')->format('Y-m-d');

        $firstEntry = TimeEntry::where('employee_id', $userid)
            ->where('entry_time', 'like', $time . '%')
            ->orderBy('entry_time', 'asc')
            ->first();

        if ($firstEntry && in_array($firstEntry->entry_type, ["regularization", "casual_leave", "sick_leave", "holiday"])) {
            return response($firstEntry->entry_type);
        } else if ($firstEntry && $firstEntry->entry_type === 'punch_in') {
            $lastEntry = TimeEntry::where('employee_id', $userid)
                ->where('entry_time', 'like', $time . '%')
                ->orderBy('entry_time', 'desc')
                ->first();

            if ($lastEntry->entry_type == "punch_out") {
                return response('4');
            } else if ($lastEntry->entry_type == "lunch_start") {
                return response('2');
            } else if ($lastEntry->entry_type == "lunch_end") {
                return response('3');
            } else if (in_array($lastEntry->entry_type, ["regularization", "casual_leave", "sick_leave", "holiday"])) {
                return response($lastEntry->entry_type);
            } else {
                return response('1');
            }
        } else {
            if ($firstEntry && $firstEntry->entry_type == "half_day") {
                $lastEntry = TimeEntry::where('employee_id', $userid)
                    ->where('entry_time', 'like', $time . '%')
                    ->orderBy('entry_time', 'desc')
                    ->first();

                if ($lastEntry->entry_type == "punch_in") {
                    return response('1');
                } else if ($lastEntry->entry_type == "punch_out") {
                    return response('4');
                } else if ($lastEntry->entry_type == "lunch_start") {
                    return response('2');
                } else if ($lastEntry->entry_type == "lunch_end") {
                    return response('3');
                } else if (in_array($lastEntry->entry_type, ["regularization", "casual_leave", "sick_leave", "holiday"])) {
                    return response($lastEntry->entry_type);
                } else {
                    return response('1');
                }
            } else {
                return response('5');
            }
        }
    }
    
    public function detailsById(Request $request)
    {
        $userid = $request->input('id');
        
        // Get all entries for current month
        $entries = TimeEntry::where('employee_id', $userid)
            ->whereMonth('entry_time', now()->month)
            ->whereYear('entry_time', now()->year)
            ->orderBy('entry_time', 'asc')
            ->get();
            
        if ($entries->isEmpty()) {
            return response('<tr><td colspan="7" style="color:#666; text-align:center; padding: 20px;">No Data Found for Current Month!</td></tr>');
        }
        
        // Group entries by date
        $groupedEntries = $entries->groupBy(function($entry) {
            return Carbon::parse($entry->entry_time)->format('Y-m-d');
        });
        
        $output = '';
        foreach ($groupedEntries as $date => $dayEntries) {
            $punchin = $lunchstart = $lunchend = $punchout = null;
            $state = "Absent";
            
            foreach ($dayEntries as $entry) {
                switch ($entry->entry_type) {
                    case 'punch_in':
                        $punchin = $entry->entry_time;
                        $state = "Present";
                        break;
                    case 'punch_out':
                        $punchout = $entry->entry_time;
                        break;
                    case 'lunch_start':
                        $lunchstart = $entry->entry_time;
                        break;
                    case 'lunch_end':
                        $lunchend = $entry->entry_time;
                        break;
                    case 'half_day':
                    case 'casual_leave':
                    case 'sick_leave':
                    case 'holiday':
                        $state = ucfirst(str_replace('_', ' ', $entry->entry_type));
                        break;
                }
            }
            
            // Calculate total hours worked
            $totalHours = '00:00:00';
            if ($punchin && $punchout) {
                // Simple calculation without timezone conversion
                $punchInTime = new \DateTime($punchin);
                $punchOutTime = new \DateTime($punchout);
                
                // Calculate total time in minutes
                $interval = $punchInTime->diff($punchOutTime);
                $totalMinutes = ($interval->h * 60) + $interval->i;
                
                // Subtract lunch time if both lunch start and end exist
                if ($lunchstart && $lunchend) {
                    $lunchStartTime = new \DateTime($lunchstart);
                    $lunchEndTime = new \DateTime($lunchend);
                    $lunchInterval = $lunchStartTime->diff($lunchEndTime);
                    $lunchMinutes = ($lunchInterval->h * 60) + $lunchInterval->i;
                    $totalMinutes -= $lunchMinutes;
                }
                
                // Ensure non-negative result
                $totalMinutes = max(0, $totalMinutes);
                $hours = floor($totalMinutes / 60);
                $minutes = $totalMinutes % 60;
                $totalHours = sprintf('%02d:%02d:00', $hours, $minutes);
            }
            
            // Format the row
            $output .= '<tr style="border-bottom: 1px solid #eee;">';
            $output .= '<td style="padding: 12px;">'.Carbon::parse($date)->format('M d, Y').'</td>';
            $output .= '<td style="padding: 12px;">'.($punchin ? Carbon::parse($punchin)->format('H:i:s') : '-').'</td>';
            $output .= '<td style="padding: 12px;">'.($lunchstart ? Carbon::parse($lunchstart)->format('H:i:s') : '-').'</td>';
            $output .= '<td style="padding: 12px;">'.($lunchend ? Carbon::parse($lunchend)->format('H:i:s') : '-').'</td>';
            $output .= '<td style="padding: 12px;">'.($punchout ? Carbon::parse($punchout)->format('H:i:s') : '-').'</td>';
            $output .= '<td style="padding: 12px; font-weight: 600;">'.$totalHours.'</td>';
            $output .= '<td style="padding: 12px;"><span class="status-badge status-'.$state.'">'.$state.'</span></td>';
            $output .= '</tr>';
        }
        
        return response($output);
    }
    
    public function filterTime(Request $request)
    {
        $click = $request->input('click');
        $userid = $request->input('id');
        
        if ($click == "filterLastMonth") {
            $entries = TimeEntry::where('employee_id', $userid)
                ->whereMonth('entry_time', now()->subMonth()->month)
                ->whereYear('entry_time', now()->subMonth()->year)
                ->orderBy('entry_time', 'asc')
                ->get();
        } elseif ($click == "filterCustom") {
            $from = $request->input('from');
            $to = $request->input('to');
            
            $entries = TimeEntry::where('employee_id', $userid)
                ->whereBetween('entry_time', [$from, Carbon::parse($to)->addDay()])
                ->orderBy('entry_time', 'asc')
                ->get();
        } else {
            return response('Invalid filter', 400);
        }
        
        if ($entries->isEmpty()) {
            return response('<tr><td colspan="7" style="color:#666; text-align:center; padding: 20px;">No Data Found!</td></tr>');
        }
        
        // Group entries by date
        $groupedEntries = $entries->groupBy(function($entry) {
            return Carbon::parse($entry->entry_time)->format('Y-m-d');
        });
        
        $output = '';
        foreach ($groupedEntries as $date => $dayEntries) {
            $punchin = $lunchstart = $lunchend = $punchout = null;
            $state = "Absent";
            
            foreach ($dayEntries as $entry) {
                switch ($entry->entry_type) {
                    case 'punch_in':
                        $punchin = $entry->entry_time;
                        $state = "Present";
                        break;
                    case 'punch_out':
                        $punchout = $entry->entry_time;
                        break;
                    case 'lunch_start':
                        $lunchstart = $entry->entry_time;
                        break;
                    case 'lunch_end':
                        $lunchend = $entry->entry_time;
                        break;
                    case 'half_day':
                    case 'casual_leave':
                    case 'sick_leave':
                    case 'holiday':
                        $state = ucfirst(str_replace('_', ' ', $entry->entry_type));
                        break;
                }
            }
            
            // Calculate total hours worked
            $totalHours = '00:00:00';
            if ($punchin && $punchout) {
                // Simple calculation without timezone conversion
                $punchInTime = new \DateTime($punchin);
                $punchOutTime = new \DateTime($punchout);
                
                // Calculate total time in minutes
                $interval = $punchInTime->diff($punchOutTime);
                $totalMinutes = ($interval->h * 60) + $interval->i;
                
                // Subtract lunch time if both lunch start and end exist
                if ($lunchstart && $lunchend) {
                    $lunchStartTime = new \DateTime($lunchstart);
                    $lunchEndTime = new \DateTime($lunchend);
                    $lunchInterval = $lunchStartTime->diff($lunchEndTime);
                    $lunchMinutes = ($lunchInterval->h * 60) + $lunchInterval->i;
                    $totalMinutes -= $lunchMinutes;
                }
                
                // Ensure non-negative result
                $totalMinutes = max(0, $totalMinutes);
                $hours = floor($totalMinutes / 60);
                $minutes = $totalMinutes % 60;
                $totalHours = sprintf('%02d:%02d:00', $hours, $minutes);
            }
            
            // Format the row
            $output .= '<tr style="border-bottom: 1px solid #eee;">';
            $output .= '<td style="padding: 12px;">'.Carbon::parse($date)->format('M d, Y').'</td>';
            $output .= '<td style="padding: 12px;">'.($punchin ? Carbon::parse($punchin)->format('H:i:s') : '-').'</td>';
            $output .= '<td style="padding: 12px;">'.($lunchstart ? Carbon::parse($lunchstart)->format('H:i:s') : '-').'</td>';
            $output .= '<td style="padding: 12px;">'.($lunchend ? Carbon::parse($lunchend)->format('H:i:s') : '-').'</td>';
            $output .= '<td style="padding: 12px;">'.($punchout ? Carbon::parse($punchout)->format('H:i:s') : '-').'</td>';
            $output .= '<td style="padding: 12px; font-weight: 600;">'.$totalHours.'</td>';
            $output .= '<td style="padding: 12px;"><span class="status-badge status-'.$state.'">'.$state.'</span></td>';
            $output .= '</tr>';
        }
        
        return response($output);
    }
}