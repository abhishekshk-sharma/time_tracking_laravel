<?php
// app/Services/FCMService.php

namespace App\Services;

use App\Models\employee;
use App\Models\LunchAlarm;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FCMService
{
    protected $messaging;

    public function __construct()
    {
        try {
            $this->messaging = app('firebase.messaging');
        } catch (\Exception $e) {
            Log::error('FCM Service initialization failed: ' . $e->getMessage());
        }
    }

    public function sendLunchReminder(employee $employee, Carbon $lunchStartTime)
    {
        if (!$employee->device_token) {
            Log::warning("No device token for employee: {$employee->id}");
            return false;
        }

        try {
            // Calculate lunch end time
            $lunchDuration = $this->getLunchDuration();
            $lunchEndTime = $lunchStartTime->copy()->addMinutes($lunchDuration);
            
            // Create notification
            $title = '🍽️ Lunch Break Ending Soon';
            $body = sprintf(
                'Your lunch break ends at %s. Please return to work.',
                $lunchEndTime->format('h:i A')
            );

            $notification = Notification::create($title, $body);

            // Android configuration
            $androidConfig = AndroidConfig::fromArray([
                'priority' => 'high',
                'notification' => [
                    'sound' => 'default',
                    'click_action' => 'LUNCH_REMINDER',
                    'channel_id' => 'lunch_reminders',
                    'color' => '#FF9800',
                    'tag' => 'lunch_' . $employee->id
                ]
            ]);

            // iOS configuration
            $apnsConfig = ApnsConfig::fromArray([
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                        'badge' => 1,
                        'category' => 'LUNCH_REMINDER'
                    ]
                ]
            ]);

            // Build message
            $message = CloudMessage::withTarget('token', $employee->device_token)
                ->withNotification($notification)
                ->withData([
                    'type' => 'lunch_reminder',
                    'employee_id' => (string) $employee->id,
                    'employee_name' => $employee->name,
                    'lunch_start_time' => $lunchStartTime->toDateTimeString(),
                    'lunch_end_time' => $lunchEndTime->toDateTimeString(),
                    'timestamp' => Carbon::now()->toDateTimeString(),
                    'click_action' => 'LUNCH_REMINDER'
                ])
                ->withAndroidConfig($androidConfig)
                ->withApnsConfig($apnsConfig);

            // Send notification
            $this->messaging->send($message);

            // Log the notification
            LunchAlarm::create([
                'employee_id' => $employee->id,
                'lunch_start_time' => $lunchStartTime,
                'alarm_time' => Carbon::now(),
                'is_active' => false,
                'message' => $body,
                'notification_sent_at' => Carbon::now()
            ]);

            Log::info("Lunch reminder sent to employee {$employee->id} - {$employee->name}");
            return true;

        } catch (\Kreait\Firebase\Exception\Messaging\InvalidMessage $e) {
            Log::error("Invalid FCM message for employee {$employee->id}: " . $e->getMessage());
            
            if (strpos($e->getMessage(), 'InvalidRegistration') !== false || 
                strpos($e->getMessage(), 'NotRegistered') !== false) {
                $employee->device_token = null;
                $employee->save();
                Log::info("Removed invalid device token for employee {$employee->id}");
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error("FCM send error for employee {$employee->id}: " . $e->getMessage());
            return false;
        }
    }

    public function sendTestNotification(employee $employee)
    {
        if (!$employee->device_token) {
            return false;
        }

        try {
            $notification = Notification::create(
                '🧪 Test Notification',
                'This is a test message from your lunch alarm system'
            );

            $message = CloudMessage::withTarget('token', $employee->device_token)
                ->withNotification($notification)
                ->withData([
                    'type' => 'test',
                    'employee_id' => (string) $employee->id,
                    'timestamp' => Carbon::now()->toDateTimeString()
                ]);

            $this->messaging->send($message);
            
            Log::info("Test notification sent to employee {$employee->id}");
            return true;
            
        } catch (\Exception $e) {
            Log::error("Test notification failed for employee {$employee->id}: " . $e->getMessage());
            return false;
        }
    }

    protected function getLunchDuration()
    {
        try {
            $duration = \App\Models\SystemSetting::where('setting_key', 'lunch_duration')
                        ->value('setting_value');
            return $duration ? (int) $duration : 60;
        } catch (\Exception $e) {
            return 60;
        }
    }
}