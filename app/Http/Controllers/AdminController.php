<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveCount;
use App\Models\TimeEntry;
use App\Models\Application;
use App\Models\AppNotification;
use App\Models\Department;
use App\Models\SystemSetting;
use App\Models\Wfh;
use App\Models\Salary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Validation\Rule; 


class AdminController extends Controller
{

    public function dashboard()
    {
        $adminEmpId = auth()->user()->emp_id;
        
        $stats = [
            'total_employees' => Employee::where('role', 'employee')->where('referrance', $adminEmpId)->count(),
            'active_employees' => Employee::where(['status' => 'active', 'role' => 'employee'])->where('referrance', $adminEmpId)->count(),
            'inactive_employees' => Employee::where(['status' => 'inactive', 'role' => 'employee'])->where('referrance', $adminEmpId)->count(),
            'present_today' => $this->getPresentToday(),
            'pending_applications' => Application::whereHas('employee', function($q) use ($adminEmpId) {
                $q->where('referrance', $adminEmpId);
            })->where('status', 'pending')->count(),
            'total_departments' => Department::count(),
        ];

        $recentApplications = Application::with('employee')
            ->whereHas('employee', function($q) use ($adminEmpId) {
                $q->where('referrance', $adminEmpId);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $todayAttendance = $this->getTodayAttendanceStats();

        return view('admin.dashboard.index', compact('stats', 'recentApplications', 'todayAttendance'));
    }

    public function employees(Request $request)
    {
        $adminEmpId = auth()->user()->emp_id;
        
        $query = Employee::where('role', 'employee')
            ->where('referrance', $adminEmpId);
        
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
        if ($request->has('grade_filter') && $request->grade_filter) {
            $query->where('senior_junior', $request->grade_filter);
        }
        // return $request->grade_filter;
        
        // Status filter - default to active employees only
        $status = $request->get('status', 'active');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $employees = $query->with('department')->paginate(15);
        $departments = Department::where(function($q){
            $q->whereNotIn('name', ['admin']);
        })->get();

        // return $employees->first()->department->name;
        return view('admin.employees.index', compact('employees', 'departments'));
    }

    public function showEmployee(Employee $employee)
    {
        $adminEmpId = auth()->user()->emp_id;
        
        // Check if the employee belongs to the current admin
        if ($employee->referrance !== $adminEmpId) {
            return redirect()->route('admin.employees')
                ->with('error', 'You do not have permission to view this employee.');
        }
        
        $employee->load('department');
        return view('admin.employees.show', compact('employee'));
    }
    
    public function employeeTimeHistory(Request $request, Employee $employee)
    {
        $adminEmpId = auth()->user()->emp_id;
        
        // Check if the employee belongs to the current admin
        if ($employee->referrance !== $adminEmpId) {
            return redirect()->route('admin.employees')
                ->with('error', 'You do not have permission to view this employee.');
        }
        
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
            'username' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'senior_junior' => 'nullable|in:senior,junior',
            'metro_city' => 'nullable|boolean',
            'hiredate' => [ 
                'required', 
                'date', 
                'beforeOrEqual:' . Carbon::now()->addDays(30)->toDateString(), 
            ],
            
            'dob' => 
            [ 
                'required', 
                'date', 
                'beforeOrEqual:' . Carbon::now()->subYears(18)->toDateString(), 
            ],
            'email' => 'required|email|unique:employees,email',
            'password' => 'required|min:6',
            'department_id' => 'required|exists:departments,id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            
        ]);
        $hashpassword = Hash::make($request->password);
        Employee::create([
            'emp_id' => $request->emp_id,
            'full_name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password_hash' => $hashpassword,
            'department_id' => $request->department_id,
            'hire_date' => $request->hiredate,
            'dob' => $request->dob,
            'phone' => $request->phone,
            'address' => $request->address,
            'referrance' => auth()->user()->emp_id,
            'position' => $request->position,
            'senior_junior' => $request->senior_junior,
            'metro_city' => $request->metro_city ? 1 : 0,
            'role' => 'employee',
            'status' => 'active',
        ]);

        return redirect()->route('admin.employees')->with('success', 'Employee created successfully');
    }

    public function editEmployee(Employee $employee)
    {
        $adminEmpId = auth()->user()->emp_id;

        // $departments = departments::get();
        
        // Check if the employee belongs to the current admin
        if ($employee->referrance !== $adminEmpId) {
            return redirect()->route('admin.employees')
                ->with('error', 'You do not have permission to edit this employee.');
        }
        
        $departments = Department::get();
        $leaveCount = $employee->leaveCount;
        return view('admin.employees.edit', compact('employee', 'departments', 'leaveCount'));
    }

    public function updateEmployee(Request $request, Employee $employee)
    {
        $adminEmpId = auth()->user()->emp_id;
        
        // Check if the employee belongs to the current admin
        if ($employee->referrance !== $adminEmpId) {
            return redirect()->route('admin.employees')
                ->with('error', 'You do not have permission to update this employee.');
        }
        
        $request->validate([
            'username' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'senior_junior' => 'nullable|in:senior,junior',
            'metro_city' => 'nullable|boolean',
            'hire_date' => [ 
                'required', 
                'date', 
                'beforeOrEqual:' . Carbon::now()->addDays(30)->toDateString(), 
            ],
            
            'dob' => 
            [ 
                'required', 
                'date', 
                'beforeOrEqual:' . Carbon::now()->subYears(18)->toDateString(), 
            ],
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'department_id' => 'required|exists:departments,id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'end_date' => 'nullable|date',
            'casual_leave' => 'nullable|integer|min:0|max:365',
            'sick_leave' => 'nullable|integer|min:0|max:365',
        ]);

        $updateData = $request->only(['username', 'email', 'full_name', 'hire_date', 'dob', 'position', 'department_id', 'phone', 'address', 'status', 'end_date', 'senior_junior']);
        
        // Handle metro_city boolean conversion
        $updateData['metro_city'] = $request->metro_city ? 1 : 0;
        
        // If end_date is set, automatically make employee inactive
        if ($request->end_date) {
            $updateData['status'] = 'inactive';
        }

        $employee->update($updateData);

        if ($request->password) {
            $employee->update(['password_hash' => Hash::make($request->password)]);
        }

        // Update or create leave count
        if ($request->has('casual_leave') || $request->has('sick_leave')) {
            LeaveCount::updateOrCreate(
                ['employee_id' => $employee->emp_id],
                [
                    'casual_leave' => $request->casual_leave ?? 0,
                    'sick_leave' => $request->sick_leave ?? 0,
                ]
            );
        }

        return redirect()->route('admin.employees')->with('success', 'Employee updated successfully');
    }

