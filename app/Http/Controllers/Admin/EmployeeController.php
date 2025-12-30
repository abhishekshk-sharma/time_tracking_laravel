<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('admin.employees.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'emp_id' => 'required|unique:employees,emp_id',
            'full_name' => 'required|string|max:255',
            'username' => 'required|unique:employees,username',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:50',
            'hire_date' => 'nullable|date',
            'password' => 'required|min:8',
            'role' => 'required|in:employee,admin',
        ]);

        Employee::create([
            'emp_id' => $request->emp_id,
            'full_name' => $request->full_name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'department' => $request->department,
            'position' => $request->position,
            'hire_date' => $request->hire_date,
            'password_hash' => Hash::make($request->password),
            'role' => $request->role,
            'status' => 'active',
        ]);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee created successfully');
    }

    public function show(Employee $employee)
    {
        return view('admin.employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $departments = Department::all();
        return view('admin.employees.edit', compact('employee', 'departments'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => 'required|unique:employees,username,' . $employee->id,
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:50',
            'hire_date' => 'nullable|date',
            'status' => 'required|in:active,inactive,on_leave',
            'role' => 'required|in:employee,admin',
        ]);

        $updateData = $request->only([
            'full_name', 'username', 'email', 'phone', 
            'department', 'position', 'hire_date', 'status', 'role'
        ]);

        if ($request->filled('password')) {
            $updateData['password_hash'] = Hash::make($request->password);
        }

        $employee->update($updateData);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee updated successfully');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        
        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee deleted successfully');
    }
}