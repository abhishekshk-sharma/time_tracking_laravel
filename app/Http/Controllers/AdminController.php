<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\TimeEntry;
use App\Models\Application;
use App\Models\Department;
use App\Models\SystemSetting;
use App\Models\Wfh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminController extends Controller
{

    public function dashboard()
    {
        $stats = [
            'total_employees' => Employee::where('role', 'employee')->count(),
            'active_employees' => Employee::where(['status' => 'active', 'role' => 'employee'])->count(),
            'inactive_employees' => Employee::where(['status' => 'inactive', 'role' => 'employee'])->count(),
            'present_today' => $this->getPresentToday(),
            'pending_applications' => Application::where('status', 'pending')->count(),
            'total_departments' => Department::count(),
        ];

        $recentApplications = Application::with('employee')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $todayAttendance = $this->getTodayAttendanceStats();

        return view('admin.dashboard.index', compact('stats', 'recentApplications', 'todayAttendance'));
    }

    public function employees(Request $request)
    {
        $query = Employee::where('role', 'employee');
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('emp_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('department') && $request->department) {
            $query->where('department_id', $request->department);
        }
        
        // Status filter - default to active employees only
        $status = $request->get('status', 'active');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $employees = $query->with('department')->paginate(15);
        $departments = Department::all();

        return view('admin.employees.index', compact('employees', 'departments'));
    }

    public function showEmployee(Employee $employee)
    {
        $employee->load('department');
        return view('admin.employees.show', compact('employee'));
    }
    
    public function employeeTimeHistory(Request $request, Employee $employee)
    {
        $filter = $request->get('filter', 'this_month');
        
        $query = TimeEntry::where('employee_id', $employee->emp_id);
        
        switch ($filter) {
            case 'this_month':
                $query->whereMonth('entry_time', now()->month)
                      ->whereYear('entry_time', now()->year);
                break;
            case 'last_month':
                $query->whereMonth('entry_time', now()->subMonth()->month)
                      ->whereYear('entry_time', now()->subMonth()->year);
                break;
            case 'custom':
                if ($request->start_date && $request->end_date) {
                    $query->whereBetween('entry_time', [$request->start_date, $request->end_date]);
                }
                break;
        }
        
        $allEntries = $query->orderBy('entry_time', 'desc')->get();
        
        // Group by date and then by entry type
        $groupedEntries = $allEntries->groupBy(function($entry) {
            return $entry->entry_time->format('Y-m-d');
        })->map(function($dayEntries) {
            return $dayEntries->keyBy('entry_type');
        })->sortKeysDesc();
        
        // Paginate manually
        $page = $request->get('page', 1);
        $perPage = 10;
        $total = $groupedEntries->count();
        $items = $groupedEntries->slice(($page - 1) * $perPage, $perPage);
        
        $paginatedEntries = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'pageName' => 'page']
        );
        
        return view('admin.employees.history', compact('employee', 'paginatedEntries', 'filter'));
    }

    public function createEmployee()
    {
        $departments = Department::all();
        return view('admin.employees.create', compact('departments'));
    }

    public function storeEmployee(Request $request)
    {
        $request->validate([
            'emp_id' => 'required|unique:employees,emp_id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'password' => 'required|min:6',
            'department_id' => 'required|exists:departments,id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        Employee::create([
            'emp_id' => $request->emp_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'department_id' => $request->department_id,
            'phone' => $request->phone,
            'address' => $request->address,
            'role' => 'employee',
            'status' => 'active',
        ]);

        return redirect()->route('admin.employees')->with('success', 'Employee created successfully');
    }

    public function editEmployee(Employee $employee)
    {
        $departments = Department::all();
        return view('admin.employees.edit', compact('employee', 'departments'));
    }

    public function updateEmployee(Request $request, Employee $employee)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'departments' => 'required|exists:departments,name',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'end_date' => 'nullable|date',
        ]);

        $updateData = $request->only(['username', 'email', 'departments', 'phone', 'address', 'status', 'end_date']);
        
        // If end_date is set, automatically make employee inactive
        if ($request->end_date) {
            $updateData['status'] = 'inactive';
        }

        $employee->update($updateData);

        if ($request->password) {
            $employee->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.employees')->with('success', 'Employee updated successfully');
    }

    public function applications(Request $request)
    {
        $query = Application::with('employee');

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('type') && $request->type) {
            $query->where('req_type', $request->type);
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.applications.index', compact('applications'));
    }

    public function updateApplicationStatus(Request $request, Application $application)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_remarks' => 'nullable|string',
        ]);

        $application->update([
            'status' => $request->status,
            'admin_remarks' => $request->admin_remarks,
            'action_by' => auth()->user()->emp_id,
            'action_date' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Application ' . $request->status . ' successfully']);
    }

    public function showApplication(Application $application)
    {
        $application->load('employee');
        return response()->json($application);
    }

    public function attendance(Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));
        $department = $request->get('department');

        $query = Employee::where('role', 'employee')
            ->where('status', 'active')
            ->with(['department', 'timeEntries' => function($q) use ($date) {
                $q->whereDate('entry_time', $date)->orderBy('entry_time');
            }]);

        if ($department) {
            $query->where('department_id', $department);
        }

        $employees = $query->get();
        $departments = Department::all();
        
        // Check if the date is Sunday or 2nd/4th Saturday
        $dateCarbon = \Carbon\Carbon::parse($date);
        $isWeekend = false;
        $weekendType = '';
        
        if ($dateCarbon->isSunday()) {
            $isWeekend = true;
            $weekendType = 'Sunday';
        } elseif ($dateCarbon->isSaturday()) {
            $weekOfMonth = ceil($dateCarbon->day / 7);
            if ($weekOfMonth == 2 || $weekOfMonth == 4) {
                $isWeekend = true;
                $weekendType = ($weekOfMonth == 2 ? 'Second' : 'Fourth') . ' Saturday';
            }
        }

        return view('admin.attendance.index', compact('employees', 'departments', 'date', 'isWeekend', 'weekendType'));
    }

    public function reports(Request $request)
    {
        $departments = Department::all();
        $employees = Employee::where('role', 'employee')->get();
        
        // Get recent reports (you can store these in a reports table later)
        $recentReports = collect([
            [
                'name' => 'Monthly Attendance Report - ' . now()->format('F Y'),
                'type' => 'Attendance',
                'generated_by' => auth()->user()->name,
                'date' => now()->subDays(5)->format('M d, Y'),
                'status' => 'Completed'
            ],
            [
                'name' => 'Leave Analysis Q4 2024',
                'type' => 'Leave',
                'generated_by' => auth()->user()->name,
                'date' => now()->subDays(10)->format('M d, Y'),
                'status' => 'Completed'
            ]
        ]);
        
        // Handle AJAX requests for report generation
        if ($request->ajax()) {
            $reportType = $request->get('report_type');
            
            switch ($reportType) {
                case 'attendance':
                    return $this->generateAttendanceReport($request);
                case 'leave':
                    return $this->generateLeaveReport($request);
                case 'performance':
                    return $this->generatePerformanceReport($request);
                case 'analytics':
                    return $this->generateAnalyticsReport($request);
                default:
                    return response()->json(['error' => 'Invalid report type'], 400);
            }
        }
        
        return view('admin.reports.index', compact('departments', 'employees', 'recentReports'));
    }

    private function getPresentToday()
    {
        return TimeEntry::whereDate('entry_time', today())
            ->where('entry_type', 'punch_in')
            ->distinct('employee_id')
            ->count();
    }

    private function getTodayAttendanceStats()
    {
        $totalEmployees = Employee::where('role', 'employee')->count();
        $presentToday = $this->getPresentToday();
        $absentToday = $totalEmployees - $presentToday;

        return [
            'total' => $totalEmployees,
            'present' => $presentToday,
            'absent' => $absentToday,
            'percentage' => $totalEmployees > 0 ? round(($presentToday / $totalEmployees) * 100, 1) : 0
        ];
    }

    public function departments()
    {
        $departments = Department::withCount('employees')->get();
        return view('admin.departments.index', compact('departments'));
    }

    public function storeDepartment(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string'
        ]);

        Department::create($request->only(['name', 'description']));
        return response()->json(['success' => true]);
    }

    public function timeEntries(Request $request)
    {
        $query = TimeEntry::with('employee');
        
        if ($request->employee) {
            $query->where('employee_id', $request->employee);
        }
        
        if ($request->from_date) {
            $query->whereDate('entry_time', '>=', $request->from_date);
        }
        
        if ($request->to_date) {
            $query->whereDate('entry_time', '<=', $request->to_date);
        }
        
        $timeEntries = $query->orderBy('entry_time')->paginate(20);
        $employees = Employee::where('role', 'employee')->get();
        
        // Return JSON for AJAX requests
        if ($request->wantsJson() || $request->ajax()) {
            // Convert times to IST for JSON response
            $entries = collect($timeEntries->items())->map(function($entry) {
                return [
                    'id' => $entry->id,
                    'employee_id' => $entry->employee_id,
                    'entry_type' => $entry->entry_type,
                    'entry_time' => $entry->entry_time->timezone('Asia/Kolkata')->format('Y-m-d H:i:s'),
                    'notes' => $entry->notes,
                ];
            });
            
            return response()->json([
                'entries' => $entries
            ]);
        }
        
        return view('admin.time-entries.index', compact('timeEntries', 'employees'));
    }

    public function deleteDepartment(Department $department)
    {
        if ($department->employees()->count() > 0) {
            return response()->json(['error' => 'Cannot delete department with employees'], 400);
        }
        
        $department->delete();
        return response()->json(['success' => true]);
    }

    public function employeeHistory(Request $request)
    {
        $query = Employee::where(['role' => 'employee', 'status' => 'active']);
        
        // Handle department filter
        if ($request->department && $request->department !== 'all') {
            $query->where('department', $request->department);
        }
        
        // Handle search filter
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('username', 'like', "%{$request->search}%")
                  ->orWhere('emp_id', 'like', "%{$request->search}%");
            });
        }
        
        // Handle date range based on period
        $period = $request->get('period', 'current');
        switch ($period) {
            case 'current':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
            case 'last':
                $startDate = now()->subMonth()->startOfMonth();
                $endDate = now()->subMonth()->endOfMonth();
                break;
            case 'custom':
                $startDate = $request->start_date ? Carbon::parse($request->start_date) : now()->startOfMonth();
                $endDate = $request->end_date ? Carbon::parse($request->end_date) : now()->endOfMonth();
                break;
            default:
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
        }
        
        // Get employees with their time entries for the selected period
        $employees = $query->with(['timeEntries' => function($q) use ($startDate, $endDate) {
            $q->whereBetween('entry_time', [$startDate, $endDate])
              ->orderBy('entry_time', 'asc');
        }])->paginate(1);
        
        $departments = Department::all();
        
        return view('admin.employee-history.index', compact('employees', 'departments'));
    }

    public function workFromHome(Request $request)
    {
        $query = Application::with('employee')->where('req_type', 'work_from_home');
        
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->fromdate && $request->todate) {
            $query->whereBetween('created_at', [$request->fromdate, $request->todate]);
        }
        
        $wfhRequests = $query->orderBy('created_at')->paginate(15);
        
        return view('admin.wfh.index', compact('wfhRequests'));
    }

    public function deleteTimeEntry(TimeEntry $timeEntry)
    {
        $timeEntry->delete();
        return response()->json(['success' => true]);
    }

    public function exportAttendance(Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));
        $employees = Employee::where('role', 'employee')
            ->with(['timeEntries' => function($q) use ($date) {
                $q->whereDate('entry_time', $date);
            }])
            ->get();

        $csvData = [];
        $csvData[] = ['Employee ID', 'Name', 'Status', 'Punch In', 'Punch Out', 'Working Hours'];
        
        foreach ($employees as $employee) {
            $punchIn = $employee->timeEntries->where('entry_type', 'punch_in')->first();
            $punchOut = $employee->timeEntries->where('entry_type', 'punch_out')->first();
            
            $status = $punchIn ? 'Present' : 'Absent';
            $punchInTime = $punchIn ? $punchIn->entry_time->format('H:i') : '-';
            $punchOutTime = $punchOut ? $punchOut->entry_time->format('H:i') : '-';
            $workingHours = ($punchIn && $punchOut) ? $punchOut->entry_time->diffInMinutes($punchIn->entry_time) . ' min' : '-';
            
            $csvData[] = [
                $employee->emp_id,
                $employee->name,
                $status,
                $punchInTime,
                $punchOutTime,
                $workingHours
            ];
        }

        $filename = 'attendance_' . $date . '.csv';
        $handle = fopen('php://output', 'w');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        
        fclose($handle);
        exit;
    }

    public function schedule(Request $request)
    {
        $currentMonth = $request->get('month', now()->month);
        $currentYear = $request->get('year', now()->year);
        
        // Handle holiday submission
        if ($request->isMethod('post') && $request->has('add_holiday')) {
            return $this->addHoliday($request, $currentMonth, $currentYear);
        }
        
        // Handle holiday deletion
        if ($request->isMethod('post') && $request->has('delete_holiday')) {
            return $this->deleteHoliday($request, $currentMonth, $currentYear);
        }
        
        // Handle holiday update
        if ($request->isMethod('post') && $request->has('update_holiday')) {
            return $this->updateHoliday($request, $currentMonth, $currentYear);
        }
        
        $firstDayOfMonth = Carbon::create($currentYear, $currentMonth, 1)->startOfDay();
        $lastDayOfMonth = Carbon::create($currentYear, $currentMonth, 1)->endOfMonth();
        
        // Get time entries for the month
        $timeEntries = TimeEntry::with('employee')
            ->whereBetween('entry_time', [$firstDayOfMonth, $lastDayOfMonth])
            ->whereHas('employee', function($q) {
                $q->where('role', '!=', 'admin');
            })
            ->orderBy('entry_time')
            ->get();
            
        // Get holidays for listing
        $holidays = TimeEntry::with('employee')
            ->where('entry_type', 'holiday')
            ->whereMonth('entry_time', $currentMonth)
            ->whereYear('entry_time', $currentYear)
            ->whereHas('employee', function($q) {
                $q->whereNull('end_date');
            })
            ->get()
            ->groupBy(function($item) {
                return $item->entry_time->format('Y-m-d') . '|' . $item->notes;
            })
            ->map(function($group) {
                $first = $group->first();
                return [
                    'id' => $first->id,
                    'entry_time' => $first->entry_time,
                    'notes' => $first->notes,
                    'employee_count' => $group->count(),
                    'affected_employees' => $group->pluck('employee.name')->join(', ')
                ];
            })
            ->values();
            
        $settings = SystemSetting::pluck('setting_value', 'setting_key');
        
        return view('admin.schedule.index', compact(
            'timeEntries', 'holidays', 'settings', 'currentMonth', 'currentYear'
        ));
    }
    
    private function addHoliday(Request $request, $currentMonth, $currentYear)
    {
        $request->validate([
            'title' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string'
        ]);
        
        $employees = Employee::where('status', 'active')
            ->whereNull('end_date')
            ->where('role', '!=', 'admin')
            ->pluck('emp_id');
            
        $startDate = Carbon::parse($request->start_date);
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : $startDate;
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            foreach ($employees as $employeeId) {
                $exists = TimeEntry::where('employee_id', $employeeId)
                    ->whereDate('entry_time', $date)
                    ->where('entry_type', 'holiday')
                    ->exists();
                    
                if (!$exists) {
                    TimeEntry::create([
                        'employee_id' => $employeeId,
                        'entry_type' => 'holiday',
                        'entry_time' => $date->format('Y-m-d 00:00:00'),
                        'notes' => 'Holiday: ' . $request->title . ($request->description ? ' - ' . $request->description : '')
                    ]);
                }
            }
        }
        
        return redirect()->route('admin.schedule', ['month' => $currentMonth, 'year' => $currentYear])
            ->with('success', 'Holiday successfully added for all employees!');
    }
    
    private function deleteHoliday(Request $request, $currentMonth, $currentYear)
    {
        $holidayTime = Carbon::parse($request->holiday_time)->format('Y-m-d');
        
        TimeEntry::where('entry_type', 'holiday')
            ->whereDate('entry_time', $holidayTime)
            ->delete();
            
        return redirect()->route('admin.schedule', ['month' => $currentMonth, 'year' => $currentYear])
            ->with('success', 'Holiday successfully deleted!');
    }
    
    private function updateHoliday(Request $request, $currentMonth, $currentYear)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string'
        ]);
        
        $holidayTime = Carbon::parse($request->update_holiday)->format('Y-m-d');
        $notes = 'Holiday: ' . $request->title . ($request->description ? ' - ' . $request->description : '');
        
        TimeEntry::where('entry_type', 'holiday')
            ->whereDate('entry_time', $holidayTime)
            ->update(['notes' => $notes]);
            
        return redirect()->route('admin.schedule', ['month' => $currentMonth, 'year' => $currentYear])
            ->with('success', 'Holiday successfully updated!');
    }

    public function updateWfhStatus(Request $request, $wfhId)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_remarks' => 'nullable|string',
        ]);

        $wfh = Application::findOrFail($wfhId);
        $wfh->update([
            'status' => $request->status,
            'admin_remarks' => $request->admin_remarks,
            'action_by' => auth()->user()->emp_id,
            'action_date' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'WFH request ' . $request->status . ' successfully']);
    }

    private function generateAttendanceReport(Request $request)
    {
        $type = $request->get('type', 'daily');
        $department = $request->get('department');
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        
        $query = Employee::where('role', 'employee')
            ->with(['timeEntries' => function($q) use ($startDate, $endDate) {
                $q->whereBetween('entry_time', [$startDate, $endDate]);
            }]);
            
        if ($department) {
            $query->where('department', $department);
        }
        
        $employees = $query->get();
        
        $reportData = [];
        foreach ($employees as $employee) {
            $timeEntries = $employee->timeEntries->groupBy(function($entry) {
                return $entry->entry_time->format('Y-m-d');
            });
            
            $totalDays = 0;
            $presentDays = 0;
            $totalHours = 0;
            
            foreach ($timeEntries as $date => $entries) {
                $totalDays++;
                $punchIn = $entries->where('entry_type', 'punch_in')->first();
                $punchOut = $entries->where('entry_type', 'punch_out')->first();
                
                if ($punchIn) {
                    $presentDays++;
                    if ($punchOut) {
                        $totalHours += $punchIn->entry_time->diffInHours($punchOut->entry_time);
                    }
                }
            }
            
            $reportData[] = [
                'employee_id' => $employee->emp_id,
                'name' => $employee->username,
                'department' => $employee->department,
                'total_days' => $totalDays,
                'present_days' => $presentDays,
                'absent_days' => $totalDays - $presentDays,
                'attendance_rate' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0,
                'total_hours' => round($totalHours, 2)
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $reportData,
            'summary' => [
                'total_employees' => count($reportData),
                'avg_attendance_rate' => count($reportData) > 0 ? round(collect($reportData)->avg('attendance_rate'), 2) : 0,
                'total_hours' => collect($reportData)->sum('total_hours')
            ]
        ]);
    }
    
    private function generateLeaveReport(Request $request)
    {
        $period = $request->get('period', 'current_month');
        $leaveType = $request->get('leave_type');
        
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();
        
        switch ($period) {
            case 'last_month':
                $startDate = now()->subMonth()->startOfMonth();
                $endDate = now()->subMonth()->endOfMonth();
                break;
            case 'quarter':
                $startDate = now()->startOfQuarter();
                $endDate = now()->endOfQuarter();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
        }
        
        $query = Application::with('employee')
            ->whereBetween('created_at', [$startDate, $endDate]);
            
        if ($leaveType) {
            $query->where('req_type', $leaveType);
        }
        
        $applications = $query->get();
        
        $reportData = [
            'total_applications' => $applications->count(),
            'approved' => $applications->where('status', 'approved')->count(),
            'rejected' => $applications->where('status', 'rejected')->count(),
            'pending' => $applications->where('status', 'pending')->count(),
            'by_type' => $applications->groupBy('req_type')->map->count(),
            'by_employee' => $applications->groupBy('employee_id')->map(function($apps) {
                return [
                    'count' => $apps->count(),
                    'employee' => $apps->first()->employee->username ?? 'Unknown'
                ];
            })
        ];
        
        return response()->json([
            'success' => true,
            'data' => $reportData
        ]);
    }
    
    private function generatePerformanceReport(Request $request)
    {
        $employeeId = $request->get('employee');
        $metrics = $request->get('metrics', 'comprehensive');
        
        $query = Employee::where('role', 'employee')
            ->with(['timeEntries', 'applications']);
            
        if ($employeeId) {
            $query->where('emp_id', $employeeId);
        }
        
        $employees = $query->get();
        
        $reportData = [];
        foreach ($employees as $employee) {
            $timeEntries = $employee->timeEntries->where('entry_time', '>=', now()->subMonth());
            $applications = $employee->applications->where('created_at', '>=', now()->subMonth());
            
            $workingDays = $timeEntries->groupBy(function($entry) {
                return $entry->entry_time->format('Y-m-d');
            });
            
            $presentDays = $workingDays->filter(function($entries) {
                return $entries->where('entry_type', 'punch_in')->count() > 0;
            })->count();
            
            $lateDays = $workingDays->filter(function($entries) {
                $punchIn = $entries->where('entry_type', 'punch_in')->first();
                return $punchIn && $punchIn->entry_time->format('H:i') > '09:15';
            })->count();
            
            $totalWorkingDays = now()->subMonth()->diffInWeekdays(now());
            
            $reportData[] = [
                'employee_id' => $employee->emp_id,
                'name' => $employee->username,
                'department' => $employee->department,
                'attendance_rate' => $totalWorkingDays > 0 ? round(($presentDays / $totalWorkingDays) * 100, 2) : 0,
                'punctuality_score' => $presentDays > 0 ? round((($presentDays - $lateDays) / $presentDays) * 100, 2) : 0,
                'leave_applications' => $applications->count(),
                'approved_leaves' => $applications->where('status', 'approved')->count()
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $reportData
        ]);
    }
    
    private function generateAnalyticsReport(Request $request)
    {
        $type = $request->get('type', 'usage');
        $range = $request->get('range', '30days');
        
        $days = match($range) {
            '7days' => 7,
            '30days' => 30,
            '90days' => 90,
            '1year' => 365,
            default => 30
        };
        
        $startDate = now()->subDays($days);
        
        $analytics = [
            'total_employees' => Employee::where('role', 'employee')->count(),
            'active_employees' => Employee::where('role', 'employee')->where('status', 'active')->count(),
            'total_time_entries' => TimeEntry::where('entry_time', '>=', $startDate)->count(),
            'total_applications' => Application::where('created_at', '>=', $startDate)->count(),
            'daily_attendance' => TimeEntry::where('entry_time', '>=', $startDate)
                ->where('entry_type', 'punch_in')
                ->selectRaw('DATE(entry_time) as date, COUNT(DISTINCT employee_id) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            'department_wise' => Employee::where('role', 'employee')
                ->selectRaw('department, COUNT(*) as count')
                ->groupBy('department')
                ->get()
        ];
        
        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }
    
    public function exportReport(Request $request, $type)
    {
        $reportData = null;
        
        switch ($type) {
            case 'attendance':
                $reportData = $this->generateAttendanceReport($request);
                break;
            case 'leave':
                $reportData = $this->generateLeaveReport($request);
                break;
            case 'performance':
                $reportData = $this->generatePerformanceReport($request);
                break;
            case 'analytics':
                $reportData = $this->generateAnalyticsReport($request);
                break;
        }
        
        if (!$reportData) {
            return response()->json(['error' => 'Invalid report type'], 400);
        }
        
        $data = json_decode($reportData->getContent(), true)['data'];
        
        $filename = $type . '_report_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($data, $type) {
            $file = fopen('php://output', 'w');
            
            switch ($type) {
                case 'attendance':
                    fputcsv($file, ['Employee ID', 'Name', 'Department', 'Present Days', 'Absent Days', 'Attendance Rate %', 'Total Hours']);
                    foreach ($data as $row) {
                        fputcsv($file, [
                            $row['employee_id'],
                            $row['name'],
                            $row['department'],
                            $row['present_days'],
                            $row['absent_days'],
                            $row['attendance_rate'],
                            $row['total_hours']
                        ]);
                    }
                    break;
                    
                case 'performance':
                    fputcsv($file, ['Employee ID', 'Name', 'Department', 'Attendance Rate %', 'Punctuality Score %', 'Leave Applications']);
                    foreach ($data as $row) {
                        fputcsv($file, [
                            $row['employee_id'],
                            $row['name'],
                            $row['department'],
                            $row['attendance_rate'],
                            $row['punctuality_score'],
                            $row['leave_applications']
                        ]);
                    }
                    break;
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}