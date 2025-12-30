<?php

namespace App\Http\Controllers;

use App\Models\SuperAdmin;
use App\Models\Employee;
use App\Models\Salary;
use App\Models\Department;
use App\Models\Application;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

    public function showRegister()
    {
        return view('super-admin.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:super_admins',
            'email' => 'required|email|unique:super_admins',
            'name' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        SuperAdmin::create([
            'username' => $request->username,
            'email' => $request->email,
            'name' => $request->name,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('super-admin.login')->with('success', 'Super admin account created successfully');
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

    // Salary Management
    public function salaries()
    {
        $salaries = Salary::with('employee')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('super-admin.salaries.index', compact('salaries'));
    }

    public function createSalary()
    {
        $employees = Employee::where('status', 'active')->get();
        return view('super-admin.salaries.create', compact('employees'));
    }

    public function storeSalary(Request $request)
    {
        $request->validate([
            'emp_id' => 'required|exists:employees,emp_id',
            'basic_salary' => 'required|numeric|min:0',
            'hra' => 'nullable|numeric|min:0',
            'pf' => 'nullable|numeric|min:0',
            'pt' => 'nullable|numeric|min:0',
            'ta' => 'nullable|numeric|min:0',
            'conveyance_allowance' => 'nullable|numeric|min:0',
            'effective_from' => 'required|date',
        ]);

        // Deactivate previous salary records
        Salary::where('emp_id', $request->emp_id)->update(['is_active' => false]);

        $gross_salary = $request->basic_salary + $request->hra + $request->ta + $request->conveyance_allowance - $request->pf - $request->pt;

        Salary::create([
            'emp_id' => $request->emp_id,
            'basic_salary' => $request->basic_salary,
            'hra' => $request->hra ?? 0,
            'pf' => $request->pf ?? 0,
            'is_pf' => $request->has('auto_pf'),
            'pt' => $request->pt ?? 0,
            'ta' => $request->ta ?? 0,
            'conveyance_allowance' => $request->conveyance_allowance ?? 0,
            'gross_salary' => $gross_salary,
            'effective_from' => $request->effective_from,
        ]);

        return redirect()->route('super-admin.salaries')->with('success', 'Salary created successfully');
    }

    public function editSalary(Salary $salary)
    {
        $employees = Employee::where('status', 'active')->get();
        return view('super-admin.salaries.edit', compact('salary', 'employees'));
    }

    public function updateSalary(Request $request, Salary $salary)
    {
        $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'hra' => 'nullable|numeric|min:0',
            'pf' => 'nullable|numeric|min:0',
            'pt' => 'nullable|numeric|min:0',
            'ta' => 'nullable|numeric|min:0',
            'conveyance_allowance' => 'nullable|numeric|min:0',
            'effective_from' => 'required|date',
        ]);

        $gross_salary = $request->basic_salary + $request->hra + $request->ta + $request->conveyance_allowance - $request->pf - $request->pt;

        $salary->update([
            'basic_salary' => $request->basic_salary,
            'hra' => $request->hra ?? 0,
            'pf' => $request->pf ?? 0,
            'is_pf' => $request->has('auto_pf'),
            'pt' => $request->pt ?? 0,
            'ta' => $request->ta ?? 0,
            'conveyance_allowance' => $request->conveyance_allowance ?? 0,
            'gross_salary' => $gross_salary,
            'effective_from' => $request->effective_from,
        ]);

        return redirect()->route('super-admin.salaries')->with('success', 'Salary updated successfully');
    }

    // Copy all admin methods for super admin access
    public function employees()
    {
        $query = Employee::where('role', 'employee')
            ->with(['department', 'salary' => function($query) {
                $query->where('is_active', true);
            }]);

        // Filter by status
        $status = request('status', 'active');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $employees = $query->paginate(15);

        return view('super-admin.employees.index', compact('employees'));
    }

    public function applications()
    {
        $applications = Application::with('employee')->orderBy('created_at', 'desc')->paginate(15);
        return view('super-admin.applications.index', compact('applications'));
    }

    public function attendance()
    {
        $date = request('date', Carbon::today()->format('Y-m-d'));
        $employees = Employee::where('status', 'active')->with(['timeEntries' => function($query) use ($date) {
            $query->whereDate('entry_time', $date);
        }])->get();

        return view('super-admin.attendance.index', compact('employees', 'date'));
    }

    public function reports()
    {
        $departments = Department::all();
        $employees = Employee::where('status', 'active')->get();
        
        return view('super-admin.reports.index', compact('departments', 'employees'));
    }

    public function showEmployee(Employee $employee)
    {
        $employee->load(['department', 'salary' => function($query) {
            $query->where('is_active', true);
        }]);
        
        return view('super-admin.employees.show', compact('employee'));
    }

    public function editEmployee(Employee $employee)
    {
        $departments = Department::all();
        return view('super-admin.employees.edit', compact('employee', 'departments'));
    }

    public function updateEmployee(Request $request, Employee $employee)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:employees,username,' . $employee->id,
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'dob' => 'nullable|date',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $employee->update($request->only([
            'name', 'username', 'email', 'phone', 'department', 'position', 
            'region', 'hire_date', 'end_date', 'dob', 'address', 'status'
        ]));

        return redirect()->route('super-admin.employees.show', $employee)->with('success', 'Employee updated successfully');
    }

    public function settings()
    {
        $settings = \App\Models\SystemSetting::all()->keyBy('setting_key');
        return view('super-admin.settings.index', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            \App\Models\SystemSetting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value]
            );
        }

        return redirect()->route('super-admin.settings')->with('success', 'Settings updated successfully');
    }

    public function admins()
    {
        $admins = Employee::where('role', 'admin')
            ->withCount(['assignedEmployees' => function($query) {
                $query->where('role', 'employee');
            }])
            ->paginate(15);
            
        return view('super-admin.admins.index', compact('admins'));
    }

    public function showAdmin(Employee $admin)
    {
        $admin->load(['assignedEmployees' => function($query) {
            $query->where('role', 'employee');
        }]);
        
        return view('super-admin.admins.show', compact('admin'));
    }

    public function editAdmin(Employee $admin)
    {
        $unassignedEmployees = Employee::where('role', 'employee')
            ->whereNull('referrance')
            ->orWhere('referrance', $admin->emp_id)
            ->get();
            
        return view('super-admin.admins.edit', compact('admin', 'unassignedEmployees'));
    }

    public function updateAdmin(Request $request, Employee $admin)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:employees,username,' . $admin->id,
            'email' => 'required|email|unique:employees,email,' . $admin->id,
            'phone' => 'nullable|string|max:20',
            'assigned_employees' => 'nullable|array',
            'assigned_employees.*' => 'exists:employees,emp_id'
        ]);

        $admin->update($request->only(['name', 'username', 'email', 'phone']));

        // Update employee assignments
        Employee::where('referrance', $admin->emp_id)->update(['referrance' => null]);
        
        if ($request->assigned_employees) {
            Employee::whereIn('emp_id', $request->assigned_employees)
                ->update(['referrance' => $admin->emp_id]);
        }

        return redirect()->route('super-admin.admins.show', $admin)
            ->with('success', 'Admin updated successfully');
    }
}