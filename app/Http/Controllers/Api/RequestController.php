<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Employee;
use App\Models\TimeEntry;
use App\Models\SystemSetting;
use App\Models\LeaveCount;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class RequestController extends Controller
{
    public function check(Request $request)
    {
        $userid = Auth::user()->emp_id;
        $id = $request->input('id');
        
        $applications = Application::where('employee_id', $id)
            ->whereNotIn('req_type', ['Birthday'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->orderBy('id', 'desc')
            ->get();

        if ($applications->count() >= 1) {
            $output = "";
            foreach ($applications as $row) {
                $create = Carbon::parse($row->created_at)->setTimezone('Asia/Kolkata');
                $time = $create->format('Y-m-d H:i:s');

                $start = Carbon::parse($row->start_date)->setTimezone('Asia/Kolkata');
                $end = $row->end_date ? Carbon::parse($row->end_date)->setTimezone('Asia/Kolkata') : null;
                
                $output .= "<tr>
                                <td>{$row->created_at}</td>
                                <td>{$row->req_type}</td>
                                <td>{$row->subject}</td>
                                <td>{$start->format('M d')} - " . ($end ? $end->format('M d') : "") . "</td>
                                <td><span class='status-badge status-{$row->status}'>{$row->status}</span></td>
                                <td>
                                    <button class='btn btn-sm btn-secondary view_request' data-id='{$row->employee_id}' data-type='{$row->req_type}' data-time='{$time}' style='padding-right: 8px;'>
                                        <i class='fas fa-eye'></i> 
                                    </button>
                                </td>
                            </tr>";
            }
            return response($output);
        } else {
            return response("<p style='color:red;'>Not Found!</p>");
        }
    }

    public function store(Request $request)
    {
        try {
            // Add debugging
            \Log::info('Request store called with data:', $request->all());
            
            $userid = Auth::user()->emp_id;
            $info = $request->input('info');
            
            \Log::info('User ID: ' . $userid . ', Info: ' . $info);

            switch ($info) {
                case 'casualLeave':
                    return $this->handleCasualLeave($request, $userid);
                case 'sick_leave':
                    return $this->handleSickLeave($request, $userid);
                case 'half_day':
                    return $this->handleHalfDay($request, $userid);
                case 'complaint':
                case 'other':
                    return $this->handleComplaintOrOther($request, $userid);
                case 'regularization':
                    return $this->handleRegularization($request, $userid);
                case 'punch_Out_regularization':
                    return $this->handlePunchOutRegularization($request, $userid);
                case 'work_from_home':
                    return $this->handleWorkFromHome($request, $userid);
                default:
                    \Log::error('Invalid request type: ' . $info);
                    return response('Invalid request type', 400);
            }
        } catch (\Exception $e) {
            \Log::error('Application store error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response('Error: ' . $e->getMessage(), 500);
        }
    }

    private function handleCasualLeave(Request $request, $userid)
    {
        try {
            \Log::info('handleCasualLeave called with data:', $request->all());
            
            // Basic validation
            if (!$request->input('id')) {
                return response('Employee ID is required.', 400);
            }
            
            if (!$request->input('type')) {
                return response('Request type is required.', 400);
            }
            
            if (!$request->input('subject')) {
                return response('Subject is required.', 400);
            }
            
            if (!$request->input('start_date') || !$request->input('end_date')) {
                return response('Start and end dates are required.', 400);
            }

            $id = $request->input('id');
            $type = $request->input('type');
            $subject = $request->input('subject');
            $description = $request->input('description', '');
            $start_date = Carbon::parse($request->input('start_date'));
            $end_date = Carbon::parse($request->input('end_date'));

            if ($start_date > $end_date) {
                return response('Start date must be before end date.', 400);
            }

            // Calculate requested leave days
            $requestedDays = $start_date->diffInDays($end_date) + 1;
            
            // Check remaining casual leave
            $leaveCount = LeaveCount::where('employee_id', $userid)->first();
            $remainingLeave = $leaveCount ? $leaveCount->casual_leave : 0;
            
            if ($remainingLeave < $requestedDays) {
                return response("Insufficient casual leave balance! Available: {$remainingLeave} days, Requested: {$requestedDays} days", 400);
            }

            // Handle file upload
            $filePath = '';
            if ($request->hasFile('image')) {
                try {
                    $filePath = $this->handleFileUpload($request);
                } catch (\Exception $e) {
                    \Log::error('File upload error: ' . $e->getMessage());
                    // Continue without file if upload fails
                }
            }
            
            \Log::info('Creating application with data:', [
                'employee_id' => $id,
                'req_type' => $type,
                'subject' => $subject,
                'description' => $description,
                'start_date' => $start_date->format('Y-m-d H:i:s'),
                'end_date' => $end_date->format('Y-m-d H:i:s'),
                'file' => $filePath
            ]);

            $application = Application::create([
                'employee_id' => $id,
                'req_type' => $type,
                'subject' => $subject,
                'description' => $description,
                'start_date' => $start_date->format('Y-m-d H:i:s'),
                'end_date' => $end_date->format('Y-m-d H:i:s'),
                'file' => $filePath,
                'status' => 'pending'
            ]);

            if ($application) {
                \Log::info('Application created successfully with ID: ' . $application->id);
                $this->createNotificationsForAdmins($application->id, $id);
                return response('100');
            }

            \Log::error('Application creation failed');
            return response('Application creation failed', 400);
            
        } catch (\Exception $e) {
            \Log::error('Casual leave application error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response('Error: ' . $e->getMessage(), 500);
        }
    }

    public function modal(Request $request)
    {
        $id = $request->input('id');
        $type = $request->input('type');
        $time = $request->input('time');
        
        // Debug logging
        \Log::info('Modal request data:', [
            'id' => $id,
            'type' => $type, 
            'time' => $time
        ]);

        // Try different query approaches
        $result = Application::select('applications.*', 'employees.username as name')
            ->leftJoin('employees', 'employees.emp_id', '=', 'applications.employee_id')
            ->where('employee_id', $id)
            ->where('req_type', $type)
            ->orderBy('applications.created_at', 'desc')
            ->first();

        if ($result) {
            $response = [
                "id" => $result->id,
                "emp_id" => $result->employee_id,
                "name" => $result->name,
                "req_type" => $result->req_type,
                "subject" => $result->subject,
                "description" => $result->description,
                "halfday" => $result->half_day ?? "-",
                "startdate" => $result->start_date ?? "-",
                "enddate" => $result->end_date ?? "-",
                "file" => $result->file ?? "-",
                "status" => $result->status ?? "-",
                "createdat" => $result->created_at ? Carbon::parse($result->created_at)->setTimezone('Asia/Kolkata')->format('Y-m-d H:i:s') : "-"
            ];

            if ($result->req_type === "punch_Out_regularization") {
                $response['enddate'] = $result->end_date ? Carbon::parse($result->end_date)->setTimezone('Asia/Kolkata')->format('H:i') : "-";
            }

            return response()->json($response);
        }

        return response()->json(['error' => 'Not found'], 404);
    }

    public function leaveCheck(Request $request)
    {
        $userid = Auth::user()->emp_id;

        $systemSettings = SystemSetting::all();
        $sickLeave = $casualLeave = 0;

        foreach ($systemSettings as $row) {
            if ($row->setting_key == "sick_leave") {
                $sickLeave = $row->setting_value;
            }
            if ($row->setting_key == "casual_leave") {
                $casualLeave = $row->setting_value;
            }
        }

        $leaveCount = LeaveCount::where('employee_id', $userid)->first();

        $response = [
            "tsl" => "<label style='color:green;font-size: 20px;'>$sickLeave</label>",
            "rsl" => "<label style='color:green;font-size: 20px;'>" . ($leaveCount->sick_leave ?? 0) . "</label>",
            "tcl" => "<label style='color:orange;font-size: 20px;'>$casualLeave</label>",
            "rcl" => "<label style='color:orange;font-size: 20px;'>" . ($leaveCount->casual_leave ?? 0) . "</label>",
            "valrsl" => $leaveCount->sick_leave ?? 0,
            "valrcl" => $leaveCount->casual_leave ?? 0
        ];

        return response()->json($response);
    }

    private function handleFileUpload(Request $request)
    {
        if ($request->hasFile('image')) {
            try {
                $file = $request->file('image');
                
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                
                $extension = strtolower($file->getClientOriginalExtension());
                $mimeType = $file->getMimeType();
                
                if (!in_array($extension, $allowedExtensions) || !in_array($mimeType, $allowedMimeTypes)) {
                    throw new \Exception('Invalid file type. Only JPEG, PNG and GIF images are allowed.');
                }

                if ($file->getSize() > 2 * 1024 * 1024) {
                    throw new \Exception('File too large. Maximum size is 2MB.');
                }

                // Ensure uploads directory exists
                $uploadsPath = public_path('uploads');
                if (!file_exists($uploadsPath)) {
                    mkdir($uploadsPath, 0755, true);
                }

                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move($uploadsPath, $fileName);
                return 'uploads/' . $fileName;
            } catch (\Exception $e) {
                \Log::error('File upload error: ' . $e->getMessage());
                throw $e;
            }
        }

        return '';
    }

    private function handleSickLeave(Request $request, $userid)
    {
        try {
            \Log::info('handleSickLeave called with data:', $request->all());
            
            // Basic validation
            if (!$request->input('id')) {
                return response('Employee ID is required.', 400);
            }
            
            if (!$request->input('type')) {
                return response('Request type is required.', 400);
            }
            
            if (!$request->input('subject')) {
                return response('Subject is required.', 400);
            }
            
            if (!$request->input('start_date') || !$request->input('end_date')) {
                return response('Start and end dates are required.', 400);
            }

            $id = $request->input('id');
            $type = $request->input('type');
            $subject = $request->input('subject');
            $description = $request->input('description', '');
            $start_date = Carbon::parse($request->input('start_date'));
            $end_date = Carbon::parse($request->input('end_date'));

            if ($start_date > $end_date) {
                return response('Start date must be before end date.', 400);
            }

            // Calculate requested leave days
            $requestedDays = $start_date->diffInDays($end_date) + 1;
            
            // Check remaining sick leave
            $leaveCount = LeaveCount::where('employee_id', $userid)->first();
            $remainingLeave = $leaveCount ? $leaveCount->sick_leave : 0;
            
            if ($remainingLeave < $requestedDays) {
                return response("Insufficient sick leave balance! Available: {$remainingLeave} days, Requested: {$requestedDays} days", 400);
            }

            // Handle file upload
            $filePath = '';
            if ($request->hasFile('image')) {
                try {
                    $filePath = $this->handleFileUpload($request);
                } catch (\Exception $e) {
                    \Log::error('File upload error: ' . $e->getMessage());
                    // Continue without file if upload fails
                }
            }
            
            \Log::info('Creating sick leave application with data:', [
                'employee_id' => $id,
                'req_type' => $type,
                'subject' => $subject,
                'description' => $description,
                'start_date' => $start_date->format('Y-m-d H:i:s'),
                'end_date' => $end_date->format('Y-m-d H:i:s'),
                'file' => $filePath
            ]);

            $application = Application::create([
                'employee_id' => $id,
                'req_type' => $type,
                'subject' => $subject,
                'description' => $description,
                'start_date' => $start_date->format('Y-m-d H:i:s'),
                'end_date' => $end_date->format('Y-m-d H:i:s'),
                'file' => $filePath,
                'status' => 'pending'
            ]);

            if ($application) {
                \Log::info('Sick leave application created successfully with ID: ' . $application->id);
                $this->createNotificationsForAdmins($application->id, $id);
                return response('100');
            }

            \Log::error('Sick leave application creation failed');
            return response('Application creation failed', 400);
            
        } catch (\Exception $e) {
            \Log::error('Sick leave application error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response('Error: ' . $e->getMessage(), 500);
        }
    }

    private function handleHalfDay(Request $request, $userid)
    {
        try {
            $id = $request->input('id');
            $type = $request->input('type');
            $start_date = Carbon::parse($request->input('start_date'))->setTimezone('Asia/Kolkata');
            $subject = $request->input('subject');
            $description = $request->input('description');
            $half_day_type = $request->input('half_day_type', 'first_half');

            $filePath = $this->handleFileUpload($request);

            $application = Application::create([
                'employee_id' => $id,
                'req_type' => $type,
                'subject' => $subject,
                'description' => $description,
                'start_date' => $start_date->format('Y-m-d H:i:s'),
                'half_day' => $half_day_type,
                'file' => $filePath,
                'status' => 'pending'
            ]);

            if ($application) {
                $this->createNotificationsForAdmins($application->id, $id);
                return response('100');
            }

            return response('Not uploaded', 400);
        } catch (\Exception $e) {
            \Log::error('Half day application error: ' . $e->getMessage());
            return response('Error: ' . $e->getMessage(), 500);
        }
    }

    private function handleComplaintOrOther(Request $request, $userid)
    {
        $id = $request->input('id');
        $type = $request->input('type');
        $start_date = Carbon::parse($request->input('start_date'))->setTimezone('Asia/Kolkata');
        $subject = $request->input('subject');
        $description = $request->input('description');

        $filePath = $this->handleFileUpload($request);

        $application = Application::create([
            'employee_id' => $id,
            'req_type' => $type,
            'subject' => $subject,
            'description' => $description,
            'start_date' => $start_date->format('Y-m-d H:i:s'),
            'file' => $filePath
        ]);

        if ($application) {
            $this->createNotificationsForAdmins($application->id, $id);
            return response('100');
        }

        return response('Not uploaded', 400);
    }

    private function handleRegularization(Request $request, $userid)
    {
        $id = $request->input('id');
        $type = $request->input('type');
        $start_date = Carbon::parse($request->input('start_date'))->setTimezone('Asia/Kolkata');
        $end_date = Carbon::parse($request->input('end_date'))->setTimezone('Asia/Kolkata');
        $subject = $request->input('subject');
        $description = $request->input('description');

        $filePath = $this->handleFileUpload($request);

        $application = Application::create([
            'employee_id' => $id,
            'req_type' => $type,
            'subject' => $subject,
            'description' => $description,
            'start_date' => $start_date->format('Y-m-d H:i:s'),
            'end_date' => $end_date->format('Y-m-d H:i:s'),
            'file' => $filePath
        ]);

        if ($application) {
            $this->createNotificationsForAdmins($application->id, $id);
            return response('100');
        }

        return response('Not uploaded', 400);
    }

    private function handlePunchOutRegularization(Request $request, $userid)
    {
        $id = $request->input('id');
        $type = $request->input('type');
        $start_date = Carbon::parse($request->input('start_date'))->setTimezone('Asia/Kolkata');
        $end_date = $request->input('end_date');
        $subject = $request->input('subject');
        $description = $request->input('description');

        $filePath = $this->handleFileUpload($request);

        $application = Application::create([
            'employee_id' => $id,
            'req_type' => $type,
            'subject' => $subject,
            'description' => $description,
            'start_date' => $start_date->format('Y-m-d H:i:s'),
            'end_date' => $end_date,
            'file' => $filePath
        ]);

        if ($application) {
            $this->createNotificationsForAdmins($application->id, $id);
            return response('100');
        }

        return response('Not uploaded', 400);
    }

    private function handleWorkFromHome(Request $request, $userid)
    {
        $id = $request->input('id');
        $type = $request->input('type');
        $start_date = Carbon::parse($request->input('start_date'))->setTimezone('Asia/Kolkata');
        $end_date = Carbon::parse($request->input('end_date'))->setTimezone('Asia/Kolkata');
        $subject = $request->input('subject');
        $description = $request->input('description');

        $filePath = $this->handleFileUpload($request);

        $application = Application::create([
            'employee_id' => $id,
            'req_type' => $type,
            'subject' => $subject,
            'description' => $description,
            'start_date' => $start_date->format('Y-m-d H:i:s'),
            'end_date' => $end_date->format('Y-m-d H:i:s'),
            'file' => $filePath
        ]);

        if ($application) {
            $this->createNotificationsForAdmins($application->id, $id);
            return response('100');
        }

        return response('Not uploaded', 400);
    }

    private function createNotificationsForAdmins($applicationId, $createdBy)
    {
        $admins = Employee::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            Notification::create([
                'App_id' => $applicationId,
                'created_by' => $createdBy,
                'notify_to' => $admin->emp_id
            ]);
        }
    }
}