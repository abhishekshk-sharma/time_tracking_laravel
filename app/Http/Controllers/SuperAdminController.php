<?php

namespace App\Http\Controllers;

use App\Models\SuperAdmin;
use App\Models\Employee;
use App\Models\Salary;
use App\Models\SalaryReport;
use App\Models\Department;
use App\Models\Application;
use App\Models\TimeEntry;
use App\Models\Region;
use App\Models\SystemSetting;
use App\Models\ScheduleException;
use App\Services\SalaryCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Spatie\Browsershot\Browsershot;

class SuperAdminController extends Controller
{
    public function showLogin()
    {
        return view('super-admin.auth.login');
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

        return view('super-admin.dashboard', compact('stats'));
    }

    // Schedule Management
    public function schedule()
    {
        $currentMonth = request('month', Carbon::now()->month);
        $currentYear = request('year', Carbon::now()->year);
        
        // Get all schedule exceptions for the current month/year regardless of who created them
        $scheduleExceptions = ScheduleException::whereMonth('exception_date', $currentMonth)
            ->whereYear('exception_date', $currentYear)
            ->get()
            ->keyBy('exception_date');
        
        $calendar = $this->generateCalendar($currentYear, $currentMonth, $scheduleExceptions);
        
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
        $startOfWeek = $firstDay->copy()->startOfWeek();
        $endOfWeek = $lastDay->copy()->endOfWeek();
        
        $calendar = [];
        $current = $startOfWeek->copy();
        
        while ($current <= $endOfWeek) {
            $dateStr = $current->format('Y-m-d');
            $exception = $scheduleExceptions->get($dateStr);
            
            $calendar[] = [
                'date' => $current->copy(),
                'is_current_month' => $current->month == $month,
                'exception' => $exception
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

    // Other methods can be added as needed
    public function employees()
    {
        $perPage = request('per_page', 10);
        $search = request('search');
        $adminFilter = request('admin_filter');
        
        $employees = Employee::where('role', 'employee')
            ->when($search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('emp_id', 'like', '%' . $search . '%')
                      ->orWhere('username', 'like', '%' . $search . '%');
                });
            })
            ->when($adminFilter, function($query, $adminFilter) {
                $query->where('referrance', $adminFilter);
            })
            ->with(['department', 'region'])
            ->paginate($perPage);
            
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

        // return dd($departments);
        return view('super-admin.employees.edit', compact('employee', 'departments', 'regions', 'admins'));
    }

