<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use App\Models\SystemSetting;
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
        $halfDayApplication = \App\Models\Application::where('employee_id', $employee->emp_id)
            ->where('req_type', 'half_day')
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();
            
        $isHalfDay = $halfDayApplication ? true : false;
        $halfDayTime = null;
        
        if ($isHalfDay) {
            $halfDayTime = \App\Models\SystemSetting::where('setting_key', 'half_day_time')
                ->value('setting_value') ?? '4:30';
        }
        
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
            'isHalfDay',
            'halfDayTime'
        ));
    }

    public function punchIn(Request $request)
    {
        $employee = Auth::user();
        $lastEntry = TimeEntry::getLastEntryForEmployee($employee->emp_id);

        if ($lastEntry && in_array($lastEntry->entry_type, ['punch_in', 'lunch_end'])) {
            return response()->json(['error' => 'Already punched in'], 400);
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

        TimeEntry::create([
            'employee_id' => $employee->emp_id,
            'entry_type' => 'punch_out',
            'entry_time' => now(),
            'notes' => 'punch_out'
        ]);

        return response()->json(['success' => true, 'message' => 'Punch Out Successful!']);
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
        return view('schedule');
    }
    
    public function getScheduleData(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $userid = Auth::user()->emp_id;
        
        \Log::info('getScheduleData called with data:', $request->all());
        \Log::info('Schedule params:', ['month' => $month, 'year' => $year, 'userid' => $userid]);
        
        // Get time entries for the month
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        \Log::info('Date range:', ['start' => $startDate->toDateTimeString(), 'end' => $endDate->toDateTimeString()]);
        
        $timeEntries = TimeEntry::where('employee_id', $userid)
            ->whereBetween('entry_time', [$startDate, $endDate])
            ->orderBy('entry_time')
            ->get();
            
        \Log::info('Found entries count:', $timeEntries->count());
            
        // Process entries by date
        $dateWiseData = [];
        foreach ($timeEntries as $entry) {
            $date = $entry->entry_time->format('Y-m-d');
            if (!isset($dateWiseData[$date])) {
                $dateWiseData[$date] = ['status' => 'absent', 'entries' => []];
            }
            
            $dateWiseData[$date]['entries'][] = $entry;
            
            // Determine status based on entry type
            if ($entry->entry_type === 'punch_in') {
                $dateWiseData[$date]['status'] = 'present';
            } elseif (in_array($entry->entry_type, ['casual_leave', 'sick_leave', 'half_day', 'holiday'])) {
                $dateWiseData[$date]['status'] = $entry->entry_type;
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
            
        $status = 'absent';
        if ($entries->count() > 0) {
            $firstEntry = $entries->first();
            if ($firstEntry->entry_type === 'punch_in') {
                $status = 'present';
            } elseif (in_array($firstEntry->entry_type, ['casual_leave', 'sick_leave', 'half_day', 'holiday'])) {
                $status = $firstEntry->entry_type;
            }
        }
        
        return response()->json([
            'status' => $status,
            'entries' => $entries->map(function($entry) {
                return [
                    'entry_type' => $entry->entry_type,
                    'notes' => $entry->notes,
                    'entry_time' => $entry->entry_time->format('H:i:s')
                ];
            })
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
}