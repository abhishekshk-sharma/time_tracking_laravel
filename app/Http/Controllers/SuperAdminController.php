<?php

namespace App\Http\Controllers;

use App\Models\SuperAdmin;
use App\Models\Employee;
use App\Models\Salary;
use App\Models\SalaryReport;
use App\Models\Department;
use App\Models\Application;
use App\Models\AppNotification;
use App\Models\TimeEntry;
use App\Models\Region;
use App\Models\SystemSetting;
use App\Models\ScheduleException;
use App\Services\SalaryCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Spatie\Browsershot\Browsershot;
use Illuminate\Validation\Rule; 

class SuperAdminController extends Controller
{
    public function showLogin()
    {
        return view('super-admin.auth.login');
    }

    public function showRegister()
    {
        return view('super-admin.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:super_admins,username',
            'email' => 'required|email|unique:super_admins,email',
            'password' => 'required|min:6|confirmed',
        ]);

        SuperAdmin::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('super-admin.login')->with('success', 'Super admin account created successfully!');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::guard('super_admin')->attempt($credentials)) {
            return redirect()->route('super-admin.dashboard');
        }

        return back()->withErrors(['username' => 'Invalid credentials']);
    }

    public function logout()
    {
        Auth::guard('super_admin')->logout();
        return redirect()->route('super-admin.login');
    }

    public function dashboard()
    {
        $stats = [
            'total_employees' => Employee::count(),
            'active_employees' => Employee::where('status', 'active')->count(),
            'total_departments' => Department::count(),
            'pending_applications' => Application::where('status', 'pending')->count(),
        ];

        // Get recent applications (last 5)
        $recentApplications = Application::with('employee')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Calculate today's attendance
        $totalActiveEmployees = Employee::where('role', 'employee')
            ->where('status', 'active')
            ->count();
            
        $presentToday = Employee::where('role', 'employee')
            ->where('status', 'active')
            ->whereHas('timeEntries', function($query) {
                $query->where('entry_type', 'punch_in')
                      ->whereDate('entry_time', today());
            })
            ->count();
            
        $absentToday = $totalActiveEmployees - $presentToday;
        $attendancePercentage = $totalActiveEmployees > 0 ? round(($presentToday / $totalActiveEmployees) * 100) : 0;
        
        $todayAttendance = [
            'present' => $presentToday,
            'absent' => $absentToday,
            'percentage' => $attendancePercentage
        ];

        return view('super-admin.dashboard', compact('stats', 'recentApplications', 'todayAttendance'));
    }

    public function schedule()
    {
        $currentMonth = request('month', Carbon::now()->month);
        $currentYear = request('year', Carbon::now()->year);
        
        $scheduleExceptions = \App\Models\ScheduleException::whereMonth('exception_date', $currentMonth)
        ->whereMonth('exception_date', $currentMonth)
            ->whereYear('exception_date', $currentYear)
            ->get();

            
            $calendar = $this->generateCalendar($currentYear, $currentMonth, $scheduleExceptions);
            
            // return $calendar;

        return view('super-admin.schedule.index', compact('calendar', 'currentMonth', 'currentYear', 'scheduleExceptions'));
    }
    
    public function storeScheduleException(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:holiday,working_day,weekend',
            'description' => 'nullable|string|max:255'
        ]);
        
        try {
            ScheduleException::updateOrCreate(
                ['exception_date' => $request->date],
                [
                    'type' => $request->type,
                    'description' => $request->description,
                    'superadmin_id' => Auth::guard('super_admin')->id()
                ]
            );
            
            return response()->json(['success' => true, 'message' => 'Schedule exception saved successfully']);
        } catch (\Exception $e) {
            \Log::error('Schedule Exception Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    public function deleteScheduleException(Request $request)
    {
        try {
            ScheduleException::where('exception_date', $request->date)->delete();
            return response()->json(['success' => true, 'message' => 'Schedule exception deleted successfully']);
        } catch (\Exception $e) {
            \Log::error('Delete Schedule Exception Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    private function generateCalendar($year, $month, $scheduleExceptions)
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

    // Departments Management
    public function departments()
    {
        $departments = Department::withCount('employees')->get();
        return view('super-admin.departments.index', compact('departments'));
    }

    public function storeDepartment(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:departments,name',
                'description' => 'nullable|string|max:500'
            ]);

            $department = Department::create([
                'name' => $request->name,
                'description' => $request->description
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Department created successfully',
                'department' => $department
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create department: ' . $e->getMessage()
            ], 500);
        }
    }

    public function editDepartment(Department $department)
    {
        try {
            return response()->json([
                'success' => true,
                'department' => $department
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load department: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateDepartment(Request $request, Department $department)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
                'description' => 'nullable|string|max:500'
            ]);

            $department->update([
                'name' => $request->name,
                'description' => $request->description
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Department updated successfully',
                'department' => $department->fresh()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update department: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteDepartment(Department $department)
    {
        try {
            // Check if department has employees
            $employeeCount = $department->employees()->count();
            
            if ($employeeCount > 0) {
                return response()->json([
                    'success' => false,
                    'error' => "Cannot delete department. It has {$employeeCount} employee(s) assigned to it."
                ], 400);
            }
            
            $department->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Department deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete department: ' . $e->getMessage()
            ], 500);
        }
    }

    // Regions Management
    public function regions()
    {
        $regions = Region::withCount('employees')->get();
        return view('super-admin.regions.index', compact('regions'));
    }
    
    public function storeRegion(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'pin_code' => 'required|string|max:10',
            'ip_address' => 'nullable|ip',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180'
        ]);
        
        Region::create($request->all());
        
        return redirect()->route('super-admin.regions')->with('success', 'Region created successfully');
    }
    
    public function updateRegion(Request $request, Region $region)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'pin_code' => 'required|string|max:10',
            'ip_address' => 'nullable|ip',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180'
        ]);
        
        $region->update($request->all());
        
        return redirect()->route('super-admin.regions')->with('success', 'Region updated successfully');
    }

    public function createEmployee()
    {
        $departments = Department::all();
        $admins = Employee::where('role', 'admin')->get();
        return view('super-admin.employees.create', compact('departments', 'admins'));
    }

    public function storeEmployee(Request $request)
    {
        $request->validate([
            'emp_id' => 'required|unique:employees,emp_id',
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'position' => 'required|string|max:255',
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
            'referrance' => 'nullable|exists:employees,emp_id'
        ]);
        
        Employee::create([
            'emp_id' => $request->emp_id,
            'full_name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'position' => $request->position,
            'hire_date' => $request->hire_date,
            'dob' => $request->dob,
            'password_hash' => Hash::make($request->password),
            'department_id' => $request->department_id,
            'phone' => $request->phone,
            'address' => $request->address,
            'referrance' => $request->referrance,
            'role' => 'employee',
            'status' => 'active',
        ]);

        return redirect()->route('super-admin.employees')->with('success', 'Employee created successfully');
    }

    public function employees()
    {
        $perPage = request('per_page', 10);
        $search = request('search');
        $adminFilter = request('admin_filter');
        $status = request('status');
        
        $employees = Employee::where('role', 'employee')
            // ->where(function($q){
            //     $admin = Employee::where('role', 'admin')->pluck('id')->toArray();
            // })
            ->when($search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('emp_id', 'like', '%' . $search . '%')
                      ->orWhere('username', 'like', '%' . $search . '%');
                });
            })
            ->when($adminFilter, function($query, $adminFilter) {
                $query->where('referrance', $adminFilter);
            })
            ->when($status, function($query, $status) {
                $query->where('status', $status);
            })
            ->with(['department', 'region'])
            ->paginate($perPage);

            // return $employees;
            
        $admins = Employee::where('role', 'admin')->get();
        
        return view('super-admin.employees.index', compact('employees', 'admins'));
    }

    public function showEmployee($id)
    {
        $employee = Employee::with(['salary', 'department', 'region'])->findOrFail($id);
        return view('super-admin.employees.show', compact('employee'));
    }

    public function editEmployee($id)
    {
        $employee = Employee::with(['salary', 'department', 'region'])->findOrFail($id);
        $admins = Employee::with('department')
        ->where('role', 'admin')
        
        ->get();
        $departments = Department::all();
        $regions = Region::all();
        $leaveCount = $employee->leaveCount;

        // return ($admins);
        return view('super-admin.employees.edit', compact('employee', 'departments', 'regions', 'admins', 'leaveCount'));
    }

    public function updateEmployee(Request $request, $id)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'hire_date' => [ 
                'required', 
                'date', 
                'beforeOrEqual:' . Carbon::now()->addDays(30)->toDateString(), 
            ],
            'dob' => 
            [ 
                'nullable', 
                'date', 
                'beforeOrEqual:' . Carbon::now()->subYears(18)->toDateString(), 
            ],
            'end_date' => 
            [ 
                'nullable', 
                'date', 
                'beforeOrEqual:' . Carbon::now()->addDays(30)->toDateString(),
                'after:hire_date', 
            ],
            'email' => 'required|email|unique:employees,email,' . $id,
            'role' => 'required|in:employee,admin',
            'department_id' => 'nullable|exists:departments,id',
            'region_id' => 'nullable|exists:regions,id',
            'position' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'address' => 'nullable|string',
            'referrance' => 'nullable|string|max:255',
            'password' => 'nullable|min:6',
            'casual_leave' => 'nullable|integer|min:0|max:365',
            'sick_leave' => 'nullable|integer|min:0|max:365',
        ]);

        $employee = Employee::findOrFail($id);
        
        $updateData = $request->except(['password', 'casual_leave', 'sick_leave']);
        
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
            \App\Models\LeaveCount::updateOrCreate(
                ['employee_id' => $employee->emp_id],
                [
                    'casual_leave' => $request->casual_leave ?? 0,
                    'sick_leave' => $request->sick_leave ?? 0,
                ]
            );
        }
        
        return redirect()->route('super-admin.employees.edit', $id)
            ->with('success', 'Employee updated successfully');
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

        return view('super-admin.applications.index', compact('applications'));
    }

    public function showApplication(Application $application)
    {
        $application->load('employee');
        return response()->json($application);
    }

    public function updateApplicationStatus(Request $request, Application $application)
    {
        try {
            $request->validate([
                'status' => 'required|in:approved,rejected',
            ]);
            
            // Validate before updating status for approved applications
            if ($request->status === 'approved') {
                $this->validateApplicationForApproval($application);
            }

            $application->update([
                'status' => $request->status,
                'action_by' => auth('super_admin')->user()->username,
            ]);
            
            // Create time entries for approved applications
            if ($request->status === 'approved') {
                $this->createTimeEntryForApplication($application);
            }

            // Create notification for employee
            AppNotification::create([
                'App_id' => $application->id,
                'created_by' => auth('super_admin')->user()->username,
                'notify_to' => $application->employee_id
            ]);

            return response()->json(['success' => true, 'message' => 'Application ' . $request->status . ' successfully']);
        } catch (\Exception $e) {
            \Log::error('Super admin application status update error: ' . $e->getMessage());
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

    public function attendance(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);
        
        $query = Employee::where('role', 'employee')
            ->where('status', 'active')
            ->when($search, function($q) use ($search) {
                $q->where(function($query) use ($search) {
                    $query->where('username', 'like', '%' . $search . '%')
                          ->orWhere('emp_id', 'like', '%' . $search . '%');
                });
            })
            ->with(['timeEntries' => function($query) use ($date) {
                $query->whereDate('entry_time', $date);
            }, 'department', 'entryImages' => function($query) use ($date) {
                $query->whereDate('entry_time', $date);
            }]);
            
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
            $punchInArray = $punchIns->values();
            $punchOutArray = $punchOuts->values();
            
            // Pair each punch-in with the next available punch-out
            for ($i = 0; $i < $punchInArray->count(); $i++) {
                if ($i < $punchOutArray->count()) {
                    $punchInTime = \Carbon\Carbon::parse($punchInArray[$i]->entry_time);
                    $punchOutTime = \Carbon\Carbon::parse($punchOutArray[$i]->entry_time);
                    $totalMinutes += $punchInTime->diffInMinutes($punchOutTime);
                }
            }

            // return ($totalMinutes/60);
            
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

        return view('super-admin.attendance.index', compact('employees', 'date'));
    }

    public function reports()
    {
        $departments = Department::all();
        $employees = Employee::where('status', 'active')->get();
        
        $perPage = request('per_page', 10);
        $search = request('search');
        $month = request('month');
        $year = request('year');
        
        $salaryQuery = SalaryReport::with(['employee', 'region']);
        
        // Filter salary reports based on employee hire_date and end_date
        if ($month && $year) {
            $salaryQuery->whereExists(function($query) use ($month, $year) {
                $query->select(\DB::raw(1))
                      ->from('employees')
                      ->whereRaw('BINARY salary_reports.emp_id = BINARY employees.emp_id')
                      ->where(function($q) use ($month, $year) {
                          $q->whereRaw('(
                              (employees.hire_date IS NULL OR DATE(employees.hire_date) <= LAST_DAY(CONCAT(?, "-", LPAD(?, 2, "0"), "-01")))
                              AND
                              (employees.end_date IS NULL OR DATE(employees.end_date) >= CONCAT(?, "-", LPAD(?, 2, "0"), "-01"))
                          )', [$year, $month, $year, $month]);
                      });
            });
        }
        
        $salaryReports = $salaryQuery->when($search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('emp_id', 'like', '%' . $search . '%')
                      ->orWhere('emp_name', 'like', '%' . $search . '%');
                });
            })
            ->when($month, function($query, $month) {
                $query->where('month', $month);
            })
            ->when($year, function($query, $year) {
                $query->where('year', $year);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        return view('super-admin.reports.index', compact('departments', 'employees', 'salaryReports'));
    }

    public function generateSalaryReports(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020|max:2030'
        ]);

        $month = $request->month;
        $year = $request->year;
        $reportDate = Carbon::now();

        // Clear existing reports for this month/year
        SalaryReport::where('month', $month)->where('year', $year)->delete();

        // Get employees active during the requested month
        $requestedMonthStart = \Carbon\Carbon::create($year, $month, 1);
        $requestedMonthEnd = $requestedMonthStart->copy()->endOfMonth();
        
        $employees = Employee::where('role', 'employee')
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
            ->with(['salary', 'department', 'region'])
            ->get();

        $salaryService = new SalaryCalculationService();
        $generatedCount = 0;

        foreach ($employees as $employee) {
            // Double-check employee eligibility using service method
            if (!$salaryService->shouldIncludeEmployeeInReport($employee->emp_id, $month, $year)) {
                continue;
            }
            
            $attendanceData = $salaryService->calculatePayableDays($employee->emp_id, $month, $year, $employee->referrance);
            $salary = $employee->salary;

            if (!$salary) continue;

            // Calculate total days in month
            $totalMonthDays = date('t', mktime(0, 0, 0, $month, 1, $year));
            
            // Calculate payable days: present + holidays + sick/casual leave + (half_days * 0.5) + (short_attendance * 0.5)
            $payableDays = $attendanceData['present_days'] + $attendanceData['holidays'] + 
                          $attendanceData['sick_leave'] + $attendanceData['casual_leave'] + 
                          ($attendanceData['half_days'] * 0.5) + ($attendanceData['short_attendance'] * 0.5)
                          + $attendanceData['regularization'];
            
            $payableBasicSalary = ($salary->basic_salary / $totalMonthDays) * $payableDays;
            $grossSalary = $payableBasicSalary + $salary->hra + $salary->conveyance_allowance;
            $totalDeductions = $salary->pf + $salary->pt;
            $netSalary = $grossSalary - $totalDeductions;

            $hasNegativeSalary = $netSalary < 0;
            $hasMissingData = !$salary->basic_salary || !$employee->department;
            $needsReview = $hasNegativeSalary || $hasMissingData || $payableDays < 10;

            SalaryReport::create([
                'emp_id' => $employee->emp_id,
                'emp_name' => $employee->username,
                'designation' => $employee->position ?? 'N/A',
                'department' => $employee->department->name ?? 'N/A',
                'admin_id' => $employee->referrance,
                'region_id' => $employee->region_id,
                'report_date' => $reportDate,
                'month' => $month,
                'year' => $year,
                'total_working_days' => $totalMonthDays,
                'present_days' => $attendanceData['present_days'],
                'absent_days' => $attendanceData['absent_days'],
                'half_days' => $attendanceData['half_days'],
                'sick_leave' => $attendanceData['sick_leave'],
                'casual_leave' => $attendanceData['casual_leave'],
                'regularization' => $attendanceData['regularization'],
                'holidays' => $attendanceData['holidays'],
                'short_attendance' => $attendanceData['short_attendance'],
                'payable_days' => $payableDays,
                'basic_salary' => $salary->basic_salary,
                'hra' => $salary->hra,
                'conveyance_allowance' => $salary->conveyance_allowance,
                'pf' => $salary->pf,
                'pt' => $salary->pt,
                'payable_basic_salary' => $payableBasicSalary,
                'gross_salary' => $grossSalary,
                'total_deductions' => $totalDeductions,
                'net_salary' => $netSalary,
                'has_negative_salary' => $hasNegativeSalary,
                'has_missing_data' => $hasMissingData,
                'needs_review' => $needsReview,
                'status' => 'generated',
                'bank_name' => $salary->bank_name,
                'bank_account' => $salary->bank_account,
                'ifsc_code' => $salary->ifsc_code,
                'bank_branch' => $salary->bank_branch,
                'uan' => $salary->uan,
                'pf_no' => $salary->pf_no,
                'esic_no' => $salary->esic_no,
                'payment_mode' => $salary->payment_mode ?? 'bank_transfer'
            ]);

            $generatedCount++;
        }

        return redirect()->route('super-admin.reports')
            ->with('success', "Generated {$generatedCount} salary reports for " . date('F Y', mktime(0, 0, 0, $month, 1, $year)));
    }

    public function downloadSalaryReport($id)
    {
        $salaryReport = SalaryReport::with(['employee', 'region'])->findOrFail($id);
        $employee = Employee::where('emp_id', $salaryReport->emp_id)->first();
        
        $html = view('super-admin.reports.salary-report-pdf', compact('salaryReport', 'employee'))->render();
        
        $pdf = Browsershot::html($html)
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->pdf();

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="salary_slip_' . $salaryReport->emp_id . '_' . $salaryReport->month . '_' . $salaryReport->year . '.pdf"');
    }

    public function editSalaryReport($id)
    {
        $salaryReport = SalaryReport::with(['employee', 'region'])->findOrFail($id);
        
        // Get detailed daily attendance data with admin priority
        $salaryService = new SalaryCalculationService();
        $dailyAttendance = $salaryService->getDailyAttendanceDetails($salaryReport->emp_id, $salaryReport->month, $salaryReport->year, $salaryReport->admin_id);
        
        // Calculate per day basic salary
        $perDayBasicSalary = $salaryReport->basic_salary / $salaryReport->total_working_days;
        
        return view('super-admin.reports.edit-salary-report', compact('salaryReport', 'dailyAttendance', 'perDayBasicSalary'));
    }

    public function updateSalaryReport(Request $request, $id)
    {
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
            'pf' => 'required|numeric|min:0',
            'pt' => 'required|numeric|min:0',
            'payment_mode' => 'required|in:cash,bank_transfer',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:11',
            'bank_branch' => 'nullable|string|max:255',
            'uan' => 'nullable|string|max:12',
            'pf_no' => 'nullable|string|max:255',
            'esic_no' => 'nullable|string|max:17'
        ]);

        // Get original salary data for validation
        $salaryReport = SalaryReport::findOrFail($id);
        $employee = Employee::with('salary')->where('emp_id', $salaryReport->emp_id)->first();
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
        
        // Validate absent days + payable days don't exceed working days
        $payableDays = $request->present_days + $request->holidays + $request->sick_leave + 
                      $request->casual_leave + $request->regularization + 
                      ($request->half_days * 0.5) + ($request->short_attendance * 0.5);
        
        if (($request->absent_days + $payableDays) > $request->total_working_days) {
            return back()->withErrors(['absent_days' => 'Absent days + payable days cannot exceed total working days.'])
                        ->withInput();
        }
        
        $payableBasicSalary = ($request->basic_salary / $request->total_working_days) * $request->payable_days;
        $grossSalary = $payableBasicSalary + $request->hra + $request->conveyance_allowance;
        $totalDeductions = $request->pf + $request->pt;
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
            'pf' => $request->pf,
            'pt' => $request->pt,
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

        return redirect()->route('super-admin.reports')
            ->with('success', 'Salary report updated successfully');
    }

    public function settings()
    {
        $settings = SystemSetting::all()->keyBy('setting_key');
        return view('super-admin.settings.index', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validatedData = $request->validate([
            'weekend_policy' => 'nullable|string',
            'custom_saturday_weeks' => 'nullable|string'
        ]);

        // Handle weekend policy setting
        if ($request->has('weekend_policy')) {
            $weekendConfig = $this->getWeekendConfig($request->weekend_policy, $request->custom_saturday_weeks);
            
            SystemSetting::updateOrCreate(
                ['setting_key' => 'weekend_policy'],
                ['setting_value' => json_encode($weekendConfig)]
            );
        }

        // Handle other settings
        foreach ($request->except(['_token', 'weekend_policy', 'custom_saturday_weeks']) as $key => $value) {
            SystemSetting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value]
            );
        }

        return redirect()->route('super-admin.settings')->with('success', 'Settings updated successfully');
    }

    private function getWeekendConfig($policy, $customWeeks = null)
    {
        $weekendOptions = [
            'sunday_only' => [
                'label' => 'Sunday Only',
                'recurring_days' => [0],
                'specific_pattern' => []
            ],
            'sat_sun' => [
                'label' => 'Saturday & Sunday',
                'recurring_days' => [0, 6],
                'specific_pattern' => []
            ],
            'sun_2_4_sat' => [
                'label' => 'Sunday & 2nd/4th Saturday',
                'recurring_days' => [0],
                'specific_pattern' => [
                    6 => [2, 4]
                ]
            ],
            'sun_custom_sat' => [
                'label' => 'Sunday & Custom Saturday',
                'recurring_days' => [0],
                'specific_pattern' => [
                    6 => $customWeeks ? json_decode($customWeeks, true) : [1, 3]
                ]
            ]
        ];

        return $weekendOptions[$policy] ?? $weekendOptions['sunday_only'];
    }

    public function createAdmin()
    {
        $departments = Department::all();
        $regions = Region::all();
        return view('super-admin.admins.create', compact('departments', 'regions'));
    }

    public function storeAdmin(Request $request)
    {
        $request->validate([
            'emp_id' => 'required|unique:employees,emp_id',
            'name' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:employees,username',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'required|string|max:20',
            'department_id' => 'required|exists:departments,id',
            'region_id' => 'required|exists:regions,id',
            'position' => 'required|string|max:255',
            'hire_date' => 'required|date|before_or_equal:today',
            'dob' => 'required|date|before:' . Carbon::now()->subYears(18)->toDateString(),
            'address' => 'required|string',
            'password' => 'required|min:6|confirmed',
        ]);

        Employee::create([
            'emp_id' => $request->emp_id,
            'name' => $request->name,
            'full_name' => $request->full_name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'department_id' => $request->department_id,
            'region_id' => $request->region_id,
            'position' => $request->position,
            'hire_date' => $request->hire_date,
            'dob' => $request->dob,
            'address' => $request->address,
            'password_hash' => Hash::make($request->password),
            'role' => 'admin',
            'status' => 'active',
        ]);

        return redirect()->route('super-admin.admins')->with('success', 'Admin created successfully');
    }

    public function admins()
    {
        $perPage = request('per_page', 10);
        $search = request('search');
        $status = request('status');
        
        $admins = Employee::where('role', 'admin')
            ->when($search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('emp_id', 'like', '%' . $search . '%')
                      ->orWhere('username', 'like', '%' . $search . '%');
                });
            })
            ->when($status, function($query, $status) {
                $query->where('status', $status);
            })
            ->withCount(['assignedEmployees' => function($query) {
                $query->where('role', 'employee');
            }])
            ->paginate($perPage);
            
        return view('super-admin.admins.index', compact('admins'));
    }

    public function showAdmin($id)
    {
        $admin = Employee::with(['salary', 'department', 'region'])
            ->where('role', 'admin')
            ->findOrFail($id);
        
        // Get reference admin if exists
        $referenceAdmin = null;
        if ($admin->referrance) {
            $referenceAdmin = Employee::where('emp_id', $admin->referrance)->first();
        }
        
        // Paginate assigned employees with search
        $perPage = request('per_page', 10);
        $search = request('search');
        
        $assignedEmployees = Employee::where('referrance', $admin->emp_id)
            ->where('role', 'employee')
            ->when($search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('emp_id', 'like', '%' . $search . '%')
                      ->orWhere('username', 'like', '%' . $search . '%');
                });
            })
            ->with(['department', 'region'])
            ->paginate($perPage);
        
        if (request()->ajax()) {
            return view('super-admin.admins.partials.employee-list', compact('assignedEmployees'))->render();
        }
        
        return view('super-admin.admins.show', compact('admin', 'referenceAdmin', 'assignedEmployees'));
    }

    public function editAdmin($id)
    {
        $admin = Employee::with(['salary', 'department', 'region'])->where('role', 'admin')->findOrFail($id);
        $departments = Department::all();
        $regions = Region::all();
        $unassignedEmployees = Employee::where('role', 'employee')
            ->where(function($query) {
                $query->whereNull('referrance')->orWhere('referrance', '');
            })
            ->get();
        $assignedEmployees = Employee::where('role', 'employee')
            ->where('referrance', $admin->emp_id)
            ->get();
        $allAdmins = Employee::where('role', 'admin')->get();
        return view('super-admin.admins.edit', compact('admin', 'departments', 'regions', 'unassignedEmployees', 'assignedEmployees', 'allAdmins'));
    }

    public function updateAdmin(Request $request, $id)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $id,
            'department_id' => 'nullable|exists:departments,id',
            'region_id' => 'nullable|exists:regions,id',
            'position' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'address' => 'nullable|string|max:255',
            'referrance' => 'nullable|string|max:255',
            'password' => 'nullable|min:6|confirmed',
        ]);

        $admin = Employee::where('role', 'admin')->findOrFail($id);
        
        $updateData = $request->except(['password', 'password_confirmation']);
        
        if ($request->password) {
            $updateData['password'] = Hash::make($request->password);
        }
        
        $admin->update($updateData);
        
        // Handle employee assignments
        if ($request->has('assigned_employees')) {
            Employee::whereIn('emp_id', $request->assigned_employees)
                ->update(['referrance' => $admin->emp_id]);
        }
        
        // Handle employee unassignments
        if ($request->has('unassign_employees')) {
            Employee::whereIn('emp_id', $request->unassign_employees)
                ->update(['referrance' => null]);
        }

        return redirect()->route('super-admin.admins.edit', $id)
            ->with('success', 'Admin updated successfully');
    }

    public function salaries()
    {
        $salaries = Salary::with('employee')->paginate(15);
        return view('super-admin.salaries.index', compact('salaries'));
    }

    public function editSalary(Salary $salary)
    {
        return view('super-admin.salaries.edit', compact('salary'));
    }

    public function updateSalary(Request $request, Salary $salary)
    {
        $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'hra' => 'required|numeric|min:0',
            'conveyance_allowance' => 'required|numeric|min:0',
            'pf' => 'required|numeric|min:0',
            'pt' => 'required|numeric|min:0',
            'payment_mode' => 'required|in:cash,bank_transfer',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:11',
            'bank_branch' => 'nullable|string|max:255',
            'uan' => 'nullable|string|max:12',
            'pf_no' => 'nullable|string|max:255',
            'esic_no' => 'nullable|string|max:17'
        ]);

        $salary->update($request->only([
            'basic_salary', 'hra', 'conveyance_allowance', 'pf', 'pt', 'payment_mode',
            'bank_name', 'bank_account', 'ifsc_code', 'bank_branch', 'uan', 'pf_no', 'esic_no'
        ]));

        return redirect()->route('super-admin.salaries')->with('success', 'Salary updated successfully');
    }

    public function createSalary()
    {
        return view('super-admin.salaries.create');
    }

    public function getPendingEmployees()
    {
        $perPage = request('per_page', 10);
        $search = request('search');
        
        $employeesWithSalary = DB::table('salaries')->pluck('emp_id')->toArray();
        $employees = Employee::where('role', 'employee')
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
        $request->validate([
            'emp_id' => 'required|exists:employees,emp_id',
            'basic_salary' => 'required|numeric|min:0',
            'hra' => 'required|numeric|min:0',
            'conveyance_allowance' => 'required|numeric|min:0',
            'pf' => 'required|numeric|min:0',
            'pt' => 'required|numeric|min:0',
            'payment_mode' => 'required|in:cash,bank_transfer',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:11',
            'bank_branch' => 'nullable|string|max:255',
            'uan' => 'nullable|string|max:12',
            'pf_no' => 'nullable|string|max:255',
            'esic_no' => 'nullable|string|max:17'
        ]);

        Salary::create($request->all());

        return redirect()->route('super-admin.salaries')->with('success', 'Salary created successfully');
    }

    public function employeeHistory(Request $request)
    {
        $query = Employee::where(['role' => 'employee', 'status' => 'active']);
        
        if ($request->department && $request->department !== 'all') {
            $query->where('department_id', $request->department);
        }
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('username', 'like', "%{$request->search}%")
                  ->orWhere('emp_id', 'like', "%{$request->search}%");
            });
        }
        
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
        
        $employees = $query->with(['timeEntries' => function($q) use ($startDate, $endDate) {
            $q->whereBetween('entry_time', [$startDate, $endDate])
              ->orderBy('entry_time');
        }, 'department', 'entryImages' => function($q) use ($startDate, $endDate) {
            $q->whereBetween('entry_time', [$startDate, $endDate]);
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

        $admin_name = Employee::where('role', 'admin')->get()->keyBy('emp_id');
        $departments = Department::all();
        
        return view('super-admin.employee-history.index', compact('employees', 'departments', 'admin_name'));
    }

    public function timeEntries(Request $request)
    {
        // Handle AJAX requests for filtered data
        if ($request->ajax() || ($request->has('employee') || $request->has('from_date') || $request->has('to_date'))) {
            $query = TimeEntry::with('employee');
            
            // Filter by employee
            if ($request->employee) {
                $query->where('employee_id', $request->employee);
            }
            
            // Filter by date range
            if ($request->from_date && $request->to_date) {
                $query->whereBetween('entry_time', [$request->from_date . ' 00:00:00', $request->to_date . ' 23:59:59']);
            } elseif ($request->from_date) {
                $query->whereDate('entry_time', $request->from_date);
            } elseif ($request->to_date) {
                $query->whereDate('entry_time', '<=', $request->to_date);
            } elseif ($request->employee && !$request->from_date && !$request->to_date) {
                // If only employee is selected, show today's entries
                $query->whereDate('entry_time', today());
            }
            
            $entries = $query->orderBy('entry_time', 'desc')->get();
            
            // Get entry images
            $images = \App\Models\EntryImage::whereIn('entry_id', $entries->pluck('id'))
                ->get()
                ->keyBy('entry_id');
            
            if ($request->ajax()) {
                return response()->json([
                    'entries' => $entries,
                    'images' => $images
                ]);
            }
            
            // For non-AJAX requests, return view with filtered data
            $timeEntries = new \Illuminate\Pagination\LengthAwarePaginator(
                $entries,
                $entries->count(),
                20,
                1,
                ['path' => request()->url()]
            );
            
            $employees = Employee::where('role', 'employee')->get();
            return view('super-admin.time-entries.index', compact('timeEntries', 'employees'));
        }
        
        // Default view - show recent entries
        $query = TimeEntry::with('employee');
        $timeEntries = $query->orderBy('entry_time', 'desc')->paginate(20);
        $employees = Employee::where('role', 'employee')->get();
        
        return view('super-admin.time-entries.index', compact('timeEntries', 'employees'));
    }

    public function getEmployeeTimeEntries($empId, $date)
    {
        $timeEntries = TimeEntry::where('employee_id', $empId)
            ->whereDate('entry_time', $date)
            ->orderBy('entry_time')
            ->get();
            
        return response()->json([
            'success' => true,
            'timeEntries' => $timeEntries
        ]);
    }
    
    public function updateTimeEntries(Request $request)
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.id' => 'required|exists:time_entries,id',
            'updates.*.time' => 'required|date_format:H:i'
        ]);
        
        try {
            foreach ($request->updates as $update) {
                $timeEntry = TimeEntry::find($update['id']);
                $date = $timeEntry->entry_time->format('Y-m-d');
                $newDateTime = $date . ' ' . $update['time'] . ':00';
                $timeEntry->update(['entry_time' => $newDateTime]);
            }
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function addTimeEntry(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,emp_id',
            'date' => 'required|date',
            'entry_type' => 'required|in:punch_in,punch_out,lunch_start,lunch_end',
            'time' => 'required|date_format:H:i'
        ]);
        
        try {
            $entryTime = $request->date . ' ' . $request->time . ':00';
            
            TimeEntry::create([
                'employee_id' => $request->employee_id,
                'entry_type' => $request->entry_type,
                'entry_time' => $entryTime
            ]);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function deleteTimeEntry($id)
    {
        try {
            $timeEntry = TimeEntry::findOrFail($id);
            $timeEntry->delete();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function generateAttendanceReports(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020|max:2030'
        ]);

        $month = $request->month;
        $year = $request->year;
        
        // Get employees active during the requested month
        $requestedMonthStart = \Carbon\Carbon::create($year, $month, 1);
        $requestedMonthEnd = $requestedMonthStart->copy()->endOfMonth();
        
        $employees = Employee::where('role', 'employee')
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

        $salaryService = new SalaryCalculationService();
        $attendanceData = [];

        foreach ($employees as $employee) {
            // Double-check employee eligibility using service method
            if (!$salaryService->shouldIncludeEmployeeInReport($employee->emp_id, $month, $year)) {
                continue;
            }
            
            $attendance = $salaryService->calculatePayableDays($employee->emp_id, $month, $year, $employee->referrance);

            // $getkey = [];
            // foreach($attendance as $key){

            //     $getkey =  [$key." ->this is the responce"];
            // }

            // return $getkey;
            // return [$employee->emp_id, $employee->username,$attendance];
            
            $attendanceData[] = [
                'Employee ID' => $employee->emp_id,
                'Employee Name' => $employee->username,
                'Department' => $employee->department->name ?? 'N/A',
                'Position' => $employee->position ?? 'N/A',
                'Total Working Days' => date('t', mktime(0, 0, 0, $month, 1, $year)),
                'Present Days' => $attendance['present_days'],
                'Absent Days' => $attendance['absent_days'],
                'Sick Leave' => $attendance['sick_leave'],
                'Casual Leave' => $attendance['casual_leave'],
                'Half Days' => $attendance['half_days'],
                'Holidays' => $attendance['holidays'],
                'Regularization' => $attendance['regularization'],
                'Short Attendance' => $attendance['short_attendance'],
                'Total Payable Days' => $attendance['present_days'] + $attendance['holidays'] + 
                                       $attendance['sick_leave'] + $attendance['casual_leave'] + 
                                       ($attendance['half_days'] * 0.5) + ($attendance['short_attendance'] * 0.5) + 
                                       $attendance['regularization']
            ];
        }

        // Generate Excel file
        $filename = 'attendance_report_' . date('F_Y', mktime(0, 0, 0, $month, 1, $year)) . '.xlsx';
        
        return $this->generateExcelReport($attendanceData, $filename, $month, $year);
    }
    
    private function generateExcelReport($data, $filename, $month = null, $year = null)
    {
        // Generate CSV content
        $csvContent = "";
        
        // Add company header if month and year are provided
        if ($month && $year) {
            $companyName = "Time Tracking System";
            $monthName = date('F', mktime(0, 0, 0, $month, 1));
            $csvContent .= "\"$companyName\"\r\n";
            $csvContent .= "\"Attendance Report for $monthName $year\"\r\n";
            $csvContent .= "\"Generated on: " . now()->format('F j, Y') . "\"\r\n";
            $csvContent .= "\r\n"; // Empty row
        }
        
        // Header row
        if (!empty($data)) {
            $csvContent .= implode(',', array_map(function($header) {
                return '"' . str_replace('"', '""', $header) . '"';
            }, array_keys($data[0]))) . "\r\n";
            
            // Data rows
            foreach ($data as $row) {
                $csvContent .= implode(',', array_map(function($cell) {
                    return '"' . str_replace('"', '""', $cell) . '"';
                }, array_values($row))) . "\r\n";
            }
        }
        
        return response($csvContent)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="' . str_replace('.xlsx', '.csv', $filename) . '"')
            ->header('Cache-Control', 'max-age=0');
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
        
        return view('super-admin.employees.history', compact('employee', 'paginatedEntries', 'filter'));
    }

    public function updateTimeEntry(Request $request)
    {
        $request->validate([
            'entry_id' => 'required|exists:time_entries,id',
            'new_time' => 'required|date_format:Y-m-d H:i:s'
        ]);
        
        $timeEntry = TimeEntry::findOrFail($request->entry_id);
        
        $timeEntry->update([
            'entry_time' => $request->new_time,
            'notes' => ($timeEntry->notes ? $timeEntry->notes . ' | ' : '') . 'Edited by super admin on ' . now()->format('Y-m-d H:i:s')
        ]);
        
        return response()->json(['success' => true, 'message' => 'Time entry updated successfully']);
    }

    public function timeEntryImages(Request $request)
    {
        $period = $request->get('period', 'current_month');
        $search = $request->get('search');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        
        $query = \App\Models\EntryImage::with('employee');
        
        // Apply period filter
        switch ($period) {
            case 'current_month':
                $query->whereMonth('entry_time', now()->month)
                      ->whereYear('entry_time', now()->year);
                break;
            case 'last_month':
                $query->whereMonth('entry_time', now()->subMonth()->month)
                      ->whereYear('entry_time', now()->subMonth()->year);
                break;
            case 'custom':
                if ($fromDate && $toDate) {
                    $query->whereBetween('entry_time', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);
                }
                break;
        }
        
        // Apply search filter
        if ($search) {
            $query->whereHas('employee', function($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                  ->orWhere('emp_id', 'like', '%' . $search . '%');
            });
        }
        
        $images = $query->orderBy('entry_time', 'desc')->paginate(20);
        
        return view('super-admin.time-entry-images.index', compact('images'));
    }

    public function downloadImages(Request $request)
    {
        $images = $request->input('images', []);
        
        if (empty($images)) {
            return back()->with('error', 'No images selected for download.');
        }
        
        $zip = new \ZipArchive();
        $zipFileName = 'time_entry_images_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);
        
        // Create temp directory if it doesn't exist
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
            foreach ($images as $imageFile) {
                $imagePath = public_path('entry_images/' . $imageFile);
                if (file_exists($imagePath)) {
                    $zip->addFile($imagePath, $imageFile);
                }
            }
            $zip->close();
            
            return response()->download($zipPath)->deleteFileAfterSend(true);
        }
        
        return back()->with('error', 'Failed to create zip file.');
    }
    
    public function deleteImages(Request $request)
    {
        $imageIds = $request->input('image_ids', []);
        
        if (empty($imageIds)) {
            return back()->with('error', 'No images selected for deletion.');
        }
        
        $deletedCount = 0;
        
        foreach ($imageIds as $imageId) {
            $image = \App\Models\EntryImage::find($imageId);
            if ($image) {
                // Delete physical file
                $imagePath = public_path('entry_images/' . $image->imageFile);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                
                // Delete database record
                $image->delete();
                $deletedCount++;
            }
        }
        
        return back()->with('success', "Successfully deleted {$deletedCount} image(s).");
    }

    public function getSalarySlipPreview($id)
    {
        $salaryReport = SalaryReport::with(['employee', 'region'])->findOrFail($id);
        return response()->json($salaryReport);
    }

    public function showSalarySlipPreview($id)
    {

        $salaryReport = SalaryReport::with(['employee', 'region'])->findOrFail($id);
        $employee = Employee::with("region")->where('emp_id', $salaryReport->emp_id)->first();

        // return $employee;
        return view('super-admin.reports.salary-slip-preview', compact('salaryReport', 'employee'));
    }
    
    public function profile()
    {
        $superAdmin = auth('super_admin')->user();
        return view('super-admin.profile.index', compact('superAdmin'));
    }
    
    public function updateProfile(Request $request)
    {
        $superAdmin = auth('super_admin')->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:super_admins,username,' . $superAdmin->id,
            'email' => 'required|email|unique:super_admins,email,' . $superAdmin->id,
            'password' => 'nullable|min:6|confirmed',
        ]);
        
        $updateData = $request->only(['name', 'username', 'email']);
        
        if ($request->password) {
            $updateData['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
        }
        
        $superAdmin->update($updateData);
        
        return redirect()->route('super-admin.profile')->with('success', 'Profile updated successfully!');
    }

    public function showForgotPassword()
    {
        return view('super-admin.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $superAdmin = SuperAdmin::where('email', $request->email)->first();

        if (!$superAdmin) {
            return back()->withErrors(['email' => 'We can\'t find a super admin with that email address.']);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        $resetUrl = route('super-admin.password.reset', ['token' => $token]) . '?email=' . urlencode($request->email);

        try {
            Mail::send('emails.super-admin-password-reset', ['resetUrl' => $resetUrl, 'superAdmin' => $superAdmin], function($message) use($request) {
                $message->to($request->email);
                $message->subject('Super Admin Password Reset');
            });

            return back()->with('status', 'We have emailed your password reset link!');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Failed to send reset email. Please try again.']);
        }
    }

    public function showResetPassword(Request $request, $token)
    {
        return view('super-admin.auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

      public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset || !Hash::check($request->token, $passwordReset->token)) {
            return back()->withErrors(['email' => 'This password reset token is invalid.']);
        }

        if (Carbon::parse($passwordReset->created_at)->addMinutes(60)->isPast()) {
            return back()->withErrors(['email' => 'This password reset token has expired.']);
        }

        $superAdmin = SuperAdmin::where('email', $request->email)->first();
        $superAdmin->password = Hash::make($request->password);
        $superAdmin->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('super-admin.login')->with('status', 'Your password has been reset!');
    }

    public function checkSalaryReports(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020|max:2030'
        ]);

        $hasUnreleasedReports = SalaryReport::where('month', $request->month)
            ->where('year', $request->year)
            ->where('is_released', 0)
            ->exists();

        return response()->json(['hasUnreleasedReports' => $hasUnreleasedReports]);
    }

    public function releaseSalaryReports(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020|max:2030'
        ]);

        $month = $request->month;
        $year = $request->year;

        // Check if reports exist for this month/year
        $reportsCount = SalaryReport::where('month', $month)
            ->where('year', $year)
            ->count();

        if ($reportsCount === 0) {
            return redirect()->route('super-admin.reports')
                ->with('error', 'No salary reports found for ' . date('F Y', mktime(0, 0, 0, $month, 1, $year)));
        }

        // Release all reports for this month/year
        $releasedCount = SalaryReport::where('month', $month)
            ->where('year', $year)
            ->update(['is_released' => 1]);

        return redirect()->route('super-admin.reports')
            ->with('success', "Released {$releasedCount} salary reports for " . date('F Y', mktime(0, 0, 0, $month, 1, $year)));
    }
    
    public function leaveDays()
    {
        $currentSettings = \App\Models\LeaveCount::selectRaw('MAX(casual_leave) as casual_leave, MAX(sick_leave) as sick_leave')
            ->first()
            ->toArray();
        
        return view('super-admin.leave-days.index', compact('currentSettings'));
    }
    
    public function updateLeaveDays(Request $request)
    {
        $request->validate([
            'casual_leave' => 'required|integer|min:0|max:365',
            'sick_leave' => 'required|integer|min:0|max:365'
        ]);
        
        \App\Models\LeaveCount::query()->update([
            'casual_leave' => $request->casual_leave,
            'sick_leave' => $request->sick_leave
        ]);
        
        return redirect()->route('super-admin.leave-days')
            ->with('success', 'Leave days updated successfully for all employees');
    }
}