<?php
// app/Http/Controllers/LunchAlarmController.php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\TimeEntry;
use App\Models\LunchAlarm;
use App\Models\SystemSetting;
use App\Notifications\LunchReminderNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LunchAlarmController extends Controller
{
    public function checkLunchAlarm(Request $request, $empId)
    {
        try {
            $now = Carbon::now();
            
            $lunchStart = TimeEntry::where('employee_id', $empId)
                ->where('entry_type', 'lunch_start')
                ->whereDate('entry_time', $now->toDateString())
                ->orderBy('entry_time', 'desc')
                ->first();
                
            if (!$lunchStart) {
                return response()->json(['alarm_active' => false]);
            }
            
            $lunchEnd = TimeEntry::where('employee_id', $empId)
                ->where('entry_type', 'lunch_end')
                ->where('entry_time', '>', $lunchStart->entry_time)
                ->first();
                
            if ($lunchEnd) {
                return response()->json(['alarm_active' => false]);
            }
            
            $lunchDuration = SystemSetting::where('setting_key', 'lunch_duration')
                            ->value('setting_value') ?? 60;
            
            $lunchEndTime = Carbon::parse($lunchStart->entry_time)
                           ->addMinutes($lunchDuration);
            
            $alarmTime = $lunchEndTime->copy()->subMinutes(5);
            $alarmEndTime = $alarmTime->copy()->addSeconds(30);
            
            $alarmActive = $now->gte($alarmTime) && $now->lt($alarmEndTime);
            
            return response()->json([
                'alarm_active' => $alarmActive,
                'lunch_end_time' => $lunchEndTime->format('h:i A'),
                'message' => 'Your lunch break ends in 5 minutes!'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Lunch alarm check error: ' . $e->getMessage());
            return response()->json(['alarm_active' => false]);
        }
    }

    /**
     * Send lunch reminders via Web Push
     */
    public function sendLunchReminders()
    {
        try {
            $now = Carbon::now();
            $notificationsSent = 0;
            $employeesNotified = [];

            // Get all employees currently on lunch break
            $employees = $this->getEmployeesOnLunchBreak();

            foreach ($employees as $employee) {
                $lunchStart = TimeEntry::where('employee_id', $employee->emp_id)
                    ->where('entry_type', 'lunch_start')
                    ->whereDate('entry_time', $now->toDateString())
                    ->orderBy('entry_time', 'desc')
                    ->first();

                if (!$lunchStart) continue;

                $lunchDuration = $this->getLunchDuration();
                $lunchEndTime = Carbon::parse($lunchStart->entry_time)
                               ->addMinutes($lunchDuration);
                
                // For short lunch durations, send notification immediately
                // For longer durations, send 5 minutes before end
                $reminderMinutes = min(5, max(0, $lunchDuration - 1));
                $reminderTime = $lunchEndTime->copy()->subMinutes($reminderMinutes);

                // Check if it's time to send reminder (within 2 minute window for flexibility)
                if ($now->gte($reminderTime) && $now->lt($reminderTime->copy()->addMinutes(2))) {
                    
                    // Check if already sent today
                    $alreadySent = LunchAlarm::where('employee_id', $employee->emp_id)
                        ->whereDate('created_at', $now->toDateString())
                        ->exists();

                    if (!$alreadySent && $employee->pushSubscriptions()->exists()) {
                        // Send web push notification
                        $employee->notify(new LunchReminderNotification(
                            $lunchEndTime,
                            $employee->full_name ?? $employee->username ?? $employee->emp_id
                        ));
                        
                        // Record the notification
                        LunchAlarm::create([
                            'employee_id' => $employee->emp_id,
                            'lunch_start_time' => $lunchStart->entry_time,
                            'alarm_time' => $now,
                            'is_active' => false,
                            'message' => "Your lunch break ends at {$lunchEndTime->format('h:i A')}",
                            'notification_sent_at' => $now
                        ]);

                        $notificationsSent++;
                        $employeesNotified[] = $employee->name;
                        
                        Log::info("Lunch reminder sent to employee: {$employee->name} (ID: {$employee->emp_id})");
                    }
                }
            }

            Log::info("Lunch reminders sent: {$notificationsSent}", [
                'employees' => $employeesNotified,
                'timestamp' => $now->toDateTimeString()
            ]);
            
            return response()->json([
                'success' => true,
                'notifications_sent' => $notificationsSent,
                'employees' => $employeesNotified
            ]);

        } catch (\Exception $e) {
            Log::error('Send lunch reminders failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send lunch reminders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get employees currently on lunch break
     */
    private function getEmployeesOnLunchBreak()
    {
        $today = Carbon::today()->toDateString();

        return Employee::whereHas('timeEntries', function ($query) use ($today) {
                $query->where('entry_type', 'lunch_start')
                      ->whereDate('entry_time', $today);
            })
            ->whereDoesntHave('timeEntries', function ($query) use ($today) {
                $lunchStartSubquery = TimeEntry::select('entry_time')
                    ->whereColumn('employee_id', 'time_entries.employee_id')
                    ->where('entry_type', 'lunch_start')
                    ->whereDate('entry_time', $today)
                    ->orderBy('entry_time', 'desc')
                    ->limit(1);

                $query->where('entry_type', 'lunch_end')
                      ->whereDate('entry_time', $today)
                      ->whereRaw('entry_time > (' . $lunchStartSubquery->toSql() . ')', $lunchStartSubquery->getBindings());
            })
            ->get();
    }

    /**
     * Get lunch duration from settings
     */
    private function getLunchDuration()
    {
        try {
            $duration = SystemSetting::where('setting_key', 'lunch_duration')
                        ->value('setting_value');
            return $duration ? (int) $duration : 60;
        } catch (\Exception $e) {
            return 60;
        }
    }
}