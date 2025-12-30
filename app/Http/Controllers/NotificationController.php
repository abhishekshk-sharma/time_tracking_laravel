<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class NotificationController extends Controller
{
    public function handleNotification(Request $request)
    {
        $userId = Session::get('id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $click = $request->input('click');

        switch ($click) {
            case 'notification':
                return $this->getNotifications($userId);
            case 'changeNoteStatus':
                return $this->changeNotificationStatus($request, $userId);
            case 'markAllRead':
                return $this->markAllAsRead($userId);
            case 'deletenoty':
                return $this->deleteNotification($request, $userId);
            default:
                return response()->json(['error' => 'Invalid action']);
        }
    }

    private function getNotifications($userId)
    {
        $notifications = DB::table('notification as n')
            ->join('applications as a', 'n.App_id', '=', 'a.id')
            ->join('employees as e', 'n.created_by', '=', 'e.emp_id')
            ->where('n.notify_to', $userId)
            ->where('n.status', 'pending')
            ->select('n.*', 'a.subject', 'a.req_type', 'e.full_name')
            ->orderBy('n.created_at', 'desc')
            ->get();

        $output = '';
        foreach ($notifications as $notification) {
            $output .= '<div class="notification-content-head-div notification-item unread" data-appid="' . $notification->App_id . '">
                <div class="notification-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-title">' . $notification->subject . '</div>
                    <div class="notification-desc">Request Type: ' . ucfirst(str_replace('_', ' ', $notification->req_type)) . '</div>
                    <div class="notification-time">' . \Carbon\Carbon::parse($notification->created_at)->diffForHumans() . '</div>
                </div>
                <button class="deletenoty" data-appid="' . $notification->App_id . '" style="background:none;border:none;color:red;cursor:pointer;">
                    <i class="fas fa-trash"></i>
                </button>
            </div>';
        }

        if (empty($output)) {
            $output = '<div class="no-notifications">No new notifications</div>';
        }

        return response()->json([
            'output' => $output,
            'count' => $notifications->count()
        ]);
    }

    private function changeNotificationStatus(Request $request, $userId)
    {
        $appId = $request->input('appid');
        
        // Get application details
        $application = DB::table('applications as a')
            ->join('employees as e', 'a.employee_id', '=', 'e.emp_id')
            ->where('a.id', $appId)
            ->select('a.*', 'e.full_name')
            ->first();

        if (!$application) {
            return response()->json(['error' => 'Application not found']);
        }

        // Mark notification as checked
        DB::table('notification')
            ->where('App_id', $appId)
            ->where('notify_to', $userId)
            ->update(['status' => 'checked']);

        // Format application details for modal
        $output = '<div class="detail-field">
            <div class="detail-label">Employee:</div>
            <div class="detail-value">' . $application->full_name . '</div>
        </div>
        <div class="detail-field">
            <div class="detail-label">Request Type:</div>
            <div class="detail-value">' . ucfirst(str_replace('_', ' ', $application->req_type)) . '</div>
        </div>
        <div class="detail-field">
            <div class="detail-label">Subject:</div>
            <div class="detail-value">' . $application->subject . '</div>
        </div>
        <div class="detail-field">
            <div class="detail-label">Description:</div>
            <div class="detail-value">' . $application->description . '</div>
        </div>
        <div class="detail-field">
            <div class="detail-label">Date Range:</div>
            <div class="detail-value">' . \Carbon\Carbon::parse($application->start_date)->format('M d, Y') . ' - ' . 
            (\Carbon\Carbon::parse($application->end_date)->format('M d, Y') ?? 'N/A') . '</div>
        </div>
        <div class="detail-field">
            <div class="detail-label">Status:</div>
            <div class="detail-value"><span class="status-badge status-' . $application->status . '">' . ucfirst($application->status) . '</span></div>
        </div>';

        // Get updated notification count
        $count = DB::table('notification')
            ->where('notify_to', $userId)
            ->where('status', 'pending')
            ->count();

        return response()->json([
            'output' => $output,
            'count' => $count,
            'appid' => $appId
        ]);
    }

    private function markAllAsRead($userId)
    {
        DB::table('notification')
            ->where('notify_to', $userId)
            ->where('status', 'pending')
            ->update(['status' => 'checked']);

        return response()->json([
            'output' => '<div class="no-notifications">All notifications marked as read</div>',
            'count' => 0
        ]);
    }

    private function deleteNotification(Request $request, $userId)
    {
        $appId = $request->input('appid');
        
        DB::table('notification')
            ->where('App_id', $appId)
            ->where('notify_to', $userId)
            ->delete();

        // Get remaining notifications
        return $this->getNotifications($userId);
    }
}