    public function applications(Request $request)
    {
        $adminEmpId = auth()->user()->emp_id;
        
        $query = Application::with('employee')
            ->whereHas('employee', function($q) use ($adminEmpId) {
                $q->where('referrance', $adminEmpId);
            });

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('type') && $request->type) {
            $query->where('req_type', $request->type);
        }
        
        if ($request->has('search') && $request->search) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('username', 'like', '%' . $request->search . '%')
                  ->orWhere('emp_id', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->has('employee_status') && $request->employee_status !== 'all') {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('status', $request->employee_status);
            });
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate(15);
        // return $applications;
        return view('admin.applications.index', compact('applications'));
    }

    public function updateApplicationStatus(Request $request, Application $application)
    {
        try {
            \Log::info('Admin updateApplicationStatus called', [
                'application_id' => $application->id,
                'request_data' => $request->all(),
                'admin_emp_id' => auth()->user()->emp_id
            ]);
            
            $adminEmpId = auth()->user()->emp_id;
            
            // Check if admin has permission to update this application
            if ($application->employee->referrance !== $adminEmpId) {
                \Log::warning('Admin permission denied', [
                    'admin_emp_id' => $adminEmpId,
                    'employee_referrance' => $application->employee->referrance
                ]);
                return response()->json(['error' => 'You do not have permission to update this application.'], 403);
            }
            
            $request->validate([
                'status' => 'required|in:approved,rejected',
            ]);
            
            // Validate before updating status for approved applications
            if ($request->status === 'approved') {
                $this->validateApplicationForApproval($application);
            }

            $application->update([
                'status' => $request->status,
                'action_by' => auth()->user()->emp_id,
            ]);
            
            // Create time entries for approved applications
            if ($request->status === 'approved') {
                $this->createTimeEntryForApplication($application);
            }

            \Log::info('Application status updated successfully', ['application_id' => $application->id]);
            // Create notification for employee
        AppNotification::create([
            'App_id' => $application->id,
            'created_by' => Auth::user()->emp_id,
            'notify_to' => $application->employee_id
        ]);

        return response()->json(['success' => true, 'message' => 'Application ' . $request->status . ' successfully']);
        } catch (\Exception $e) {
            \Log::error('Application status update error: ' . $e->getMessage(), [
                'application_id' => $application->id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to update application status: ' . $e->getMessage()], 500);
        }
    }
    
    private function validateApplicationForApproval(Application $application)
    {
        if (in_array($application->req_type, ['casual_leave', 'sick_leave', 'regularization'])) {
            $startDate = \Carbon\Carbon::parse($application->start_date);
            $endDate = $application->end_date ? \Carbon\Carbon::parse($application->end_date) : $startDate;
            
            $currentDate = $startDate->copy();
            $conflictDates = [];
            
            while ($currentDate <= $endDate) {
                $existingEntry = TimeEntry::where('employee_id', $application->employee_id)
                    ->whereDate('entry_time', $currentDate->format('Y-m-d'))
                    ->first();
                
                if ($existingEntry) {
                    $conflictDates[] = $currentDate->format('Y-m-d') . ' (' . $existingEntry->entry_type . ')';
                }
                
                $currentDate->addDay();
            }
            
            if (!empty($conflictDates)) {
                throw new \Exception("Cannot approve application. Employee already has entries for dates: " . implode(', ', $conflictDates));
            }
        }
    }
    
    private function createTimeEntryForApplication(Application $application)
    {
        switch ($application->req_type) {
            case 'casual_leave':
            case 'sick_leave':
            case 'regularization':
                $startDate = \Carbon\Carbon::parse($application->start_date);
                $endDate = $application->end_date ? \Carbon\Carbon::parse($application->end_date) : $startDate;
                
                $currentDate = $startDate->copy();
                $totalDays = 0;
                while ($currentDate <= $endDate) {
                    TimeEntry::create([
                        'employee_id' => $application->employee_id,
                        'entry_type' => $application->req_type,
                        'entry_time' => $currentDate->format('Y-m-d 09:00:00'),
                        'notes' => 'Auto-created from approved application #' . $application->id
                    ]);
                    $currentDate->addDay();
                    $totalDays++;
                }
                
                if (in_array($application->req_type, ['casual_leave', 'sick_leave'])) {
                    $leaveCount = \App\Models\LeaveCount::where('employee_id', $application->employee_id)->first();
                    if ($leaveCount) {
                        $currentLeave = $leaveCount->{$application->req_type};
                        $newLeaveCount = max(0, $currentLeave - $totalDays);
                        $leaveCount->update([$application->req_type => $newLeaveCount]);
                    }
                }
                break;
                
            case 'punch_Out_regularization':
                $entryTime = request('custom_time') ?? $application->end_date;
                TimeEntry::create([
                    'employee_id' => $application->employee_id,
                    'entry_type' => 'punch_out',
                    'entry_time' => $entryTime,
                    'notes' => 'Auto-created from approved application #' . $application->id
                ]);
                break;
        }
    }

    public function showApplication(Application $application)
    {
        $application->load('employee');
        return response()->json($application);
    }

    public function attendance(Request $request)
    {
        $adminEmpId = auth()->user()->emp_id;
        $date = $request->get('date', today()->format('Y-m-d'));
        $department = $request->get('department');
        $search = $request->get('search');
        $perPage = $request->get('per_page', 15);

        $query = Employee::where('role', 'employee')
            ->where('status', 'active')
            ->where('referrance', $adminEmpId)
            ->with(['department', 'timeEntries' => function($q) use ($date) {
                $q->whereDate('entry_time', $date)->orderBy('entry_time');
            }]);

        if ($department) {
            $query->where('department_id', $department);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                  ->orWhere('emp_id', 'like', '%' . $search . '%');
            });
        }

        $employees = $query->paginate($perPage);
        
        // Check for half-day applications and calculate working hours for each employee
        foreach ($employees as $employee) {
            $employee->halfDayApplication = \App\Models\Application::where('employee_id', $employee->emp_id)
                ->whereIn('req_type', ['half_day', 'half_leave'])
                ->where('status', 'approved')
                ->whereDate('start_date', $date)
                ->exists();
                
            // Calculate working hours and get first/last punch times
            $punchIns = $employee->timeEntries->where('entry_type', 'punch_in')->sortBy('entry_time');
            $punchOuts = $employee->timeEntries->where('entry_type', 'punch_out')->sortBy('entry_time');
            
            $employee->firstPunchIn = $punchIns->first();
            $employee->lastPunchOut = $punchOuts->last();
            
            // Calculate total working hours by pairing punch in/out sessions
            $totalMinutes = 0;
            $punchInArray = $punchIns->values()->toArray();
            $punchOutArray = $punchOuts->values()->toArray();
            
            for ($i = 0; $i < count($punchInArray); $i++) {
                if (isset($punchOutArray[$i])) {
                    $punchInTime = \Carbon\Carbon::parse($punchInArray[$i]['entry_time']);
                    $punchOutTime = \Carbon\Carbon::parse($punchOutArray[$i]['entry_time']);
                    $totalMinutes += $punchOutTime->diffInMinutes($punchInTime);
                }
            }
            
            $employee->workingHours = $totalMinutes > 0 ? sprintf('%d:%02d', floor($totalMinutes / 60), $totalMinutes % 60) : '0:00';
            
            // Determine status
            if ($employee->halfDayApplication) {
                $employee->attendanceStatus = 'half_day';
            } elseif ($employee->firstPunchIn) {
                $employee->attendanceStatus = 'present';
            } else {
                $employee->attendanceStatus = 'absent';
            }
        }
        
        $departments = Department::all();
        
        // Get weekend policy from system settings
        $weekendPolicySetting = SystemSetting::where('setting_key', 'weekend_policy')->first();
        $weekendPolicy = $weekendPolicySetting ? json_decode($weekendPolicySetting->setting_value, true) : [
            'recurring_days' => [0], // Default: Sunday only
            'specific_pattern' => []
        ];
        
        // Check schedule exceptions
        $scheduleException = \App\Models\ScheduleException::where('exception_date', $date)->first();
        
        // Determine if date is weekend/holiday
        $dateCarbon = \Carbon\Carbon::parse($date);
        $isWeekend = false;
        $weekendType = '';
        
        if ($scheduleException) {
            if ($scheduleException->type === 'holiday') {
                $isWeekend = true;
                $weekendType = 'Holiday';
            } elseif ($scheduleException->type === 'weekend') {
                $isWeekend = true;
                $weekendType = 'Weekend';
            }
        } else {
            // Check weekend policy
            $dayOfWeek = $dateCarbon->dayOfWeek; // 0 = Sunday, 6 = Saturday
            
            // Check recurring days
            if (in_array($dayOfWeek, $weekendPolicy['recurring_days'])) {
                $isWeekend = true;
                $weekendType = $dayOfWeek === 0 ? 'Sunday' : 'Saturday';
            }
            
            // Check specific patterns (e.g., 2nd/4th Saturday)
            if (isset($weekendPolicy['specific_pattern'][$dayOfWeek])) {
                $weekOfMonth = ceil($dateCarbon->day / 7);
                if (in_array($weekOfMonth, $weekendPolicy['specific_pattern'][$dayOfWeek])) {
                    $isWeekend = true;
                    $weekendType = $this->getOrdinal($weekOfMonth) . ' ' . $dateCarbon->format('l');
                }
            }
        }

        return view('admin.attendance.index', compact('employees', 'departments', 'date', 'isWeekend', 'weekendType'));
    }
    
    private function getOrdinal($number) {
        $ordinals = ['', 'First', 'Second', 'Third', 'Fourth', 'Fifth'];
        return $ordinals[$number] ?? $number . 'th';
    }

    public function attendanceFilter(Request $request)
    {
        $adminEmpId = auth()->user()->emp_id;
        $date = $request->get('date', today()->format('Y-m-d'));
        $department = $request->get('department');
        $search = $request->get('search');
        $entryType = $request->get('entry_type', 'all');

        $query = Employee::where('role', 'employee')
            ->where('status', 'active')
            ->where('referrance', $adminEmpId)
            ->with(['department', 'timeEntries' => function($q) use ($date) {
                $q->whereDate('entry_time', $date)->orderBy('entry_time');
            }]);

        // Add entry images relationship for camera filter
        if ($entryType === 'camera') {
            $query->with(['entryImages' => function($q) use ($date) {
                $q->whereDate('entry_time', $date);
            }]);
        }

        if ($department) {
            $query->where('department_id', $department);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                  ->orWhere('emp_id', 'like', '%' . $search . '%');
            });
        }

        $employees = $query->get();

        // Filter based on entry type
        if ($entryType === 'camera') {
            $employees = $employees->filter(function($employee) use ($date) {
                return $employee->entryImages->where('entry_time', '>=', $date . ' 00:00:00')
                                            ->where('entry_time', '<=', $date . ' 23:59:59')
                                            ->count() > 0;
            });
        }

        // Transform data for JSON response
        $employeesData = $employees->map(function($employee) {
            return [
                'emp_id' => $employee->emp_id,
                'username' => $employee->username,
                'department' => $employee->department,
                'time_entries' => $employee->timeEntries->toArray(),
                'entry_images' => $employee->entryImages ?? []
            ];
        });

        return response()->json([
            'employees' => $employeesData,
            'count' => $employees->count()
        ]);
    }

    public function timeEntries(Request $request)
    {
        $empId = $request->get('employee');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $query = \App\Models\TimeEntry::where('employee_id', $empId);
        
        if ($fromDate) {
            $query->whereDate('entry_time', '>=', $fromDate);
        }
        
        if ($toDate) {
            $query->whereDate('entry_time', '<=', $toDate);
        }
        
        $entries = $query->orderBy('entry_time')->get();
        
        // Get entry images matched by entry_id
        $images = \App\Models\EntryImage::whereIn('entry_id', $entries->pluck('id'))
            ->get()
            ->keyBy('entry_id');
        
        return response()->json([
            'entries' => $entries,
            'images' => $images
        ]);
    }

    public function entryImages(Request $request)
    {
        $empId = $request->get('employee');
        $date = $request->get('date');

        $images = \App\Models\EntryImage::where('emp_id', $empId)
            ->whereDate('entry_time', $date)
            ->orderBy('entry_time')
            ->get();

        return response()->json([
            'images' => $images
        ]);
    }

    public function reports(Request $request)
    {
        $adminEmpId = auth()->user()->emp_id;
        $departments = Department::all();
        $employees = Employee::where('role', 'employee')->where('referrance', $adminEmpId)->get();
        
        // Get salary reports for admin's allotted employees with filters
        $salaryQuery = \App\Models\SalaryReport::whereExists(function($query) use ($adminEmpId) {
            $query->select(\DB::raw(1))
                  ->from('employees')
                  ->whereRaw('BINARY salary_reports.emp_id = BINARY employees.emp_id')
                  ->where('employees.referrance', $adminEmpId)
                  ->where(function($q) {
                      $q->whereRaw('(
                          (employees.hire_date IS NULL OR DATE(employees.hire_date) <= LAST_DAY(CONCAT(salary_reports.year, "-", LPAD(salary_reports.month, 2, "0"), "-01")))
                          AND
                          (employees.end_date IS NULL OR DATE(employees.end_date) >= CONCAT(salary_reports.year, "-", LPAD(salary_reports.month, 2, "0"), "-01"))
                      )');
                  });
        });
        
        // Apply filters
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $salaryQuery->where(function($q) use ($search) {
                $q->where('emp_name', 'like', "%{$search}%")
                  ->orWhere('emp_id', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('month') && $request->month) {
            $salaryQuery->where('month', $request->month);
        }
        
        if ($request->has('year') && $request->year) {
            $salaryQuery->where('year', $request->year);
        }
        
        $perPage = $request->get('per_page', 10);
        $salaryReports = $salaryQuery->orderBy('created_at', 'desc')->paginate($perPage);
        
        // Handle POST request for attendance report generation
        if ($request->isMethod('post')) {
            return $this->generateAttendanceExcel($request);
        }
        
        return view('admin.reports.index', compact('departments', 'employees', 'salaryReports'));
    }


    
    private function generateAttendanceExcel(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2030'
        ]);
        
        $adminEmpId = auth()->user()->emp_id;
        $month = $request->month;
        $year = $request->year;
        
        // Get admin's allotted employees active during the requested month
        $requestedMonthStart = \Carbon\Carbon::create($year, $month, 1);
        $requestedMonthEnd = $requestedMonthStart->copy()->endOfMonth();
        
        $employees = Employee::where('role', 'employee')
            ->where('referrance', $adminEmpId)
            ->where(function($q) use ($requestedMonthStart, $requestedMonthEnd) {
                $q->where(function($subQ) use ($requestedMonthStart, $requestedMonthEnd) {
                    // Employee was hired before or during the month
                    $subQ->where(function($hireQ) use ($requestedMonthEnd) {
                        $hireQ->whereNull('hire_date')
                              ->orWhere('hire_date', '<=', $requestedMonthEnd->format('Y-m-d'));
                    })
                    // Employee end date is after or during the month (or null)
                    ->where(function($endQ) use ($requestedMonthStart) {
                        $endQ->whereNull('end_date')
                             ->orWhere('end_date', '>=', $requestedMonthStart->format('Y-m-d'));
                    });
                });
            })
            ->with(['department', 'region'])
            ->get();
        
        $salaryService = new \App\Services\SalaryCalculationService();
        $attendanceData = [];

        foreach ($employees as $employee) {
            // Double-check employee eligibility using service method
            if (!$salaryService->shouldIncludeEmployeeInReport($employee->emp_id, $month, $year)) {
                continue;
            }
            
            $attendance = $salaryService->calculatePayableDays($employee->emp_id, $month, $year, $adminEmpId);
            
            $attendanceData[] = [
                'Employee ID' => $employee->emp_id,
                'Employee Name' => $employee->username ?? $employee->name,
                'Department' => $employee->department->name ?? 'N/A',
                'Position' => $employee->position ?? 'N/A',
                'Total Working Days' => date('t', mktime(0, 0, 0, $month, 1, $year)),
                'Present Days' => $attendance['present_days'],
                'Absent Days' => $attendance['absent_days'],
                'Sick Leave' => $attendance['sick_leave'],
                'Casual Leave' => $attendance['casual_leave'],
                'Half Days' => $attendance['half_days'],
                'Holidays' => $attendance['holidays'],
                'WFH Days' => $attendance['wfh_days'] ?? 0,
                'Regularization' => $attendance['regularization'],
                'Short Attendance' => $attendance['short_attendance'],
                'Total Payable Days' => $attendance['present_days'] + $attendance['holidays'] + 
                                       $attendance['sick_leave'] + $attendance['casual_leave'] + 
                                       ($attendance['half_days'] * 0.5) + ($attendance['short_attendance'] * 0.5) + 
                                       $attendance['regularization'] + ($attendance['wfh_days'] ?? 0)
            ];
        }
        
        $filename = 'attendance_report_' . date('F_Y', mktime(0, 0, 0, $month, 1, $year)) . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($attendanceData, $month, $year) {
            $file = fopen('php://output', 'w');
            
            // Add header with generation date and requested month/year
            $monthName = date('F', mktime(0, 0, 0, $month, 1));
            $generatedDate = now()->format('j M, Y');
            fputcsv($file, ['Attendance Report for ' . $monthName . ' ' . $year]);
            fputcsv($file, ['Generated On: ' . $generatedDate]);
            fputcsv($file, []); // Empty row
            
            // CSV Headers
            if (!empty($attendanceData)) {
                fputcsv($file, array_keys($attendanceData[0]));
                
                // Data rows
                foreach ($attendanceData as $row) {
                    fputcsv($file, array_values($row));
                }
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function downloadSalaryReport($id)
    {
        $adminEmpId = auth()->user()->emp_id;
        
        // Find salary report for admin's allotted employee using admin_id
        $salaryReport = \App\Models\SalaryReport::where('id', $id)
            ->where('admin_id', $adminEmpId)
            ->first();
            
        if (!$salaryReport) {
            return redirect()->route('admin.reports')->with('error', 'Salary report not found or access denied.');
        }
        
        $employee = Employee::where('emp_id', $salaryReport->emp_id)->first();
        // Use the same approach as super-admin with Browsershot and original template
        $html = view('super-admin.reports.salary-report-pdf', compact('salaryReport', 'employee'))->render();
        
        $pdf = \Spatie\Browsershot\Browsershot::html($html)
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->pdf();

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="salary_slip_' . $salaryReport->emp_id . '_' . $salaryReport->month . '_' . $salaryReport->year . '.pdf"');
    }

    public function showSalarySlipPreview($id)
    {
        $adminEmpId = auth()->user()->emp_id;
        
        $salaryReport = \App\Models\SalaryReport::where('id', $id)
            ->where('admin_id', $adminEmpId)
            ->with(['employee', 'region'])
            ->first();
            
        if (!$salaryReport) {
            return redirect()->route('admin.reports')->with('error', 'Salary report not found or access denied.');
        }

        $employee = Employee::with('region')->where('emp_id', $salaryReport->emp_id)->first();
        
        // return $employee;
        return view('admin.reports.salary-slip-preview', compact('salaryReport', 'employee'));
    }

    public function editSalaryReport($id)
    {
        $adminEmpId = auth()->user()->emp_id;
        
        $salaryReport = \App\Models\SalaryReport::where('id', $id)
            ->where('admin_id', $adminEmpId)
            ->with(['employee', 'region'])
            ->first();
            
        if (!$salaryReport) {
            return redirect()->route('admin.reports')->with('error', 'Salary report not found or access denied.');
        }
        
        // Get detailed daily attendance data with admin priority
        $salaryService = new \App\Services\SalaryCalculationService();
        $dailyAttendance = $salaryService->getDailyAttendanceDetails($salaryReport->emp_id, $salaryReport->month, $salaryReport->year, $adminEmpId);
        
        // Calculate per day basic salary
        $perDayBasicSalary = $salaryReport->basic_salary / $salaryReport->total_working_days;
        
        return view('admin.reports.edit-salary-report', compact('salaryReport', 'dailyAttendance', 'perDayBasicSalary'));
    }

    public function updateSalaryReport(Request $request, $id)
    {
        $adminEmpId = auth()->user()->emp_id;
        
        $salaryReport = \App\Models\SalaryReport::where('id', $id)
            ->where('admin_id', $adminEmpId)
            ->first();
            
        if (!$salaryReport) {
            return redirect()->route('admin.reports')->with('error', 'Salary report not found or access denied.');
        }
        
        $request->validate([
            'emp_name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'total_working_days' => 'required|integer|min:1',
            'present_days' => 'required|integer|min:0',
            'absent_days' => 'required|integer|min:0',
            'half_days' => 'required|integer|min:0',
            'sick_leave' => 'required|integer|min:0',
            'casual_leave' => 'required|integer|min:0',
            'regularization' => 'required|integer|min:0',
            'holidays' => 'required|integer|min:0',
            'short_attendance' => 'required|integer|min:0',
            'payable_days' => 'required|numeric|min:0',
            'basic_salary' => 'required|numeric|min:0',
            'hra' => 'required|numeric|min:0',
            'conveyance_allowance' => 'required|numeric|min:0',
            'special_allowance' => 'nullable|numeric|min:0',
            'pf' => 'required|numeric|min:0',
            'pt' => 'required|numeric|min:0',
            'tds' => 'nullable|numeric|min:0',
            'healthcare_cess' => 'nullable|numeric|min:0',
            'payment_mode' => 'required|in:cash,bank_transfer',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:11',
            'bank_branch' => 'nullable|string|max:255',
            'uan' => 'nullable|string|max:12',
            'pf_no' => 'nullable|string|max:255',
            'esic_no' => 'nullable|string|max:17'
        ]);

        if($request->payable_days + $request->absent_days > $request->total_working_days ){
            return back()->withErrors(['error' => 'Days can\'t exceed total month days!'])
                            ->withInput();
        }

        // Get original salary data for validation
        $employee = \App\Models\Employee::with('salary')->where('emp_id', $salaryReport->emp_id)->first();
        $originalSalary = $employee->salary;
        
        // Validate salary components don't exceed original amounts
        if ($originalSalary) {
            if ($request->basic_salary > $originalSalary->basic_salary) {
                return back()->withErrors(['basic_salary' => 'Basic salary cannot exceed original amount of ₹' . number_format($originalSalary->basic_salary, 2)])
                            ->withInput();
            }
            if ($request->hra > $originalSalary->hra) {
                return back()->withErrors(['hra' => 'HRA cannot exceed original amount of ₹' . number_format($originalSalary->hra, 2)])
                            ->withInput();
            }
            if ($request->conveyance_allowance > $originalSalary->conveyance_allowance) {
                return back()->withErrors(['conveyance_allowance' => 'Conveyance allowance cannot exceed original amount of ₹' . number_format($originalSalary->conveyance_allowance, 2)])
                            ->withInput();
            }
            if ($request->pf > $originalSalary->pf) {
                return back()->withErrors(['pf' => 'PF cannot exceed original amount of ₹' . number_format($originalSalary->pf, 2)])
                            ->withInput();
            }
            if ($request->pt > $originalSalary->pt) {
                return back()->withErrors(['pt' => 'PT cannot exceed original amount of ₹' . number_format($originalSalary->pt, 2)])
                            ->withInput();
            }
        }
        
        $payableBasicSalary = ($request->basic_salary / $request->total_working_days) * $request->payable_days;
        $grossSalary = $payableBasicSalary + $request->hra + $request->conveyance_allowance + ($request->special_allowance ?? 0);
        $totalDeductions = $request->pf + $request->pt + ($request->tds ?? 0) + ($request->healthcare_cess ?? 0);
        $netSalary = $grossSalary - $totalDeductions;

        $salaryReport->update([
            'emp_name' => $request->emp_name,
            'designation' => $request->designation,
            'department' => $request->department,
            'total_working_days' => $request->total_working_days,
            'present_days' => $request->present_days,
            'absent_days' => $request->absent_days,
            'half_days' => $request->half_days,
            'sick_leave' => $request->sick_leave,
            'casual_leave' => $request->casual_leave,
            'regularization' => $request->regularization,
            'holidays' => $request->holidays,
            'short_attendance' => $request->short_attendance,
            'payable_days' => $request->payable_days,
            'basic_salary' => $request->basic_salary,
            'hra' => $request->hra,
            'conveyance_allowance' => $request->conveyance_allowance,
            'special_allowance' => $request->special_allowance ?? 0,
            'pf' => $request->pf,
            'pt' => $request->pt,
            'tds' => $request->tds ?? 0,
            'healthcare_cess' => $request->healthcare_cess ?? 0,
            'payable_basic_salary' => $payableBasicSalary,
            'gross_salary' => $grossSalary,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'has_negative_salary' => $netSalary < 0,
            'has_missing_data' => !$request->basic_salary || !$request->department,
            'needs_review' => $netSalary < 0 || !$request->basic_salary || !$request->department || $request->payable_days < 10,
            'status' => 'reviewed',
            'payment_mode' => $request->payment_mode,
            'bank_name' => $request->bank_name,
            'bank_account' => $request->bank_account,
            'ifsc_code' => $request->ifsc_code,
            'bank_branch' => $request->bank_branch,
            'uan' => $request->uan,
            'pf_no' => $request->pf_no,
            'esic_no' => $request->esic_no
        ]);

        return redirect()->route('admin.reports')
            ->with('success', 'Salary report updated successfully');
    }

    private function getPresentToday()
    {
        $adminEmpId = auth()->user()->emp_id;
        
        return TimeEntry::whereDate('entry_time', today())
            ->where('entry_type', 'punch_in')
            ->whereHas('employee', function($q) use ($adminEmpId) {
                $q->where('referrance', $adminEmpId);
            })
            ->distinct('employee_id')
            ->count();
    }

    private function getTodayAttendanceStats()
    {
        $adminEmpId = auth()->user()->emp_id;
        
        $totalEmployees = Employee::where('role', 'employee')
        ->where('status', 'active')
        ->where('referrance', $adminEmpId)->count();
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

    // timeEntries and deleteTimeEntry methods moved to SuperAdminController

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
        $adminEmpId = auth()->user()->emp_id;
        
        $query = Employee::where('role', 'employee')
            ->where('referrance', $adminEmpId);
        
        // Handle status filter
        $status = $request->get('status', 'active');
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        // Handle department filter
        if ($request->department && $request->department !== 'all') {
            $query->where('department', $request->department);
        }
        
        // Handle search filter
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('username', 'like', "%{$request->search}%")
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
        
        // Check for half-day applications for each employee
        foreach ($employees as $employee) {
            $employee->halfDayApplications = \App\Models\Application::where('employee_id', $employee->emp_id)
                ->whereIn('req_type', ['half_day', 'half_leave'])
                ->where('status', 'approved')
                ->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->pluck('start_date')
                ->map(function($date) {
                    return Carbon::parse($date)->format('Y-m-d');
                })
                ->toArray();
        }
        
        $departments = Department::all();
        
        return view('admin.employee-history.index', compact('employees', 'departments'));
    }

    // deleteTimeEntry method moved to SuperAdminController

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

    public function schedule()
    {
        $currentMonth = request('month', Carbon::now()->month);
        $currentYear = request('year', Carbon::now()->year);
        $adminEmpId = auth()->user()->emp_id;
        
        $scheduleExceptions = \App\Models\ScheduleException::whereMonth('exception_date', $currentMonth)
            ->whereYear('exception_date', $currentYear)
            ->where(function($query) use ($adminEmpId) {
                $query->whereNotNull('superadmin_id')
                      ->orWhere('admin_id', $adminEmpId);
            })
            ->orderBy('admin_id', 'desc') // Prioritize admin exceptions over super admin
            ->get()
            ->groupBy('exception_date')
            ->map(function($exceptions) {
                // If admin exception exists, show only that; otherwise show super admin exception
                return $exceptions->where('admin_id', '!=', null)->first() ?? $exceptions->first();
            });
        
        $calendar = $this->generateCalendar($currentYear, $currentMonth, $scheduleExceptions);
        
        // return $calendar;
        return view('admin.schedule.index', compact('calendar', 'currentMonth', 'currentYear', 'scheduleExceptions'));
    }
    
    public function storeScheduleException(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:holiday,working_day,wfh',
            'description' => 'nullable|string|max:255'
        ]);
        
        try {
            $adminEmpId = auth()->user()->emp_id;
            
            // Check if there's a super admin exception for this date
            $existingSuperAdminException = \App\Models\ScheduleException::where('exception_date', $request->date)
                ->whereNotNull('superadmin_id')
                ->first();
            
            if ($existingSuperAdminException) {
                // If super admin exception exists, create a new admin exception (don't update)
                \App\Models\ScheduleException::create([
                    'exception_date' => $request->date,
                    'type' => $request->type,
                    'description' => $request->description,
                    'admin_id' => $adminEmpId
                ]);
            } else {
                // Normal behavior: update or create admin exception
                \App\Models\ScheduleException::updateOrCreate(
                    [
                        'exception_date' => $request->date,
                        'admin_id' => $adminEmpId
                    ],
                    [
                        'type' => $request->type,
                        'description' => $request->description
                    ]
                );
            }
            
            return response()->json(['success' => true, 'message' => 'Schedule exception saved successfully']);
        } catch (\Exception $e) {
            \Log::error('Schedule Exception Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    public function deleteScheduleException(Request $request)
    {
        \App\Models\ScheduleException::where('id', $request->id)->delete();
        return response()->json(['success' => true]);
    }
    
    private function generateCalendar($year, $month, )
    {
        $firstDay = Carbon::create($year, $month, 1);
        $lastDay = $firstDay->copy()->endOfMonth();
        
        // Start from Monday (1) and end on Sunday (0)
        $startOfWeek = $firstDay->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $lastDay->copy()->endOfWeek(Carbon::SUNDAY);

        $adminEmpId = auth()->user()->emp_id;
        
        $calendar = [];
        $current = $startOfWeek->copy();
        
        while ($current <= $endOfWeek) {
            // $dateStr = $current->format('d');
            // $monthStr = $current->format('m');
            // $yearStr = $current->format('Y');

            

            $exception = \App\Models\ScheduleException::where('exception_date', $current)
            // ->whereMonth('exception_date', $monthStr)
            // ->whereYear('exception_date', $yearStr)
            ->where(function($query) use ($adminEmpId) {
                $query->whereNotNull('superadmin_id')
                      ->orWhere('admin_id', $adminEmpId);
            })
            ->get();
            // $exception = $scheduleExceptions->firstWhere('exception_date', $dateStr);
            
            $calendar[] = [
                'date' => $current->copy(),
                'is_current_month' => $current->month == $month,
                'exception' => (count($exception) <= 0)?false:$exception
            ];
            
            $current->addDay();
        }
        
        return array_chunk($calendar, 7);
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
    
    public function updateTimeEntry(Request $request)
    {
        $request->validate([
            'entry_id' => 'required|exists:time_entries,id',
            'new_time' => 'required|date_format:Y-m-d H:i:s'
        ]);
        
        $timeEntry = \App\Models\TimeEntry::findOrFail($request->entry_id);
        
        // Check if admin has permission to edit this employee's data
        $adminEmpId = auth()->user()->emp_id;
        $employee = \App\Models\Employee::where('emp_id', $timeEntry->employee_id)->first();
        
        if (!$employee || $employee->referrance !== $adminEmpId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }
        
        $timeEntry->update([
            'entry_time' => $request->new_time,
            'notes' => ($timeEntry->notes ? $timeEntry->notes . ' | ' : '') . 'Edited by admin on ' . now()->format('Y-m-d H:i:s')
        ]);
        
        return response()->json(['success' => true, 'message' => 'Time entry updated successfully']);
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
    
    public function profile()
    {
        $admin = auth()->user();
        return view('admin.profile.index', compact('admin'));
    }
    
    public function updateProfile(Request $request)
    {
        $admin = auth()->user();
        
        $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:employees,username,' . $admin->id,
            'email' => 'required|email|unique:employees,email,' . $admin->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'password' => 'nullable|min:6|confirmed',
        ]);
        
        $updateData = $request->only(['full_name', 'username', 'email', 'phone', 'address']);
        
        if ($request->password) {
            $updateData['password'] = Hash::make($request->password);
        }
        
        $admin->update($updateData);
        
        return redirect()->route('admin.profile')->with('success', 'Profile updated successfully!');
    }

    // Salary Management
    public function salaries()
    {
        $adminEmpId = auth()->user()->emp_id;
        
        $salaries = Salary::whereExists(function($query) use ($adminEmpId) {
            $query->select(DB::raw(1))
                  ->from('employees')
                  ->whereRaw('BINARY salaries.emp_id = BINARY employees.emp_id')
                  ->where('employees.referrance', $adminEmpId);
        })->with('employee')->paginate(15);
        
        return view('admin.salaries.index', compact('salaries'));
    }

    public function createSalary()
    {
        return view('admin.salaries.create');
    }

    public function getPendingEmployees()
    {
        $adminEmpId = auth()->user()->emp_id;
        $perPage = request('per_page', 10);
        $search = request('search');
        
        $employeesWithSalary = DB::table('salaries')->pluck('emp_id')->toArray();
        $employees = Employee::where('role', 'employee')
            ->where('referrance', $adminEmpId)
            ->whereNotIn('emp_id', $employeesWithSalary)
            ->when($search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('emp_id', 'like', '%' . $search . '%')
                      ->orWhere('username', 'like', '%' . $search . '%');
                });
            })
            ->with(['department'])
            ->paginate($perPage);
        
        if (request()->ajax()) {
            return response()->json([
                'employees' => $employees->items(),
                'pagination' => [
                    'current_page' => $employees->currentPage(),
                    'last_page' => $employees->lastPage(),
                    'per_page' => $employees->perPage(),
                    'total' => $employees->total()
                ]
            ]);
        }
        
        return $employees;
    }

    public function storeSalary(Request $request)
    {
        $adminEmpId = auth()->user()->emp_id;
        
        $request->validate([
            'emp_id' => 'required|exists:employees,emp_id',
            'basic_salary' => 'required|numeric|min:0',
            'hra' => 'required|numeric|min:0',
            'conveyance_allowance' => 'required|numeric|min:0',
            'special_allowance' => 'required|numeric|min:0',
            'tds' => 'required|numeric|min:0',
            'healthcare_cess' => 'required|numeric|min:0',
            'pf' => 'required|numeric|min:0',
            'pt' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
            'payment_mode' => 'required|in:cash,bank_transfer',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:11',
            'bank_branch' => 'nullable|string|max:255',
            'uan' => 'nullable|string|max:12',
            'pf_no' => 'nullable|string|max:255',
            'esic_no' => 'nullable|string|max:17'
        ]);

        // Verify employee belongs to this admin
        $employee = Employee::where('emp_id', $request->emp_id)
            ->where('referrance', $adminEmpId)
            ->first();
            
        if (!$employee) {
            return back()->withErrors(['emp_id' => 'Employee not found or not assigned to you.']);
        }

        // Calculate gross salary
        $grossSalary = $request->basic_salary + $request->hra + $request->conveyance_allowance + $request->special_allowance;
        
        // Determine if PF is auto-calculated
        $isPf = $request->has('auto_pf') ? 1 : 0;

        Salary::create(array_merge($request->all(), [
            'gross_salary' => $grossSalary,
            'is_pf' => $isPf
        ]));

        return redirect()->route('admin.salaries')->with('success', 'Salary created successfully');
    }

    public function editSalary(Salary $salary)
    {
        $adminEmpId = auth()->user()->emp_id;
        
        // Verify salary belongs to admin's employee
        if ($salary->employee->referrance !== $adminEmpId) {
            return redirect()->route('admin.salaries')
                ->with('error', 'You do not have permission to edit this salary.');
        }
        
        return view('admin.salaries.edit', compact('salary'));
    }

    public function updateSalary(Request $request, Salary $salary)
    {
        $adminEmpId = auth()->user()->emp_id;
        
        // Verify salary belongs to admin's employee
        if ($salary->employee->referrance !== $adminEmpId) {
            return redirect()->route('admin.salaries')
                ->with('error', 'You do not have permission to edit this salary.');
        }
        
        $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'hra' => 'required|numeric|min:0',
            'conveyance_allowance' => 'required|numeric|min:0',
            'special_allowance' => 'required|numeric|min:0',
            'tds' => 'required|numeric|min:0',
            'healthcare_cess' => 'required|numeric|min:0',
            'pf' => 'required|numeric|min:0',
            'pt' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
            'payment_mode' => 'required|in:cash,bank_transfer',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:11',
            'bank_branch' => 'nullable|string|max:255',
            'uan' => 'nullable|string|max:12',
            'pf_no' => 'nullable|string|max:255',
            'esic_no' => 'nullable|string|max:17'
        ]);

        // Calculate gross salary
        $grossSalary = $request->basic_salary + $request->hra + $request->conveyance_allowance + $request->special_allowance;
        
        // Determine if PF is auto-calculated
        $isPf = $request->has('auto_pf') ? 1 : 0;

        $salary->update(array_merge($request->only([
            'basic_salary', 'hra', 'conveyance_allowance', 'special_allowance', 'tds', 'healthcare_cess',
            'pf', 'pt', 'effective_from', 'payment_mode', 'bank_name', 'bank_account', 'ifsc_code', 
            'bank_branch', 'uan', 'pf_no', 'esic_no'
        ]), [
            'gross_salary' => $grossSalary,
            'is_pf' => $isPf
        ]));

        return redirect()->route('admin.salaries')->with('success', 'Salary updated successfully');
    }

    public function calculateSalaryComponents(Request $request)
    {
        $request->validate([
            'emp_id' => 'required|exists:employees,emp_id',
            'monthly_ctc' => 'required|numeric|min:0',
            'include_pf' => 'nullable|boolean'
        ]);

        $employee = Employee::where('emp_id', $request->emp_id)->first();
        $monthlyCTC = $request->monthly_ctc;
        
        // Get system settings
        $settings = SystemSetting::pluck('setting_value', 'setting_key');
        
        // Determine if employee is in metro city and senior/junior level
        $isMetro = $employee->metro_city ?? false;
        $isSenior = $employee->senior_junior === 'senior';
        
        // Calculate basic salary percentage based on employee profile
        $basicPercentage = $isMetro ? 
            ($isSenior ? (float)($settings['metro_basic'] ?? 50) : (float)($settings['default_basic'] ?? 50)) :
            (float)($settings['default_basic'] ?? 50);
        
        $basicSalary = ($monthlyCTC * $basicPercentage) / 100;
        
        // Calculate HRA percentage
        $hraPercentage = $isMetro ? 
            (float)($settings['metro_hra'] ?? 50) : 
            (float)($settings['default_hra'] ?? 40);
        $hra = ($basicSalary * $hraPercentage) / 100;
        
        // Calculate conveyance allowance
        $conveyanceAllowance = $isSenior ? 
            (float)($settings['senior_ca'] ?? 1600) : 
            (float)($settings['junior_ca'] ?? 1200);
        
        // Calculate special allowance (remaining amount)
        $specialAllowance = $monthlyCTC - $basicSalary - $hra - $conveyanceAllowance;
        
        // Calculate TDS
        $annualCTC = $monthlyCTC * 12;
        $standardDeduction = (float)($settings['standard_deduction'] ?? 75000);
        $refinedIncome = $annualCTC - $standardDeduction;
        
        $taxRate = 0;
        if ($refinedIncome > 0) {
            $taxSlab = \App\Models\Tax::where(function($query) use ($refinedIncome) {
                $query->where(function($q) use ($refinedIncome) {
                    $q->whereNotNull('income_to')
                      ->where('income_from', '<=', $refinedIncome)
                      ->where('income_to', '>=', $refinedIncome);
                })->orWhere(function($q) use ($refinedIncome) {
                    $q->whereNull('income_to')
                      ->where('income_from', '<=', $refinedIncome);
                });
            })->orderBy('income_from', 'desc')->first();
            
            if ($taxSlab) {
                $taxRate = $taxSlab->tax_rate;
            }
        }
        
        $annualTax = $refinedIncome * ($taxRate / 100);
        $monthlyTDS = $annualTax / 12;
        
        // Calculate Healthcare Cess
        $cessPercentage = 4;
        if (isset($settings['health_&_education_cess'])) {
            $cessValue = str_replace('%', '', $settings['health_&_education_cess']);
            $cessPercentage = (float)$cessValue;
        }
        $healthcareCess = $monthlyTDS * ($cessPercentage / 100);
        
        // Calculate PF (12% of basic salary)

        if (!$request->has('include_pf') || !$request->include_pf) {
            $pf = 0;
        } else {
            $pf = $basicSalary * 0.12;
        }

        // Get PT from settings
        $pt = (float)($settings['pt'] ?? 200);
        
        return response()->json([
            'success' => true,
            'components' => [
                'basic_salary' => round($basicSalary, 2),
                'hra' => round($hra, 2),
                'conveyance_allowance' => round($conveyanceAllowance, 2),
                'special_allowance' => round($specialAllowance, 2),
                'pf' => round($pf, 2),
                'pt' => round($pt, 2),
                'tds' => round($monthlyTDS, 2),
                'healthcare_cess' => round($healthcareCess, 2)
            ]
        ]);
    }
}