<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\TimeEntry;
use App\Models\Application;
use App\Models\Wfh;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EmployeeApiController extends Controller
{
    // Login
    public function login(Request $request)
    {
        $request->validate([
            'emp_id' => 'required',
            'password' => 'required'
        ]);

        $employee = Employee::where('emp_id', $request->emp_id)->first();

        if (!$employee || !Hash::check($request->password, $employee->password_hash)) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'Invalid credentials']
            ], 401);
        }

        if ($employee->status !== 'active') {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'Account is not active']
            ], 403);
        }

        $token = $employee->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'employee' => [
                    'emp_id' => $employee->emp_id,
                    'full_name' => $employee->full_name,
                    'username' => $employee->username,
                    'email' => $employee->email,
                    'phone' => $employee->phone,
                    'department' => $employee->department?->name,
                    'position' => $employee->position,
                    'role' => $employee->role,
                    'region' => $employee->region?->name
                ]
            ]
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'data' => ['message' => 'Logged out successfully']
        ]);
    }

    // Get Profile
    public function profile(Request $request)
    {
        $employee = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'emp_id' => $employee->emp_id,
                'full_name' => $employee->full_name,
                'username' => $employee->username,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'department' => $employee->department?->name,
                'position' => $employee->position,
                'hire_date' => $employee->hire_date?->format('Y-m-d'),
                'dob' => $employee->dob?->format('Y-m-d'),
                'address' => $employee->address,
                'region' => $employee->region?->name,
                'status' => $employee->status
            ]
        ]);
    }

    // Clock In
    public function clockIn(Request $request)
    {
        $employee = $request->user();
        $today = Carbon::today();

        $existingEntry = TimeEntry::where('employee_id', $employee->emp_id)
            ->whereDate('entry_time', $today)
            ->where('entry_type', 'clock_in')
            ->first();

        if ($existingEntry) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'Already clocked in today']
            ], 400);
        }

        $entry = TimeEntry::create([
            'employee_id' => $employee->emp_id,
            'entry_type' => 'clock_in',
            'entry_time' => now(),
            'location' => $request->location,
            'notes' => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Clocked in successfully',
                'entry_time' => $entry->entry_time->format('Y-m-d H:i:s')
            ]
        ]);
    }

    // Clock Out
    public function clockOut(Request $request)
    {
        $employee = $request->user();
        $today = Carbon::today();

        $clockIn = TimeEntry::where('employee_id', $employee->emp_id)
            ->whereDate('entry_time', $today)
            ->where('entry_type', 'clock_in')
            ->first();

        if (!$clockIn) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'No clock in found for today']
            ], 400);
        }

        $existingClockOut = TimeEntry::where('employee_id', $employee->emp_id)
            ->whereDate('entry_time', $today)
            ->where('entry_type', 'clock_out')
            ->first();

        if ($existingClockOut) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'Already clocked out today']
            ], 400);
        }

        $entry = TimeEntry::create([
            'employee_id' => $employee->emp_id,
            'entry_type' => 'clock_out',
            'entry_time' => now(),
            'location' => $request->location,
            'notes' => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Clocked out successfully',
                'entry_time' => $entry->entry_time->format('Y-m-d H:i:s')
            ]
        ]);
    }

    // Lunch Start
    public function lunchStart(Request $request)
    {
        $employee = $request->user();
        $today = Carbon::today();

        $existingLunch = TimeEntry::where('employee_id', $employee->emp_id)
            ->whereDate('entry_time', $today)
            ->where('entry_type', 'lunch_start')
            ->whereDoesntHave('lunchEnd')
            ->first();

        if ($existingLunch) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'Lunch already started']
            ], 400);
        }

        $entry = TimeEntry::create([
            'employee_id' => $employee->emp_id,
            'entry_type' => 'lunch_start',
            'entry_time' => now(),
            'location' => $request->location
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Lunch started',
                'entry_time' => $entry->entry_time->format('Y-m-d H:i:s')
            ]
        ]);
    }

    // Lunch End
    public function lunchEnd(Request $request)
    {
        $employee = $request->user();
        $today = Carbon::today();

        $lunchStart = TimeEntry::where('employee_id', $employee->emp_id)
            ->whereDate('entry_time', $today)
            ->where('entry_type', 'lunch_start')
            ->orderBy('entry_time', 'desc')
            ->first();

        if (!$lunchStart) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'No lunch start found']
            ], 400);
        }

        $existingLunchEnd = TimeEntry::where('employee_id', $employee->emp_id)
            ->where('entry_type', 'lunch_end')
            ->where('entry_time', '>', $lunchStart->entry_time)
            ->first();

        if ($existingLunchEnd) {
            return response()->json([
                'success' => false,
                'data' => ['message' => 'Lunch already ended']
            ], 400);
        }

        $entry = TimeEntry::create([
            'employee_id' => $employee->emp_id,
            'entry_type' => 'lunch_end',
            'entry_time' => now(),
            'location' => $request->location
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Lunch ended',
                'entry_time' => $entry->entry_time->format('Y-m-d H:i:s')
            ]
        ]);
    }

    // Get Today's Status
    public function todayStatus(Request $request)
    {
        $employee = $request->user();
        $today = Carbon::today();

        $entries = TimeEntry::where('employee_id', $employee->emp_id)
            ->whereDate('entry_time', $today)
            ->orderBy('entry_time')
            ->get();

        $clockIn = $entries->where('entry_type', 'clock_in')->first();
        $clockOut = $entries->where('entry_type', 'clock_out')->first();
        $lunchStart = $entries->where('entry_type', 'lunch_start')->last();
        $lunchEnd = $entries->where('entry_type', 'lunch_end')->last();

        $status = [
            'is_clocked_in' => $clockIn && !$clockOut,
            'is_on_lunch' => $lunchStart && !$lunchEnd,
            'clock_in_time' => $clockIn?->entry_time->format('H:i:s'),
            'clock_out_time' => $clockOut?->entry_time->format('H:i:s'),
            'lunch_start_time' => $lunchStart?->entry_time->format('H:i:s'),
            'lunch_end_time' => $lunchEnd?->entry_time->format('H:i:s')
        ];

        return response()->json([
            'success' => true,
            'data' => $status
        ]);
    }

    // Get Attendance History
    public function attendanceHistory(Request $request)
    {
        $employee = $request->user();
        $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        $entries = TimeEntry::where('employee_id', $employee->emp_id)
            ->whereBetween('entry_time', [$startDate, $endDate])
            ->orderBy('entry_time', 'desc')
            ->get()
            ->groupBy(fn($entry) => $entry->entry_time->format('Y-m-d'));

        $history = [];
        foreach ($entries as $date => $dayEntries) {
            $history[] = [
                'date' => $date,
                'clock_in' => $dayEntries->where('entry_type', 'clock_in')->first()?->entry_time->format('H:i:s'),
                'clock_out' => $dayEntries->where('entry_type', 'clock_out')->first()?->entry_time->format('H:i:s'),
                'lunch_start' => $dayEntries->where('entry_type', 'lunch_start')->first()?->entry_time->format('H:i:s'),
                'lunch_end' => $dayEntries->where('entry_type', 'lunch_end')->first()?->entry_time->format('H:i:s')
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    // Apply Leave
    public function applyLeave(Request $request)
    {
        $request->validate([
            'leave_type' => 'required|in:sick,casual,annual',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string'
        ]);

        $employee = $request->user();

        $application = Application::create([
            'employee_id' => $employee->emp_id,
            'application_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => 'pending',
            'applied_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Leave application submitted successfully',
                'application_id' => $application->id
            ]
        ]);
    }

    // Get Leave Applications
    public function leaveApplications(Request $request)
    {
        $employee = $request->user();

        $applications = Application::where('employee_id', $employee->emp_id)
            ->orderBy('applied_at', 'desc')
            ->get()
            ->map(fn($app) => [
                'id' => $app->id,
                'type' => $app->application_type,
                'start_date' => $app->start_date,
                'end_date' => $app->end_date,
                'reason' => $app->reason,
                'status' => $app->status,
                'applied_at' => $app->applied_at->format('Y-m-d H:i:s'),
                'approved_at' => $app->approved_at?->format('Y-m-d H:i:s')
            ]);

        return response()->json([
            'success' => true,
            'data' => $applications
        ]);
    }

    // Apply WFH
    public function applyWfh(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'reason' => 'required|string'
        ]);

        $employee = $request->user();

        $wfh = Wfh::create([
            'employee_id' => $employee->emp_id,
            'wfh_date' => $request->date,
            'reason' => $request->reason,
            'status' => 'pending',
            'requested_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'WFH request submitted successfully',
                'wfh_id' => $wfh->id
            ]
        ]);
    }

    // Get WFH Requests
    public function wfhRequests(Request $request)
    {
        $employee = $request->user();

        $requests = Wfh::where('employee_id', $employee->emp_id)
            ->orderBy('requested_at', 'desc')
            ->get()
            ->map(fn($wfh) => [
                'id' => $wfh->id,
                'date' => $wfh->wfh_date,
                'reason' => $wfh->reason,
                'status' => $wfh->status,
                'requested_at' => $wfh->requested_at->format('Y-m-d H:i:s')
            ]);

        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    }

    // Get Leave Balance
    public function leaveBalance(Request $request)
    {
        $employee = $request->user();
        $leaveCount = $employee->leaveCount;

        return response()->json([
            'success' => true,
            'data' => [
                'sick_leave' => $leaveCount?->sick_leave ?? 0,
                'casual_leave' => $leaveCount?->casual_leave ?? 0,
                'annual_leave' => $leaveCount?->annual_leave ?? 0
            ]
        ]);
    }

    // Get System Settings
    public function systemSettings()
    {
        $settings = SystemSetting::pluck('setting_value', 'setting_key');

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }
}
