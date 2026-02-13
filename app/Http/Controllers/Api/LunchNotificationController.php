<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TimeEntry;
use App\Models\SystemSetting;
use App\Models\LunchAlarm;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LunchNotificationController extends Controller
{
    public function checkLunchAlarm($empId)
    {
        try {
            $now = Carbon::now();
            
            $lunchStart = TimeEntry::where('employee_id', $empId)
                ->where('entry_type', 'lunch_start')
                ->whereDate('entry_time', $now->toDateString())
                ->orderBy('entry_time', 'desc')
                ->first();
                
            if (!$lunchStart) {
                return response()->json(['should_notify' => false]);
            }
            
            $lunchEnd = TimeEntry::where('employee_id', $empId)
                ->where('entry_type', 'lunch_end')
                ->where('entry_time', '>', $lunchStart->entry_time)
                ->first();
                
            if ($lunchEnd) {
                return response()->json(['should_notify' => false]);
            }
            
            $lunchDuration = (int) (SystemSetting::where('setting_key', 'lunch_duration')
                            ->value('setting_value') ?? 60);
            
            $lunchEndTime = Carbon::parse($lunchStart->entry_time)
                           ->addMinutes($lunchDuration);
            
            $reminderMinutes = min(5, max(0, $lunchDuration - 1));
            $reminderTime = $lunchEndTime->copy()->subMinutes($reminderMinutes);
            
            // Check if we're in the notification window (from reminder time until lunch ends)
            $shouldNotify = $now->greaterThanOrEqualTo($reminderTime) && $now->lessThan($lunchEndTime);
            
            if ($shouldNotify) {
                // Check if already notified
                $alreadyNotified = LunchAlarm::where('employee_id', $empId)
                    ->whereDate('created_at', $now->toDateString())
                    ->exists();
                
                if ($alreadyNotified) {
                    return response()->json(['should_notify' => false]);
                }
                
                // Mark as notified
                LunchAlarm::create([
                    'employee_id' => $empId,
                    'lunch_start_time' => $lunchStart->entry_time,
                    'alarm_time' => $now,
                    'is_active' => false,
                    'message' => "Your lunch break ends at {$lunchEndTime->format('h:i A')}",
                    'notification_sent_at' => $now
                ]);
                
                return response()->json([
                    'should_notify' => true,
                    'message' => "Your lunch break ends at {$lunchEndTime->format('h:i A')}. Please return in {$reminderMinutes} minutes!",
                    'lunch_end_time' => $lunchEndTime->format('h:i A')
                ]);
            }
            
            return response()->json(['should_notify' => false]);
            
        } catch (\Exception $e) {
            \Log::error('Lunch notification check error: ' . $e->getMessage());
            return response()->json(['should_notify' => false]);
        }
    }
}