    public function updateEmployee(Request $request, $id)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $id,
            'role' => 'required|in:employee,admin',
            'department_id' => 'nullable|exists:departments,id',
            'region_id' => 'nullable|exists:regions,id',
            'position' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'referrance' => 'nullable|string|max:255'
        ]);

        $employee = Employee::findOrFail($id);
        $employee->update($request->all());
        
        // return dd($request->all());
        return redirect()->route('super-admin.employees.edit', $id)
            ->with('success', 'Employee updated successfully');
    }

    public function applications()
    {
        $applications = Application::with('employee')->orderBy('created_at', 'desc')->paginate(15);
        return view('super-admin.applications.index', compact('applications'));
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
            }, 'department']);
            
        $employees = $query->paginate($perPage);

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
        
        $salaryReports = SalaryReport::with(['employee', 'region'])
            ->when($search, function($query, $search) {
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

        $employees = Employee::where('status', 'active')
            ->where('role', 'employee')
            ->with(['salary', 'department', 'region'])
            ->get();

        $salaryService = new SalaryCalculationService();
        $generatedCount = 0;

        foreach ($employees as $employee) {
            $attendanceData = $salaryService->calculatePayableDays($employee->emp_id, $month, $year);
            $salary = $employee->salary;

            if (!$salary) continue;

            // Calculate total days in month
            $totalMonthDays = date('t', mktime(0, 0, 0, $month, 1, $year));
            
            // Calculate payable days: present + holidays + sick/casual leave + (half_days * 0.5) + (short_attendance * 0.5)
            $payableDays = $attendanceData['present_days'] + $attendanceData['holidays'] + 
                          $attendanceData['sick_leave'] + $attendanceData['casual_leave'] + 
                          ($attendanceData['half_days'] * 0.5) + ($attendanceData['short_attendance'] * 0.5)
                          + $attendanceData['week_off'];
            
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
                'regularization' => $attendanceData['week_off'],
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
                'status' => 'generated'
            ]);

            $generatedCount++;
        }

        return redirect()->route('super-admin.reports')
            ->with('success', "Generated {$generatedCount} salary reports for " . date('F Y', mktime(0, 0, 0, $month, 1, $year)));
    }

    public function downloadSalaryReport($id)
    {
        $salaryReport = SalaryReport::with(['employee', 'region'])->findOrFail($id);
        
        $html = view('super-admin.reports.salary-report-pdf', compact('salaryReport'))->render();
        
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
        
        // Get detailed daily attendance data
        $salaryService = new SalaryCalculationService();
        $dailyAttendance = $salaryService->getDailyAttendanceDetails($salaryReport->emp_id, $salaryReport->month, $salaryReport->year);
        
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
            'pt' => 'required|numeric|min:0'
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
            'status' => 'reviewed'
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
        $allAdmins = Employee::where('role', 'admin')->get();
        return view('super-admin.admins.edit', compact('admin', 'departments', 'regions', 'unassignedEmployees', 'allAdmins'));
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
            'referrance' => 'nullable|string|max:255'
        ]);

        $admin = Employee::where('role', 'admin')->findOrFail($id);
        $admin->update($request->all());

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
            'pt' => 'required|numeric|min:0'
        ]);

        $salary->update($request->only(['basic_salary', 'hra', 'conveyance_allowance', 'pf', 'pt']));

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
            'pt' => 'required|numeric|min:0'
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
        }, 'department'])->paginate(1);

        $admin_name = Employee::where('role', 'admin')->get()->keyBy('emp_id');
         
        // return dd($admin_name);
        $departments = Department::all();
        
        return view('super-admin.employee-history.index', compact('employees', 'departments', 'admin_name'));
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

    public function generateAttendanceReports(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020|max:2030'
        ]);

        $month = $request->month;
        $year = $request->year;
        
        $employees = Employee::where('status', 'active')
            ->where('role', 'employee')
            ->with(['department', 'region'])
            ->get();

        $salaryService = new SalaryCalculationService();
        $attendanceData = [];

        foreach ($employees as $employee) {
            $attendance = $salaryService->calculatePayableDays($employee->emp_id, $month, $year);
            
            $attendanceData[] = [
                'Employee ID' => $employee->emp_id,
                'Employee Name' => $employee->username,
                'Department' => $employee->department->name ?? 'N/A',
                'Position' => $employee->position ?? 'N/A',
                'Month' => date('F', mktime(0, 0, 0, $month, 1)),
                'Year' => $year,
                'Total Working Days' => date('t', mktime(0, 0, 0, $month, 1, $year)),
                'Present Days' => $attendance['present_days'],
                'Absent Days' => $attendance['absent_days'],
                'Sick Leave' => $attendance['sick_leave'],
                'Casual Leave' => $attendance['casual_leave'],
                'Half Days' => $attendance['half_days'],
                'Holidays' => $attendance['holidays'],
                'Regularization' => $attendance['week_off'],
                'Short Attendance' => $attendance['short_attendance'],
                'Total Payable Days' => $attendance['present_days'] + $attendance['holidays'] + 
                                       $attendance['sick_leave'] + $attendance['casual_leave'] + 
                                       ($attendance['half_days'] * 0.5) + ($attendance['short_attendance'] * 0.5) + 
                                       $attendance['week_off']
            ];
        }

        // Generate Excel file
        $filename = 'attendance_report_' . date('F_Y', mktime(0, 0, 0, $month, 1, $year)) . '.xlsx';
        
        return $this->generateExcelReport($attendanceData, $filename);
    }
    
    private function generateExcelReport($data, $filename)
    {
        // Generate CSV content
        $csvContent = "";
        
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

    public function deleteTimeEntry(TimeEntry $timeEntry)
    {
        $timeEntry->delete();
        return response()->json(['success' => true]);
    }
}