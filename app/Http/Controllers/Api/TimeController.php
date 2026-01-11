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
    private function calculateTotalWorkHours($dayEntries)
    {
        $punchInEntries = $dayEntries->where('entry_type', 'punch_in')->sortBy('entry_time');
        $punchOutEntries = $dayEntries->where('entry_type', 'punch_out')->sortBy('entry_time');
        $lunchStartEntries = $dayEntries->where('entry_type', 'lunch_start')->sortBy('entry_time');
        $lunchEndEntries = $dayEntries->where('entry_type', 'lunch_end')->sortBy('entry_time');
        
        // Calculate total work time by pairing punch in/out sessions
        $totalMinutes = 0;
        $punchInArray = $punchInEntries->values()->toArray();
        $punchOutArray = $punchOutEntries->values()->toArray();
        
        for ($i = 0; $i < count($punchInArray); $i++) {
            if (isset($punchOutArray[$i])) {
                $punchInTime = Carbon::parse($punchInArray[$i]['entry_time']);
                $punchOutTime = Carbon::parse($punchOutArray[$i]['entry_time']);
                $totalMinutes += $punchInTime->diffInMinutes($punchOutTime);
            }
        }
        
        // Subtract lunch time
        $lunchStartArray = $lunchStartEntries->values()->toArray();
        $lunchEndArray = $lunchEndEntries->values()->toArray();
        
        for ($i = 0; $i < count($lunchStartArray); $i++) {
            if (isset($lunchEndArray[$i])) {
                $lunchStartTime = Carbon::parse($lunchStartArray[$i]['entry_time']);
                $lunchEndTime = Carbon::parse($lunchEndArray[$i]['entry_time']);
                $totalMinutes -= $lunchStartTime->diffInMinutes($lunchEndTime);
            }
        }
        
        // Format total hours
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        return sprintf('%d:%02d', $hours, $minutes);
    }
    
    private function getStatusBadges($date, $dayEntries, $employeeId)
    {
        $punchIn = $dayEntries->where('entry_type', 'punch_in')->first();
        $lunchStart = $dayEntries->where('entry_type', 'lunch_start')->first();
        $lunchEnd = $dayEntries->where('entry_type', 'lunch_end')->first();
        
        $systemsettings = SystemSetting::whereIn('setting_key', ['work_start_time', 'late_threshold', 'lunch_duration'])->pluck('setting_value', 'setting_key', )->toArray();

        // $latecheck = $systemsettings->work_start_time + $systemsettings->late_threshold ;
         $letscheck = [];
        foreach ($systemsettings as $key => $value) {
            $letscheck[$key] = $value;
        }

        $work_start_time = Carbon::createFromFormat('H:i', $letscheck['work_start_time']);
        $late_threshold   = Carbon::createFromFormat('i', $letscheck['late_threshold']);

        $result = $work_start_time->copy()->addMinutes($late_threshold->minute);
        $latecheck = $result->format('H:i')  ;
        // return $latecheck;


        $statuses = [];
        
        // Check for half-day application
        $isHalfDay = Application::where('employee_id', $employeeId)
            ->where('req_type', 'half_day')
            ->where('status', 'approved')
            ->whereDate('start_date', $date)
            ->exists();
        
        if (!$punchIn) {
            // Check if it's a weekend
            $dateCarbon = Carbon::parse($date);
            if ($dateCarbon->isSunday() || ($dateCarbon->isSaturday() && in_array(ceil($dateCarbon->day / 7), [2, 4]))) {
                $statuses[] = '<span class="badge" style="background:#17a2b8;color:white">Week Off</span>';
            } else {
                $statuses[] = '<span class="badge" style="background:#dc3545;color:white">Absent</span>';
            }
        } else {
            if ($isHalfDay) {
                $statuses[] = '<span class="badge" style="background:#fd7e14;color:white">Half Day</span>';
            } else {

                // Check if late (after 9:15 AM)
                if (Carbon::parse($punchIn->entry_time)->format('H:i') > $latecheck) {
                    $statuses[] = '<span class="badge" style="background:#ffc107;color:black">Late</span>';
                }
                else{

                    $statuses[] = '<span class="badge" style="background:#28a745;color:white">Present</span>';
                }
            }
            
            
        }
        
        // Check for long lunch (over 60 minutes)
        if ($lunchStart && $lunchEnd) {
            $lunchMinutes = Carbon::parse($lunchStart->entry_time)->diffInMinutes(Carbon::parse($lunchEnd->entry_time));
            if ($lunchMinutes > $letscheck['lunch_duration']) {
                $statuses[] = '<span class="badge" style="background:#fd7e14;color:white">Long Lunch</span>';
            }
        }
        
        return implode(' ', $statuses);
    }
    public function handleTimeAction(Request $request)
    {
        try {
            $userid = Auth::user()->emp_id;
            $click = $request->input('click');
            $today = Carbon::today()->format('Y-m-d');

            if (!in_array($click, ['punch_in', 'lunch_start', 'lunch_end', 'punch_out'])) {
                return response('error');
            }

            return \DB::transaction(function () use ($userid, $click, $today) {
                $time = Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s');

                // Get the last entry for today to check sequence
                $lastEntry = TimeEntry::where('employee_id', $userid)
                    ->whereDate('entry_time', $today)
                    ->orderBy('entry_time', 'desc')
                    ->first();

                // 1. Check for back-to-back duplicate (same action as last entry)
                if ($lastEntry && $lastEntry->entry_type === $click) {
                    return response('duplicate_action');
                }

                // 2. Validate proper sequence
                if ($lastEntry) {
                    if ($click === 'punch_out' && !in_array($lastEntry->entry_type, ['punch_in', 'lunch_end'])) {
                        return response('invalid_sequence');
                    }
                    if ($click === 'lunch_start' && !in_array($lastEntry->entry_type, ['punch_in', 'lunch_end'])) {
                        return response('invalid_sequence');
                    }
                    if ($click === 'lunch_end' && $lastEntry->entry_type !== 'lunch_start') {
                        return response('invalid_sequence');
                    }
                    if ($click === 'punch_in' && in_array($lastEntry->entry_type, ['punch_in', 'lunch_start'])) {
                        return response('invalid_sequence');
                    }
                } else if ($click !== 'punch_in') {
                    return response('must_punch_in_first');
                }

                // Simple punch-out validation - just check sequence
                // No complex work hour validation needed

                try {
                    TimeEntry::create([
                        'employee_id' => $userid,
                        'entry_type' => $click,
                        'entry_time' => $time,
                        'notes' => $click
                    ]);

                    // Check work completion after punch_out
                    if ($click === 'punch_out') {
                        $allTodayEntries = TimeEntry::where('employee_id', $userid)
                            ->whereDate('entry_time', $today)
                            ->orderBy('entry_time', 'asc')
                            ->get();
                        
                        if ($this->checkWorkCompletion($userid, $today, $allTodayEntries)) {
                            return response('work_complete');
                        }
                    }

                    return response('nothing');
                } catch (\Exception $e) {
                    \Log::error('Time entry creation failed: ' . $e->getMessage());
                    return response('error');
                }
            });
        } catch (\Exception $e) {
            \Log::error('HandleTimeAction Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
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
            $currentDate = Carbon::now('Asia/Kolkata')->format('Y-m-d');

            // Get system settings
            $workStartTime = SystemSetting::where('setting_key', 'work_start_time')->value('setting_value') ?? '09:00:00';
            $workEndTime = SystemSetting::where('setting_key', 'work_end_time')->value('setting_value') ?? '18:00:00';
            $lunchDuration = SystemSetting::where('setting_key', 'lunch_duration')->value('setting_value') ?? '60';
            $lateThreshold = SystemSetting::where('setting_key', 'late_threshold')->value('setting_value') ?? '15';

            // Format times
            if (strlen($workStartTime) == 5) $workStartTime .= ':00';
            if (strlen($workEndTime) == 5) $workEndTime .= ':00';

            $startTime = Carbon::createFromFormat('H:i:s', $workStartTime);
            $endTime = Carbon::createFromFormat('H:i:s', $workEndTime);
            $workTime = $startTime->format('H:i:s A') . '-' . $endTime->format('H:i:s A');

            // Get today's entries
            $entries = TimeEntry::where('employee_id', $userid)
                ->whereDate('entry_time', $currentDate)
                ->orderBy('entry_time', 'asc')
                ->get();

            // Initialize variables
            $totalTimeMinutes = 0;
            $totalLunchMinutes = 0;
            $firstPunchIn = null;
            $lastPunchOut = null;

            // Find first punch-in and last punch-out
            foreach ($entries as $entry) {
                if ($entry->entry_type == 'punch_in' && !$firstPunchIn) {
                    $firstPunchIn = Carbon::parse($entry->entry_time);
                }
                if ($entry->entry_type == 'punch_out') {
                    $lastPunchOut = Carbon::parse($entry->entry_time);
                }
            }

            // Calculate total time including lunch
            if ($firstPunchIn && $lastPunchOut) {
                $totalTimeMinutes = $firstPunchIn->diffInMinutes($lastPunchOut);
            } elseif ($firstPunchIn) {
                $totalTimeMinutes = $firstPunchIn->diffInMinutes(Carbon::now('Asia/Kolkata'));
            }

            // Calculate lunch time separately
            $lunchStart = null;
            foreach ($entries as $entry) {
                if ($entry->entry_type == 'lunch_start') {
                    $lunchStart = Carbon::parse($entry->entry_time);
                } elseif ($entry->entry_type == 'lunch_end' && $lunchStart) {
                    $lunchEnd = Carbon::parse($entry->entry_time);
                    $totalLunchMinutes += $lunchStart->diffInMinutes($lunchEnd);
                    $lunchStart = null;
                }
            }

            // Handle ongoing lunch
            if ($lunchStart) {
                $totalLunchMinutes += $lunchStart->diffInMinutes(Carbon::now('Asia/Kolkata'));
            }

            // Format total time (including lunch)
            $totalHours = floor($totalTimeMinutes / 60);
            $totalMins = $totalTimeMinutes % 60;
            $totalWorkedTime = sprintf('%dH %dM', $totalHours, $totalMins);

            // Format lunch time
            $lunchHours = floor($totalLunchMinutes / 60);
            $lunchMins = $totalLunchMinutes % 60;
            $totalLunchTime = $lunchHours > 0 ? sprintf('%dH %dM', $lunchHours, $lunchMins) : sprintf('%dM', $lunchMins);

            // Use total time for network display
            $netWorkTime = $totalWorkedTime;

            // Check attendance status
            $firstEntry = $entries->first();
            $status = 'Absent';

            if ($firstEntry && $firstEntry->entry_type == 'punch_in') {
                $punchTime = Carbon::parse($firstEntry->entry_time);
                $expectedStart = Carbon::parse($currentDate . ' ' . $workStartTime);
                $minutesLate = $expectedStart->diffInMinutes($punchTime, false);
                
                if ($minutesLate <= intval($lateThreshold)) {
                    $status = 'Present';
                } else {
                    $status = 'Late';
                }
            }

            // Check for half day
            $isHalfDay = Application::where('employee_id', $userid)
                ->where('req_type', 'half_day')
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $currentDate)
                ->whereDate('end_date', '>=', $currentDate)
                ->exists();

            $comHalf = false;
            if ($isHalfDay) {
                $halfDayTime = SystemSetting::where('setting_key', 'half_day_time')->value('setting_value') ?? '4:30';
                $halfParts = explode(':', $halfDayTime);
                $requiredHalfMinutes = ((int)$halfParts[0] * 60) + (int)$halfParts[1];
                $comHalf = $totalTimeMinutes >= $requiredHalfMinutes;
            }

            // Format status
            $statusColors = [
                'Present' => 'green',
                'Late' => 'orange', 
                'Absent' => 'red'
            ];
            $statusLabel = "<label style='color:{$statusColors[$status]};'>{$status}</label>";

            $firstPunchFormatted = $firstEntry ? Carbon::parse($firstEntry->entry_time)->format('H:i A') : 'N/A';

            return response()->json([
                'worktime' => $workTime,
                'punchTime' => $startTime->format('H:i:s A'),
                'action' => 'run',
                'lunchDuation' => $lunchDuration . 'M',
                'punch_in' => $firstPunchFormatted,
                'total_hours' => $totalWorkedTime,
                'network' => $netWorkTime,
                'totalLunchByemp' => $totalLunchTime ?: '0M',
                'late' => $statusLabel,
                'isHalf' => $isHalfDay,
                'com_half' => $comHalf
            ]);
        } catch (\Exception $e) {
            \Log::error('TimeWorked Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'worktime' => '09:00:00 AM-06:00:00 PM',
                'punchTime' => '09:00:00 AM',
                'action' => 'run',
                'lunchDuation' => '60M',
                'punch_in' => 'N/A',
                'total_hours' => '0H 0M',
                'network' => '0H 0M',
                'totalLunchByemp' => '0M',
                'late' => '<label style="color:red;">Error</label>',
                'isHalf' => false,
                'com_half' => false
            ]);
        }
    }

    public function checkFirstPunchIn(Request $request)
    {
        $userid = Auth::user()->emp_id;
        $today = Carbon::today()->format('Y-m-d');

        // Get all entries for today
        $todayEntries = TimeEntry::where('employee_id', $userid)
            ->whereDate('entry_time', $today)
            ->orderBy('entry_time', 'asc')
            ->get();

        if ($todayEntries->isEmpty()) {
            return response('5'); // No entries, can punch in
        }

        $lastEntry = $todayEntries->last();
        $firstEntry = $todayEntries->first();

        // Check work completion first
        if ($this->checkWorkCompletionForButtons($userid, $today, $todayEntries)) {
            return response('6'); // Work complete, disable all buttons
        }

        // Check current state based on last entry
        switch ($lastEntry->entry_type) {
            case 'punch_in':
                return response('1'); // Punched in
            case 'lunch_start':
                return response('2'); // On lunch
            case 'lunch_end':
                return response('3'); // Lunch ended
            case 'punch_out':
                return response('4'); // Punched out but can continue
            case 'casual_leave':
            case 'sick_leave':
            case 'holiday':
            case 'regularization':
                return response($lastEntry->entry_type);
            default:
                return response('5');
        }
    }

    private function checkWorkCompletionForButtons($userid, $today, $todayEntries)
    {
        try {
            // Get work start and end times from system settings
            $workStartTime = SystemSetting::where('setting_key', 'work_start_time')->value('setting_value') ?? '09:00';
            $workEndTime = SystemSetting::where('setting_key', 'work_end_time')->value('setting_value') ?? '18:00';
            
            // Calculate required work hours from system settings
            $startTime = Carbon::createFromFormat('H:i', $workStartTime);
            $endTime = Carbon::createFromFormat('H:i', $workEndTime);
            $requiredHours = $startTime->diffInHours($endTime);
            
            // Sum all punch in/out sessions for the day
            $punchInEntries = $todayEntries->where('entry_type', 'punch_in')->sortBy('entry_time');
            $punchOutEntries = $todayEntries->where('entry_type', 'punch_out')->sortBy('entry_time');
            
            $totalWorkedMinutes = 0;
            $punchInArray = $punchInEntries->values()->toArray();
            $punchOutArray = $punchOutEntries->values()->toArray();
            
            // Calculate total worked time by pairing punch in/out sessions
            for ($i = 0; $i < count($punchInArray); $i++) {
                if (isset($punchOutArray[$i])) {
                    $punchInTime = Carbon::parse($punchInArray[$i]['entry_time']);
                    $punchOutTime = Carbon::parse($punchOutArray[$i]['entry_time']);
                    $totalWorkedMinutes += $punchInTime->diffInMinutes($punchOutTime);
                }
            }
            
            // Convert minutes to hours (including decimal part)
            $totalWorkedHours = $totalWorkedMinutes / 60;
            
            // Check if last action is punch out
            $lastEntry = $todayEntries->last();
            $isLastActionPunchOut = $lastEntry && $lastEntry->entry_type === 'punch_out';
            
            // Work is complete if:
            // 1. Total worked hours >= required hours AND
            // 2. Last action is punch out
            return $totalWorkedHours >= $requiredHours && $isLastActionPunchOut;
            
        } catch (\Exception $e) {
            \Log::error('CheckWorkCompletionForButtons Error: ' . $e->getMessage());
            return false;
        }
    }

    private function checkWorkCompletion($userid, $today, $todayEntries)
{
    try {
        // 1. Robust Settings Retrieval (Using Carbon::parse handles '9:00', '09:00:00' etc automatically)
        $settingStart = SystemSetting::where('setting_key', 'work_start_time')->value('setting_value') ?? '09:00:00';
        $settingEnd   = SystemSetting::where('setting_key', 'work_end_time')->value('setting_value') ?? '18:00:00';
        $settingHalf  = SystemSetting::where('setting_key', 'half_day_time')->value('setting_value') ?? '04:30'; // Changed default to padded

        \Log::info('checkWorkCompletion', [['settingStart' => $settingStart, 'settingEnd' => $settingEnd, 'settingHalf' => $settingHalf]]);


        // Calculate Target Minutes for Full Day
        // We create a base date (today) to ensure diff works correctly across midnight if needed
        $reqStartTime = Carbon::parse($today . ' ' . $settingStart);
        $reqEndTime   = Carbon::parse($today . ' ' . $settingEnd);
        $requiredMinutes = $reqEndTime->diffInMinutes($reqStartTime);

        \Log::info('Targets', ['Required Full Day Mins' => $requiredMinutes]);

        // 2. Calculate Actual Worked Span (Gross Hours: First In to Last Out)
        // Filter collection to find explicit First IN and Last OUT
        $firstEntry = $todayEntries->where('entry_type', 'punch_in')->sortBy('entry_time')->first();
        $lastEntry  = $todayEntries->where('entry_type', 'punch_out')->sortByDesc('entry_time')->first();

        $firstPunchIn = $firstEntry ? Carbon::parse($firstEntry->entry_time) : null;
        $lastPunchOut = $lastEntry ? Carbon::parse($lastEntry->entry_time) : null;

        $totalTimeMinutes = 0;

        if ($firstPunchIn) {
            if ($lastPunchOut && $lastPunchOut->gt($firstPunchIn)) {
                // Case A: User has punched out. Calculate span.
                $totalTimeMinutes = $firstPunchIn->diffInMinutes($lastPunchOut);
            } else {
                // Case B: User is currently active (or last punch out is missing/invalid)
                // Fallback to "Time until Now"
                $totalTimeMinutes = $firstPunchIn->diffInMinutes(Carbon::now());
            }
        }

        \Log::info('Actuals', [
            'First In' => $firstPunchIn ? $firstPunchIn->format('H:i') : 'N/A',
            'Last Out' => $lastPunchOut ? $lastPunchOut->format('H:i') : 'N/A',
            'Total Minutes' => $totalTimeMinutes
        ]);

        if ($totalTimeMinutes <= 0) {
            return false;
        }

        // 3. Check Half Day Status
        $isHalfDay = Application::where('employee_id', $userid)
            ->where('req_type', 'half_day')
            ->where('status', 'approved')
            ->whereDate('start_date', $today)
            ->exists();

        if ($isHalfDay) {
            // Robust Half Day Calculation
            // Parse "4:30" or "04:30:00" safely
            $halfDayParts = explode(':', $settingHalf);
            $halfHours = (int)($halfDayParts[0] ?? 4);
            $halfMinutes = (int)($halfDayParts[1] ?? 30);
            $requiredHalfDayMinutes = ($halfHours * 60) + $halfMinutes;

            $result = $totalTimeMinutes >= $requiredHalfDayMinutes;
            
            \Log::info("Half Day Result: " . ($result ? 'Pass' : 'Fail'));
            return $result;
        } else {
            // Full Day Check
            $result = $totalTimeMinutes >= $requiredMinutes;
            
            // Allow a small buffer (e.g., 1 minute) if needed, otherwise strict check
            \Log::info("Full Day Result: " . ($result ? 'Pass' : 'Fail'));
            return $result;
        }

    } catch (\Exception $e) {
        \Log::error('CheckWorkCompletion Critical Error: ' . $e->getMessage() . ' on line ' . $e->getLine());
        return false;
    }
}

    public function detailsById(Request $request)
    {

        

        // return $systemsettings;
        \Log::info('detailsById called with data:', [$request->all()]);
        
        $id = $request->input('id');
        if (!$id) {
            return response('error');
        }
        
        \Log::info('Looking for employee with ID: ' . $id);
        
        $employee = \App\Models\Employee::where('emp_id', $id)->first();
        if (!$employee) {
            return response('Employee not found');
        }
        
        // Get current month entries
        $currentMonth = Carbon::now();
        $entries = TimeEntry::where('employee_id', $id)
            ->whereYear('entry_time', $currentMonth->year)
            ->whereMonth('entry_time', $currentMonth->month)
            ->orderBy('entry_time', 'desc')
            ->get();
            
        if ($entries->isEmpty()) {
            return response('');
        }
        
        // Group entries by date
        $groupedEntries = $entries->groupBy(function($entry) {
            return Carbon::parse($entry->entry_time)->format('Y-m-d');
        });
        
        $output = '';
        
        foreach ($groupedEntries as $date => $dayEntries) {
            $punchIn = $dayEntries->where('entry_type', 'punch_in')->first();
            $lunchStart = $dayEntries->where('entry_type', 'lunch_start')->first();
            $lunchEnd = $dayEntries->where('entry_type', 'lunch_end')->first();
            $punchOut = $dayEntries->where('entry_type', 'punch_out')->first();
            
            // Calculate total hours using new method
            $totalHours = $this->calculateTotalWorkHours($dayEntries);
            
            // Get status badges with robust checking
            $statusBadges = $this->getStatusBadges($date, $dayEntries, $id);
            
            $output .= '<tr>
                <td>' . Carbon::parse($date)->format('M d, Y') . '</td>
                <td>' . ($punchIn ? Carbon::parse($punchIn->entry_time)->format('H:i A') : '-') . '</td>
                <td>' . ($lunchStart ? Carbon::parse($lunchStart->entry_time)->format('H:i A') : '-') . '</td>
                <td>' . ($lunchEnd ? Carbon::parse($lunchEnd->entry_time)->format('H:i A') : '-') . '</td>
                <td>' . ($punchOut ? Carbon::parse($punchOut->entry_time)->format('H:i A') : '-') . '</td>
                <td>' . $totalHours . '</td>
                <td>' . $statusBadges . '</td>
            </tr>';
        }
        
        return response($output);
    }

    public function filterTime(Request $request)
    {
        $id = $request->input('id');
        $click = $request->input('click');
        
        if (!$id) {
            return response('error');
        }
        
        $employee = \App\Models\Employee::where('emp_id', $id)->first();
        if (!$employee) {
            return response('Employee not found');
        }
        
        $query = TimeEntry::where('employee_id', $id);
        
        if ($click === 'filterLastMonth') {
            $lastMonth = Carbon::now()->subMonth();
            $query->whereYear('entry_time', $lastMonth->year)
                  ->whereMonth('entry_time', $lastMonth->month);
        } elseif ($click === 'filterCustomRange') {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            
            if ($startDate && $endDate) {
                $query->whereDate('entry_time', '>=', $startDate)
                      ->whereDate('entry_time', '<=', $endDate);
            }
        }
        
        $entries = $query->orderBy('entry_time', 'desc')->get();
        
        if ($entries->isEmpty()) {
            return response('');
        }
        
        // Group entries by date
        $groupedEntries = $entries->groupBy(function($entry) {
            return Carbon::parse($entry->entry_time)->format('Y-m-d');
        });
        
        $output = '';
        
        foreach ($groupedEntries as $date => $dayEntries) {
            $punchIn = $dayEntries->where('entry_type', 'punch_in')->first();
            $lunchStart = $dayEntries->where('entry_type', 'lunch_start')->first();
            $lunchEnd = $dayEntries->where('entry_type', 'lunch_end')->first();
            $punchOut = $dayEntries->where('entry_type', 'punch_out')->first();
            
            // Calculate total hours using new method
            $totalHours = $this->calculateTotalWorkHours($dayEntries);
            
            // Get status badges with robust checking
            $statusBadges = $this->getStatusBadges($date, $dayEntries, $id);
            
            $output .= '<tr>
                <td>' . Carbon::parse($date)->format('M d, Y') . '</td>
                <td>' . ($punchIn ? Carbon::parse($punchIn->entry_time)->format('H:i A') : '-') . '</td>
                <td>' . ($lunchStart ? Carbon::parse($lunchStart->entry_time)->format('H:i A') : '-') . '</td>
                <td>' . ($lunchEnd ? Carbon::parse($lunchEnd->entry_time)->format('H:i A') : '-') . '</td>
                <td>' . ($punchOut ? Carbon::parse($punchOut->entry_time)->format('H:i A') : '-') . '</td>
                <td>' . $totalHours . '</td>
                <td>' . $statusBadges . '</td>
            </tr>';
        }
        
        return response($output);
    }
}