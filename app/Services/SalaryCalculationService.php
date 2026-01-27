<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TimeEntry;
use App\Models\SystemSetting;
use App\Models\ScheduleException;
use Carbon\Carbon;

class SalaryCalculationService
{
    private $weekendPolicy;
    private $scheduleExceptions;

    public function __construct()
    {
        $this->loadWeekendPolicy();
    }

    private function loadWeekendPolicy()
    {
        $setting = SystemSetting::where('setting_key', 'weekend_policy')->first();
        $this->weekendPolicy = $setting ? json_decode($setting->setting_value, true) : [
            'recurring_days' => [0],
            'specific_pattern' => []
        ];
    }

    // private function loadScheduleExceptions($month, $year, $adminId = null)    {
    //     $query = ScheduleException::whereMonth('exception_date', $month)
    //         ->whereYear('exception_date', $year);
            
    //     // If adminId is provided, get admin's exceptions + super admin exceptions
    //     if ($adminId) {
    //         $query->where(function($q) use ($adminId) {
    //             $q->where('admin_id', $adminId)
    //               ->orWhereNotNull('superadmin_id');
    //         });
    //     }
        
    //     $exceptions = $query->get();
    //     // return "hello";
        
    //     // Group by date and prioritize admin exceptions over super admin exceptions
    //     $this->scheduleExceptions = $exceptions->groupBy('exception_date')
    //         ->map(function($dateExceptions) use ($adminId) {
    //             // If admin exception exists for this date, use it; otherwise use super admin exception
    //             $adminException = $dateExceptions->where('admin_id', $adminId)->first();
    //             $superAdminException = $dateExceptions->whereNotNull('superadmin_id')->first();
    //             return $adminException ?: $superAdminException;
    //         })
    //         ->filter(function($exception) {
    //             return $exception !== null;
    //         });
    // }

    private function loadScheduleExceptions($month, $year, $adminId = null){
    // 1. Fetch Raw Data
    $query = ScheduleException::whereMonth('exception_date', $month)
        ->whereYear('exception_date', $year);
        
    if ($adminId) {
        $query->where(function($q) use ($adminId) {
            $q->where('admin_id', $adminId)
              ->orWhereNotNull('superadmin_id');
        });
    }
    
    $exceptions = $query->get();

    // 2. Process Logic
    $processedExceptions = $exceptions->groupBy(function($item) {
            // Safety: Ensure we group by clean Y-m-d string to avoid timestamp mismatches
            return \Carbon\Carbon::parse($item->exception_date)->format('Y-m-d');
        })
        ->map(function($dateExceptions) use ($adminId) {
            // Logic: Prioritize Admin ID match
            $adminException = $dateExceptions->where('admin_id', $adminId)->first();
            
            // Fallback: Super Admin
            $superAdminException = $dateExceptions->whereNotNull('superadmin_id')->first();
            
            // Return Admin exception if exists, otherwise Super Admin
            return $adminException ?: $superAdminException;
        })
        ->filter(); // Removes nulls

    // 3. Assign to property (if you need it elsewhere)
    $this->scheduleExceptions = $processedExceptions;

    // 4. CRITICAL: Return the result!
    return $processedExceptions;
}

