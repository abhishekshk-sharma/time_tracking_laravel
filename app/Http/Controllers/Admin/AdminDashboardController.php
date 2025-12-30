<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\TimeEntry;
use App\Models\Application;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalEmployees = Employee::count();
        
        return view('admin.dashboard.index', compact('totalEmployees'));
    }

    public function getEmployeeData(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $searchInput = $request->input('searchInput');
        $searchDate = $request->input('searchdate');
        
        $query = Employee::query();
        
        if ($searchInput) {
            $query->where('username', 'like', "%{$searchInput}%");
        }
        
        $employees = $query->paginate($limit, ['*'], 'page', $page);
        
        $output = '';
        foreach ($employees as $employee) {
            $date = $searchDate ? Carbon::parse($searchDate) : today();
            $timeEntries = TimeEntry::where('employee_id', $employee->emp_id)
                ->whereDate('entry_time', $date)
                ->orderBy('entry_time')
                ->get();
            
            $punchIn = $timeEntries->where('entry_type', 'punch_in')->first();
            $lunchStart = $timeEntries->where('entry_type', 'lunch_start')->first();
            $lunchEnd = $timeEntries->where('entry_type', 'lunch_end')->first();
            $punchOut = $timeEntries->where('entry_type', 'punch_out')->first();
            
            $workingHours = TimeEntry::calculateWorkingHours($employee->emp_id, $date);
            
            $output .= '<tr>';
            $output .= '<td><a href="#" class="touser" data-id="' . $employee->emp_id . '">' . $employee->username . '</a></td>';
            $output .= '<td>' . $date->format('Y-m-d') . '</td>';
            $output .= '<td>' . ($punchIn ? $punchIn->entry_time->format('H:i:s') : '-') . '</td>';
            $output .= '<td>' . ($lunchStart ? $lunchStart->entry_time->format('H:i:s') : '-') . '</td>';
            $output .= '<td>' . ($lunchEnd ? $lunchEnd->entry_time->format('H:i:s') : '-') . '</td>';
            $output .= '<td>' . ($punchOut ? $punchOut->entry_time->format('H:i:s') : '-') . '</td>';
            $output .= '<td>' . floor($workingHours['work_minutes']/60) . 'h ' . ($workingHours['work_minutes']%60) . 'm</td>';
            $output .= '<td>' . ($punchIn ? 'Present' : 'Absent') . '</td>';
            $output .= '</tr>';
        }
        
        return response()->json([
            'output' => $output,
            'row' => $employees->total()
        ]);
    }

    public function applications()
    {
        $applications = Application::with('employee')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('admin.applications.index', compact('applications'));
    }

    public function approveApplication(Application $application)
    {
        $application->update([
            'status' => 'approved',
            'action_by' => Auth::user()->emp_id
        ]);

        // Create notification
        Notification::create([
            'App_id' => $application->id,
            'created_by' => Auth::user()->emp_id,
            'notify_to' => $application->employee_id,
            'status' => 'pending'
        ]);

        return response()->json(['success' => true, 'message' => 'Application approved successfully']);
    }

    public function rejectApplication(Application $application)
    {
        $application->update([
            'status' => 'rejected',
            'action_by' => Auth::user()->emp_id
        ]);

        // Create notification
        Notification::create([
            'App_id' => $application->id,
            'created_by' => Auth::user()->emp_id,
            'notify_to' => $application->employee_id,
            'status' => 'pending'
        ]);

        return response()->json(['success' => true, 'message' => 'Application rejected successfully']);
    }
}