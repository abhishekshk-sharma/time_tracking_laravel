<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use App\Models\Employee;
use App\Models\SystemSetting;
use App\Services\LocationAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {

        

        $employee = Auth::user();
        $today = today();
        
        // Check if today is half day
        $isHalfDay = \App\Models\Application::where('employee_id', $employee->emp_id)
            ->where('req_type', 'half_leave')
            ->where('status', 'approved')
            ->whereDate('start_date', $today)
            ->exists();
        
        // Get today's time entries
        $todayEntries = $employee->getTodayTimeEntries();
        
        // Calculate working hours
        $workingHours = TimeEntry::calculateWorkingHours($employee->emp_id, $today);
        
        // Get last entry to determine current state
        $lastEntry = TimeEntry::getLastEntryForEmployee($employee->emp_id, $today);
        
        // Determine button states
        $buttonStates = $this->getButtonStates($lastEntry);
        
        // Get system settings
        $workStartTime = SystemSetting::getWorkStartTime();
        $workEndTime = SystemSetting::getWorkEndTime();

        return view('dashboard.index', compact(
            'employee',
            'todayEntries',
            'workingHours',
            'buttonStates',
            'workStartTime',
            'workEndTime',
            'isHalfDay'
        ));
    }

    public function punchIn(Request $request)
    {
        $employee = Auth::user();
        $lastEntry = TimeEntry::getLastEntryForEmployee($employee->emp_id);

        if ($lastEntry && in_array($lastEntry->entry_type, ['punch_in', 'lunch_end'])) {
            return response()->json(['error' => 'Already punched in'], 400);
        }

        // Location authentication
        $locationAuth = app(LocationAuthService::class);
        $authResult = $locationAuth->authenticateLocation($employee, $request);
        
        if (!$authResult['success']) {
            if (isset($authResult['require_image'])) {
                return response()->json([
                    'require_image' => true,
                    'message' => 'Please capture your image to proceed'
                ]);
            }
            return response()->json(['error' => $authResult['message']], 400);
        }

        TimeEntry::create([
            'employee_id' => $employee->emp_id,
            'entry_type' => 'punch_in',
            'entry_time' => now(),
            'notes' => 'punch_in'
        ]);

        return response()->json(['success' => true, 'message' => 'Punch In Successful!']);
    }

    public function punchOut(Request $request)
    {
        $employee = Auth::user();
        $lastEntry = TimeEntry::getLastEntryForEmployee($employee->emp_id);

        if (!$lastEntry || !in_array($lastEntry->entry_type, ['punch_in', 'lunch_end'])) {
            return response()->json(['error' => 'Must punch in first'], 400);
        }

        // Location authentication
        $locationAuth = app(LocationAuthService::class);
        $authResult = $locationAuth->authenticateLocation($employee, $request);
        
        if (!$authResult['success']) {
            if (isset($authResult['require_image'])) {
                return response()->json([
                    'require_image' => true,
                    'message' => 'Please capture your image to proceed'
                ]);
            }
            return response()->json(['error' => $authResult['message']], 400);
        }

        TimeEntry::create([
            'employee_id' => $employee->emp_id,
            'entry_type' => 'punch_out',
            'entry_time' => now(),
            'notes' => 'punch_out'
        ]);

        return response()->json(['success' => true, 'message' => 'Punch Out Successful!']);
    }

    public function captureImage(Request $request)
    {
        $employee = Auth::user();
        $imageData = $request->input('image');
        $entryType = $request->input('entry_type');
        
        if (!$imageData || !$entryType) {
            return response()->json(['error' => 'Image and entry type required'], 400);
        }
        
        // Create time entry first
        if ($entryType === 'punch_in') {
            $result = $this->processPunchIn($employee);
        } elseif ($entryType === 'punch_out') {
            $result = $this->processPunchOut($employee);
        } else {
            return response()->json(['error' => 'Invalid entry type'], 400);
        }
        
        if ($result['success']) {
            // Store image with entry_id
            $locationAuth = app(LocationAuthService::class);
            $imageResult = $locationAuth->storeEntryImage($employee, $imageData, $entryType, $result['entry_id']);
            
            return response()->json([
                'success' => true, 
                'message' => $result['message']
            ]);
        }
        
        return response()->json(['error' => 'Failed to create time entry'], 400);
    }
    
    private function processPunchIn($employee)
    {
        $timeEntry = TimeEntry::create([
            'employee_id' => $employee->emp_id,
            'entry_type' => 'punch_in',
            'entry_time' => now(),
            'notes' => 'punch_in'
        ]);
        
        return ['success' => true, 'message' => 'Punch In Successful!', 'entry_id' => $timeEntry->id];
    }
    
    private function processPunchOut($employee)
    {
        $timeEntry = TimeEntry::create([
            'employee_id' => $employee->emp_id,
            'entry_type' => 'punch_out',
            'entry_time' => now(),
            'notes' => 'punch_out'
        ]);
        
        return ['success' => true, 'message' => 'Punch Out Successful!', 'entry_id' => $timeEntry->id];
    }

    public function lunchStart(Request $request)
    {
        $employee = Auth::user();
        $lastEntry = TimeEntry::getLastEntryForEmployee($employee->emp_id);

        if (!$lastEntry || $lastEntry->entry_type !== 'punch_in') {
            return response()->json(['error' => 'Must punch in first'], 400);
        }

        TimeEntry::create([
            'employee_id' => $employee->emp_id,
            'entry_type' => 'lunch_start',
            'entry_time' => now(),
            'notes' => 'lunch_start'
        ]);

        return response()->json(['success' => true, 'message' => 'Lunch Start Successful!']);
    }

    public function lunchEnd(Request $request)
    {
        $employee = Auth::user();
        $lastEntry = TimeEntry::getLastEntryForEmployee($employee->emp_id);

        if (!$lastEntry || $lastEntry->entry_type !== 'lunch_start') {
            return response()->json(['error' => 'Must start lunch first'], 400);
        }

        TimeEntry::create([
            'employee_id' => $employee->emp_id,
            'entry_type' => 'lunch_end',
            'entry_time' => now(),
            'notes' => 'lunch_end'
        ]);

        return response()->json(['success' => true, 'message' => 'Lunch End Successful!']);
    }

    public function getTimeData(Request $request)
    {
        $employee = Auth::user();
        $workingHours = TimeEntry::calculateWorkingHours($employee->emp_id);
        $lastEntry = TimeEntry::getLastEntryForEmployee($employee->emp_id);
        
        $todayEntries = $employee->getTodayTimeEntries();
        $punchInTime = $todayEntries->where('entry_type', 'punch_in')->first();
        
        return response()->json([
            'action' => 'run',
            'worktime' => $this->formatMinutes($workingHours['work_minutes']),
            'punchTime' => $punchInTime ? $punchInTime->entry_time->format('h:i A') : '00:00 AM',
            'lunchDuation' => $workingHours['lunch_minutes'] . 'M',
            'punch_in' => $punchInTime ? $punchInTime->entry_time->format('h:i A') : '00:00 AM',
            'total_hours' => $this->formatMinutes($workingHours['total_minutes']),
            'network' => $this->formatMinutes($workingHours['work_minutes']),
            'totalLunchByemp' => $this->formatMinutes($workingHours['lunch_minutes']),
            'late' => $this->getLateStatus($punchInTime),
        ]);
    }

    public function checkFirstPunchIn(Request $request)
    {
        $employee = Auth::user();
        $today = today();
        
        $firstEntry = TimeEntry::where('employee_id', $employee->emp_id)
            ->whereDate('entry_time', $today)
            ->orderBy('entry_time', 'asc')
            ->first();
            
        $lastEntry = TimeEntry::where('employee_id', $employee->emp_id)
            ->whereDate('entry_time', $today)
            ->orderBy('entry_time', 'desc')
            ->first();

        if ($firstEntry && in_array($firstEntry->entry_type, ['regularization', 'casual_leave', 'sick_leave', 'holiday'])) {
            return response($firstEntry->entry_type);
        }
        
        if ($firstEntry && $firstEntry->entry_type === 'punch_in') {
            if (!$lastEntry) {
                return response('1'); // Punch in state
            }
            
            switch ($lastEntry->entry_type) {
                case 'punch_out':
                    return response('4'); // Punch out state
                case 'lunch_start':
                    return response('2'); // Lunch start state
                case 'lunch_end':
                    return response('3'); // Lunch end state
                case 'punch_in':
                    return response('1'); // Punch in state
                default:
                    return response('1');
            }
        }
        
        if ($firstEntry && $firstEntry->entry_type === 'half_day') {
            if ($lastEntry) {
                switch ($lastEntry->entry_type) {
                    case 'punch_in':
                        return response('1');
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
        }
        
        return response('5'); // Welcome back state
    }

    private function getButtonStates($lastEntry)
    {
        if (!$lastEntry) {
            return [
                'punch_in' => false,
                'lunch_start' => true,
                'lunch_end' => true,
                'punch_out' => true,
            ];
        }

        switch ($lastEntry->entry_type) {
            case 'punch_in':
            case 'lunch_end':
                return [
                    'punch_in' => true,
                    'lunch_start' => false,
                    'lunch_end' => true,
                    'punch_out' => false,
                ];
            case 'lunch_start':
                return [
                    'punch_in' => true,
                    'lunch_start' => true,
                    'lunch_end' => false,
                    'punch_out' => true,
                ];
            case 'punch_out':
                return [
                    'punch_in' => false,
                    'lunch_start' => true,
                    'lunch_end' => true,
                    'punch_out' => true,
                ];
            default:
                return [
                    'punch_in' => false,
                    'lunch_start' => true,
                    'lunch_end' => true,
                    'punch_out' => true,
                ];
        }
    }

    public function history()
    {
        return view('applications.history');
    }
    
    public function schedule()
    {
        $currentMonth = request('month', Carbon::now()->month);
        $currentYear = request('year', Carbon::now()->year);
        $userid = Auth::user()->emp_id;
        
        // Get schedule exceptions for current month
        $scheduleExceptions = \App\Models\ScheduleException::whereMonth('exception_date', $currentMonth)
            ->whereYear('exception_date', $currentYear)
            ->get();
        
        // Generate calendar
        $calendar = $this->generateUserCalendar($currentYear, $currentMonth, $userid);
        
        return view('schedule', compact('calendar', 'currentMonth', 'currentYear', 'scheduleExceptions'));
    }
    
    public function getScheduleData(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $userid = Auth::user()->emp_id;
        
        // Generate calendar using the same approach as admin
        $calendar = $this->generateUserCalendar($year, $month, $userid);
        
        // Convert calendar to the format expected by frontend
        $dateWiseData = [];
        
        foreach ($calendar as $week) {
            foreach ($week as $day) {
                $dateStr = $day['date']->format('Y-m-d');
                $status = $this->getDayStatus($day, $userid);
                
                $dateWiseData[$dateStr] = [
                    'status' => $status,
                    'entries' => $day['time_entries'] ?? []
                ];
            }
        }
        
        return response()->json($dateWiseData);
    }
    
    public function getScheduleDetails(Request $request)
    {
        $date = $request->input('date');
        $userid = Auth::user()->emp_id;
        
        $entries = TimeEntry::where('employee_id', $userid)
            ->whereDate('entry_time', $date)
            ->orderBy('entry_time')
            ->get();
        
        // Check for schedule exception
        $exception = null;
        if (\Schema::hasTable('schedule_exceptions')) {
            $exception = \DB::table('schedule_exceptions')
                ->where('exception_date', $date)
                ->first();
        }
        
        // Determine status - return exact exception type if exists
        $status = 'absent';
        if ($exception) {
            $status = $exception->type; // Return exact exception type
        } elseif ($entries->count() > 0) {
            $firstEntry = $entries->first();
            $status = match($firstEntry->entry_type) {
                'punch_in' => 'present',
                'casual_leave' => 'casual_leave',
                'sick_leave' => 'sick_leave',
                'half_day' => 'half_day',
                default => 'absent'
            };
        } elseif ($this->isWeekendByPolicy(Carbon::parse($date))) {
            $status = 'weekend';
        }
        
        $entriesData = $entries->map(function($entry) {
            return [
                'entry_type' => $entry->entry_type,
                'notes' => $entry->notes,
                'entry_time' => $entry->entry_time->format('H:i:s')
            ];
        })->toArray();
        
        // Add exception info to entries
        if ($exception) {
            $entriesData[] = [
                'entry_type' => $exception->type,
                'notes' => $exception->description ?? ucfirst(str_replace('_', ' ', $exception->type)),
                'entry_time' => $exception->description ?? ucfirst(str_replace('_', ' ', $exception->type))
            ];
        } elseif ($status === 'weekend' && empty($entriesData)) {
            $entriesData[] = [
                'entry_type' => 'weekend',
                'notes' => 'Weekend',
                'entry_time' => 'Weekend'
            ];
        }
        
        return response()->json([
            'status' => $status,
            'entries' => $entriesData
        ]);
    }
    
    private function formatMinutes($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d:00', $hours, $mins);
    }
    
    private function getLateStatus($punchInTime)
    {
        if (!$punchInTime) {
            return '<label style="color:red;">Absent</label>';
        }
        
        $workStartTime = SystemSetting::where('setting_key', 'work_start_time')->first();
        $lateThreshold = SystemSetting::where('setting_key', 'late_threshold')->first();
        
        if (!$workStartTime || !$lateThreshold) {
            return '<label style="color:green;">Present</label>';
        }
        
        try {
            // Ensure the time format is correct
            $timeValue = $workStartTime->setting_value;
            if (strlen($timeValue) == 5) {
                $timeValue .= ':00'; // Add seconds if missing
            }
            
            $startTime = Carbon::createFromFormat('H:i:s', $timeValue);
            $punchTime = Carbon::parse($punchInTime->entry_time);
            
            // Set the same date for comparison
            $startTime->setDate($punchTime->year, $punchTime->month, $punchTime->day);
            
            $minutesLate = $startTime->diffInMinutes($punchTime, false);
            
            if ($minutesLate <= intval($lateThreshold->setting_value)) {
                return '<label style="color:green;">Present</label>';
            } else {
                return '<label style="color:orange;">Late</label>';
            }
        } catch (\Exception $e) {
            \Log::error('Late status calculation error: ' . $e->getMessage());
            return '<label style="color:green;">Present</label>';
        }
    }

    public function employeehistory(){
        $applications = $history = TimeEntry::with('employee')
        ->where('employee_id', Auth::user()->emp_id)
        ->whereBetween('entry_time', ['2025-12-01', '2025-12-30'])
        ->get();

        // return $history;
        return view('applications.history', compact('applications'));
    }
    
    private function generateUserCalendar($year, $month, $userid)
    {
        $firstDay = Carbon::create($year, $month, 1);
        $lastDay = $firstDay->copy()->endOfMonth();
        
        // Start from Monday (1) and end on Sunday (0)
        $startOfWeek = $firstDay->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $lastDay->copy()->endOfWeek(Carbon::SUNDAY);
        
        $calendar = [];
        $current = $startOfWeek->copy();
        
        while ($current <= $endOfWeek) {
            // Get schedule exceptions for this date
            $exception = null;
            if (\Schema::hasTable('schedule_exceptions')) {
                $exception = \DB::table('schedule_exceptions')
                    ->where('exception_date', $current->format('Y-m-d'))
                    ->first();
            }
            
            // Get time entries for this date
            $timeEntries = TimeEntry::where('employee_id', $userid)
                ->whereDate('entry_time', $current)
                ->orderBy('entry_time')
                ->get()
                ->toArray();
            
            $calendar[] = [
                'date' => $current->copy(),
                'is_current_month' => $current->month == $month,
                'exception' => $exception,
                'time_entries' => $timeEntries
            ];
            
            $current->addDay();
        }
        
        return array_chunk($calendar, 7);
    }
    
    private function getDayStatus($day, $userid)
    {
        $date = $day['date'];
        $exception = $day['exception'] ?? null;
        $timeEntries = collect($day['time_entries'] ?? []);
        $today = Carbon::today();
        
        // Priority 1: Schedule exceptions
        if ($exception) {
            return match($exception->type) {
                'holiday' => 'holiday',
                'working_day' => 'present',
                'weekend' => 'weekend',
                default => 'weekend'
            };
        }
        
        // Priority 2: Time entries
        if ($timeEntries->count() > 0) {
            $firstEntry = $timeEntries->first();
            return match($firstEntry['entry_type']) {
                'punch_in' => 'present',
                'casual_leave' => 'casual_leave',
                'sick_leave' => 'sick_leave',
                'half_day' => 'half_day',
                default => 'absent'
            };
        }
        
        // Priority 3: Weekend policy
        if ($this->isWeekendByPolicy($date)) {
            return 'weekend';
        }
        
        // Priority 4: Future dates - no status
        if ($date->gt($today)) {
            return 'future';
        }
        
        // Priority 5: Past dates without entries - absent
        return 'absent';
    }
    
    private function isWeekendByPolicy($date)
    {
        // Get weekend policy from system settings
        $weekendPolicySetting = SystemSetting::where('setting_key', 'weekend_policy')->first();
        $weekendPolicy = $weekendPolicySetting ? json_decode($weekendPolicySetting->setting_value, true) : [
            'recurring_days' => [0], // Default: Sunday only
            'specific_pattern' => []
        ];
        
        $dayOfWeek = $date->dayOfWeek; // 0 = Sunday, 6 = Saturday
        
        // Check recurring days
        if (in_array($dayOfWeek, $weekendPolicy['recurring_days'])) {
            return true;
        }
        
        // Check specific patterns (e.g., 2nd/4th Saturday)
        if (isset($weekendPolicy['specific_pattern'][$dayOfWeek])) {
            $weekOfMonth = ceil($date->day / 7);
            if (in_array($weekOfMonth, $weekendPolicy['specific_pattern'][$dayOfWeek])) {
                return true;
            }
        }
        
        return false;
    }
}