<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function handle(Request $request)
    {
        $click = $request->input('click');
        $userid = Auth::user()->emp_id;
        
        switch ($click) {
            case 'notification':
                return $this->getNotifications($userid);
            case 'changeNoteStatus':
                return $this->changeNotificationStatus($request, $userid);
            case 'markAllRead':
                return $this->markAllAsRead($userid);
            case 'deletenoty':
                return $this->deleteNotification($request, $userid);
            default:
                return response()->json(['error' => 'Invalid action'], 400);
        }
    }
    
    private function getNotifications($userid)
    {
        $notifications = Notification::select('notification.*', 'applications.req_type', 'applications.subject', 'employees.username')
            ->leftJoin('applications', 'applications.id', '=', 'notification.App_id')
            ->leftJoin('employees', 'employees.emp_id', '=', 'notification.created_by')
            ->where('notification.notify_to', $userid)
            ->orderBy('notification.created_at', 'desc')
            ->get();
        
        $output = "";
        $count = 0;
        
        foreach ($notifications as $notification) {
            if ($notification->status == 'pending') {
                $count++;
            }
            
            $statusClass = $notification->status == 'pending' ? 'unread' : 'read';
            $output .= '<div class="notification-content-head-div ' . $statusClass . '" data-appid="' . $notification->App_id . '">
                            <div class="notification-item">
                                <div class="notification-content">
                                    <h4>' . ($notification->subject ?? 'New Application') . '</h4>
                                    <p>Type: ' . ($notification->req_type ?? 'N/A') . '</p>
                                    <p>From: ' . ($notification->username ?? 'Unknown') . '</p>
                                    <small>' . Carbon::parse($notification->created_at)->diffForHumans() . '</small>
                                </div>
                                <button class="deletenoty" data-appid="' . $notification->App_id . '">×</button>
                            </div>
                        </div>';
        }
        
        if (empty($output)) {
            $output = '<div class="no-notifications">No notifications found</div>';
        }
        
        return response()->json([
            'output' => $output,
            'count' => $count
        ]);
    }
    
    private function changeNotificationStatus(Request $request, $userid)
    {
        $appid = $request->input('appid');
        
        Notification::where('App_id', $appid)
            ->where('notify_to', $userid)
            ->update(['status' => 'checked']);
        
        return $this->getNotifications($userid);
    }
    
    private function markAllAsRead($userid)
    {
        Notification::where('notify_to', $userid)
            ->where('status', 'pending')
            ->update(['status' => 'checked']);
        
        return $this->getNotifications($userid);
    }
    
    private function deleteNotification(Request $request, $userid)
    {
        $appid = $request->input('appid');
        
        Notification::where('App_id', $appid)
            ->where('notify_to', $userid)
            ->delete();
        
        return $this->getNotifications($userid);
    }
}