    private function isWeekendDay($date)
    {
        $carbonDate = Carbon::parse($date);
        $dayOfWeek = $carbonDate->dayOfWeek;
        
        // Check recurring days
        if (in_array($dayOfWeek, $this->weekendPolicy['recurring_days'])) {
            return true;
        }
        
        // Check specific patterns (e.g., 2nd/4th Saturday)
        if (!empty($this->weekendPolicy['specific_pattern'])) {
            foreach ($this->weekendPolicy['specific_pattern'] as $weekDay => $weeks) {
                if ($dayOfWeek == $weekDay) {
                    $weekOfMonth = ceil($carbonDate->day / 7);
                    if (in_array($weekOfMonth, $weeks)) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }

    private function getScheduleException($date)
    {
        return $this->scheduleExceptions->get($date);
    }
    public function calculatePayableBasicSalary($empId, $month, $year, $adminId = null)
    {
        $employee = Employee::where('emp_id', $empId)->first();
        if (!$employee) {
            return null;
        }
        
        $salary = \App\Models\Salary::where('emp_id', $empId)->where('is_active', true)->first();
        if (!$salary) {
            $salary = \App\Models\Salary::where('emp_id', $empId)->latest()->first();
        }
        if (!$salary) {
            return null;
        }

        // Load schedule exceptions with admin priority
        $this->loadScheduleExceptions($month, $year, $adminId);

        // Step 1: Calculate Total Working Days (Denominator)
        $filteredTotalDays = $this->calculateFilteredTotalDays($empId, $month, $year);
        $perDayRate = $salary->basic_salary / $filteredTotalDays;

        // Step 2: Calculate Payable Attendance Days (Multiplier)
        $payableAttendanceDays = $this->calculatePayableAttendanceDays($empId, $month, $year);

        // Step 3: Final Calculation
        $payableBasicSalary = $perDayRate * $payableAttendanceDays;

        return [
            'original_basic_salary' => $salary->basic_salary,
            'filtered_total_days' => $filteredTotalDays,
            'per_day_rate' => $perDayRate,
            'payable_attendance_days' => $payableAttendanceDays,
            'payable_basic_salary' => $payableBasicSalary
        ];
    }

    public function shouldIncludeEmployeeInReport($empId, $month, $year)
    {
        $employee = Employee::where('emp_id', $empId)->first();
        if (!$employee) {
            return false;
        }
        
        $requestedMonthStart = Carbon::create($year, $month, 1);
        $requestedMonthEnd = $requestedMonthStart->copy()->endOfMonth();
        
        // Check if employee was hired after the requested month
        if ($employee->hire_date && Carbon::parse($employee->hire_date)->greaterThan($requestedMonthEnd)) {
            return false;
        }
        
        // Check if employee ended before the requested month
        if ($employee->end_date && Carbon::parse($employee->end_date)->lessThan($requestedMonthStart)) {
            return false;
        }
        
        return true;
    }

    public function calculatePayableDays($empId, $month, $year, $adminId = null)
    {
        $check = $this->loadScheduleExceptions($month, $year, $adminId);

        
        
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        // Calculate working days (excluding weekends unless overridden)
        $totalWorkingDays = $this->calculateFilteredTotalDays($empId, $month, $year);
        
        // Calculate payable days
        $payableDays = $this->calculatePayableAttendanceDays($empId, $month, $year);
        
        // Calculate attendance breakdown with new logic
        $attendanceBreakdown = $this->calculateAttendanceBreakdown($empId, $month, $year, $check);
        
        return $attendanceBreakdown;

        return array_merge($attendanceBreakdown, [
            'total_working_days' => $totalWorkingDays,
            'payable_days' => $payableDays
        ]);
    }

    private function calculateFilteredTotalDays($empId, $month, $year)
    {
        $employee = Employee::where('emp_id', $empId)->first();
        if (!$employee) {
            return 0;
        }
        
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        // Adjust dates based on employee hire_date and end_date
        if ($employee->hire_date && Carbon::parse($employee->hire_date)->greaterThan($startDate)) {
            $startDate = Carbon::parse($employee->hire_date);
        }
        
        if ($employee->end_date && Carbon::parse($employee->end_date)->lessThan($endDate)) {
            $endDate = Carbon::parse($employee->end_date);
        }
        
        // If employee wasn't active during this month
        if ($startDate->greaterThan($endDate)) {
            return 0;
        }
        
        $totalDays = $endDate->diffInDays($startDate) + 1;
        $excludedDays = 0;
        
        $currentDate = $startDate->copy();
        while ($currentDate->lessThanOrEqualTo($endDate)) {
            $date = $currentDate->format('Y-m-d');
            $isWeekend = $this->isWeekendDay($date);
            $scheduleException = $this->getScheduleException($date);
            
            // Check if day should be excluded from working days
            if ($isWeekend) {
                // Weekend day - exclude unless marked as working day
                if (!($scheduleException && $scheduleException->type === 'working_day')) {
                    $excludedDays++;
                }
            } else {
                // Regular day - exclude if marked as holiday
                if ($scheduleException && $scheduleException->type === 'holiday') {
                    $excludedDays++;
                }
            }
            
            $currentDate->addDay();
        }
        
        return $totalDays - $excludedDays;
    }

    private function getSecondAndFourthSaturdays($month, $year)
    {
        $saturdays = [];
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        $saturdayCount = 0;
        for ($day = 1; $day <= $endDate->day; $day++) {
            $date = Carbon::create($year, $month, $day);
            if ($date->isSaturday()) {
                $saturdayCount++;
                if ($saturdayCount == 2 || $saturdayCount == 4) {
                    $saturdays[] = $date->format('Y-m-d');
                }
            }
        }
        
        return $saturdays;
    }

    private function calculatePayableAttendanceDays($empId, $month, $year)
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $totalAttendanceDays = 0.0;
        
        for ($day = 1; $day <= $endDate->day; $day++) {
            $date = Carbon::create($year, $month, $day)->format('Y-m-d');
            $dayAttendance = $this->calculateDayAttendance($empId, $date);
            $totalAttendanceDays += $dayAttendance;
        }
        
        return round($totalAttendanceDays, 1);
    }

    private function calculateDayAttendance($empId, $date)
    {
        // First check if there's an approved half-day application for this date
        $halfDayApplication = \App\Models\Application::where('employee_id', $empId)
            ->where('req_type', 'half_leave')
            ->where('status', 'approved')
            ->whereDate('start_date', $date)
            ->exists();
            
        if ($halfDayApplication) {
            return 0.5;
        }
        
        $entries = TimeEntry::where('employee_id', $empId)
            ->whereDate('entry_time', $date)
            ->get();
            
        if ($entries->isEmpty()) {
            return 0;
        }
        
        // Check for full day entries
        $fullDayTypes = ['holiday', 'casual_leave', 'sick_leave', 'regularization'];
        if ($entries->whereIn('entry_type', $fullDayTypes)->count() > 0) {
            return 1.0;
        }
        
        // Check for half day in time entries (legacy support)
        if ($entries->where('entry_type', 'half_day')->count() > 0) {
            return 0.5;
        }
        
        // Calculate punch in/out hours
        $punchIns = $entries->where('entry_type', 'punch_in')->sortBy('entry_time');
        $punchOuts = $entries->where('entry_type', 'punch_out')->sortBy('entry_time');
        
        if ($punchIns->isEmpty()) {
            return 0;
        }
        
        $totalWorkedMinutes = 0;
        $punchInArray = $punchIns->values();
        $punchOutArray = $punchOuts->values();
        
        for ($i = 0; $i < $punchInArray->count(); $i++) {
            $punchIn = Carbon::parse($punchInArray[$i]->entry_time);
            $punchOut = isset($punchOutArray[$i]) ? 
                Carbon::parse($punchOutArray[$i]->entry_time) : 
                Carbon::parse($date . ' 18:00:00'); // Default end time if no punch out
                
            $totalWorkedMinutes += $punchIn->diffInMinutes($punchOut);
        }
        
        $totalWorkedHours = $totalWorkedMinutes / 60;
        $minWorkingHours = $this->getMinWorkingHours();
        
        return $totalWorkedHours >= $minWorkingHours ? 1.0 : 0.5;
    }
    
    private function calculateAttendanceBreakdown($empId, $month, $year, $check = null)
    {

        


        $employee = Employee::where('emp_id', $empId)->first();
        if (!$employee) {
            return [
                'present_days' => 0, 'absent_days' => 0, 'half_days' => 0,
                'sick_leave' => 0, 'casual_leave' => 0, 'regularization' => 0,
                'holidays' => 0, 'short_attendance' => 0
            ];
        }
        
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        // Adjust dates based on employee hire_date and end_date
        if ($employee->hire_date && Carbon::parse($employee->hire_date)->greaterThan($startDate)) {
            $startDate = Carbon::parse($employee->hire_date);
        }
        
        if ($employee->end_date && Carbon::parse($employee->end_date)->lessThan($endDate)) {
            $endDate = Carbon::parse($employee->end_date);
        }
        
        // If employee wasn't active during this month
        if ($startDate->greaterThan($endDate)) {
            return [
                'present_days' => 0, 'absent_days' => 0, 'half_days' => 0,
                'sick_leave' => 0, 'casual_leave' => 0, 'regularization' => 0,
                'holidays' => 0, 'short_attendance' => 0
            ];
        }
        
        $presentDays = 0;
        $absentDays = 0;
        $halfDays = 0;
        $sickLeave = 0;
        $casualLeave = 0;
        $regularization = 0;
        $holidays = 0;
        $shortAttendance = 0;
        $wfhDays = 0;
        
        $currentDate = $startDate->copy();
        while ($currentDate->lessThanOrEqualTo($endDate)) {
            $date = $currentDate->format('Y-m-d');
            
            // Check for approved half-day application first
            $halfDayApplication = \App\Models\Application::where('employee_id', $empId)
                ->where('req_type', 'half_leave')
                ->where('status', 'approved')
                ->whereDate('start_date', $date)
                ->exists();
                
            if ($halfDayApplication) {
                $halfDays++;
                $currentDate->addDay();
                continue;
            }
            
            $entries = TimeEntry::where('employee_id', $empId)
                ->whereDate('entry_time', $date)
                ->get();
            
            // Check if day is weekend according to policy and schedule exceptions
            $isWeekend = $this->isWeekendDay($date);
            $scheduleException = $this->getScheduleException($date);
            
            if ($entries->isEmpty()) {
                // No time entry for this day
                if ($isWeekend) {
                    // Weekend day with no entry
                    if ($scheduleException && $scheduleException->type === 'working_day') {
                        // Weekend overridden as working day but no entry = absent
                        $absentDays++;
                    } elseif ($scheduleException && $scheduleException->type === 'wfh') {
                        // Weekend marked as WFH but no entry = absent
                        $absentDays++;
                    } else {
                        // Regular weekend = week off
                        $holidays++;
                    }
                } else {
                    // Regular day with no entry
                    if ($scheduleException && $scheduleException->type === 'holiday') {
                        // Regular day marked as holiday
                        $holidays++;
                    } elseif ($scheduleException && $scheduleException->type === 'wfh') {
                        // Regular day marked as WFH but no entry = absent
                        $absentDays++;
                    } else {
                        // Regular day with no entry = absent
                        $absentDays++;
                    }
                }
            } else {
                // Has time entries - check entry types first, then apply WFH logic
                if ($entries->where('entry_type', 'holiday')->count() > 0) {
                    $holidays++;
                } elseif ($entries->where('entry_type', 'casual_leave')->count() > 0) {
                    $casualLeave++;
                } elseif ($entries->where('entry_type', 'sick_leave')->count() > 0) {
                    $sickLeave++;
                } elseif ($entries->where('entry_type', 'regularization')->count() > 0) {
                    $regularization++;
                } elseif ($entries->where('entry_type', 'half_day')->count() > 0) {
                    $halfDays++;
                } else {
                    // Calculate attendance for regular punch in/out days
                    $punchIns = $entries->where('entry_type', 'punch_in');
                    $punchOuts = $entries->where('entry_type', 'punch_out');
                    
                    if ($punchIns->isNotEmpty()) {
                        $dayAttendance = $this->calculateDayAttendance($empId, $date);
                        
                        // Check if this day should be counted as WFH
                        if ($scheduleException && $scheduleException->type === 'wfh') {
                            // Day marked as WFH with time entries = WFH day
                            $wfhDays++;
                        } elseif ($dayAttendance >= 1.0) {
                            $presentDays++;
                        } elseif ($dayAttendance > 0) {
                            $shortAttendance++;
                        } else {
                            $absentDays++;
                        }
                    } else {
                        // Has entries but no punch in/out = absent
                        $absentDays++;
                    }
                }
            }
            
            $currentDate->addDay();
        }


    //    this was for the debug purpose as exceptions are not worked as desired that time
        // if($check !== null){
        //     foreach($check as $row){
        //         if($row->type == 'holiday'){
        //             // $holidays++;
        //         }
        //         if($row->type == 'working_day'){
        //             // $holidays--;
        //         }
        //     }

        //     // return $get;
        // }
        
        
        return [
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'half_days' => $halfDays,
            'sick_leave' => $sickLeave,
            'casual_leave' => $casualLeave,
            'regularization' => $regularization,
            'holidays' => $holidays,
            'short_attendance' => $shortAttendance,
            'wfh_days' => $wfhDays
        ];
    }

    public function getDailyAttendanceDetails($empId, $month, $year, $adminId = null)
    {
        $this->loadScheduleExceptions($month, $year, $adminId);
        
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $dailyData = [];
        
        for ($day = 1; $day <= $endDate->day; $day++) {
            $date = Carbon::create($year, $month, $day);
            $dateStr = $date->format('Y-m-d');
            
            // Check for approved half-day application first
            $halfDayApplication = \App\Models\Application::where('employee_id', $empId)
                ->where('req_type', 'half_day')
                ->where('status', 'approved')
                ->whereDate('start_date', $dateStr)
                ->exists();
            
            $entries = TimeEntry::where('employee_id', $empId)
                ->whereDate('entry_time', $dateStr)
                ->get();
            
            $isWeekend = $this->isWeekendDay($dateStr);
            $scheduleException = $this->getScheduleException($dateStr);
            
            // Determine day status
            $status = 'absent';
            $payableValue = 0;
            
            // Check half-day application first
            if ($halfDayApplication) {
                $status = 'half_day';
                $payableValue = 0.5;
            } elseif ($entries->isEmpty()) {
                if ($isWeekend) {
                    if ($scheduleException && $scheduleException->type === 'working_day') {
                        $status = 'absent';
                        $payableValue = 0;
                    } else {
                        $status = 'week_off';
                        $payableValue = 1;
                    }
                } else {
                    if ($scheduleException && $scheduleException->type === 'holiday') {
                        $status = 'holiday';
                        $payableValue = 1;
                    } else {
                        $status = 'absent';
                        $payableValue = 0;
                    }
                }
            } else {
                // Has entries
                if ($entries->where('entry_type', 'holiday')->count() > 0) {
                    $status = 'holiday';
                    $payableValue = 1;
                } elseif ($entries->where('entry_type', 'casual_leave')->count() > 0) {
                    $status = 'casual_leave';
                    $payableValue = 1;
                } elseif ($entries->where('entry_type', 'sick_leave')->count() > 0) {
                    $status = 'sick_leave';
                    $payableValue = 1;
                } elseif ($entries->where('entry_type', 'week_off')->count() > 0) {
                    $status = 'week_off';
                    $payableValue = 1;
                } elseif ($entries->where('entry_type', 'half_day')->count() > 0) {
                    $status = 'half_day';
                    $payableValue = 0.5;
                } else {
                    $dayAttendance = $this->calculateDayAttendance($empId, $dateStr);
                    if ($dayAttendance >= 1.0) {
                        $status = 'present';
                        $payableValue = 1;
                    } elseif ($dayAttendance > 0) {
                        $status = 'short_attendance';
                        $payableValue = 0.5;
                    } else {
                        $status = 'absent';
                        $payableValue = 0;
                    }
                }
            }
            
            $dailyData[] = [
                'date' => $date,
                'day_name' => $date->format('l'),
                'status' => $status,
                'payable_value' => $payableValue,
                'is_weekend' => $isWeekend,
                'schedule_exception' => $scheduleException
            ];
        }
        
        return $dailyData;
    }
    
    private function getMinWorkingHours()
    {
        $setting = SystemSetting::where('setting_key', 'min_working_hours')->first();
        if (!$setting) {
            return 8.5; // Default 8.5 hours
        }
        
        $time = explode(':', $setting->setting_value);
        return (int)$time[0] + ((int)$time[1] / 60);
    }
}