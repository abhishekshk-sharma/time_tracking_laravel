<?php





namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\LunchAlarm;
use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Exception\FirebaseException;

class CheckLunchAlarms extends Command
{
    protected $signature = 'lunch:check-alarms';
    protected $description = 'Check and trigger lunch alarms';

    public function handle()
    {
        $now = Carbon::now();

        $activeAlarms = LunchAlarm::with('employee')
            ->where('is_active', true)
            ->where('alarm_time', '<=', $now)
            ->get();

        $this->info("Found {$activeAlarms->count()} active alarms to process");

        foreach ($activeAlarms as $alarm) {
            try {
                // Mark alarm as triggered immediately
                $alarm->update(['is_active' => false]);

                // Get employee with device token
                $employee = $alarm->employee;
                
                if (!$employee) {
                    $this->warn("Employee not found for alarm ID: {$alarm->id}");
                    continue;
                }

                if (empty($employee->device_token)) {
                    $this->warn("No device token for employee: {$employee->id} - {$employee->name}");
                    continue;
                }

                // Send notification
                $result = $this->sendLunchNotification($employee, $alarm);
                
                if ($result) {
                    $this->info("✓ Notification sent to employee {$employee->id} for alarm {$alarm->id}");
                }

            } catch (\Exception $e) {
                $this->error("Failed to process alarm {$alarm->id}: " . $e->getMessage());
                // Optionally reactivate the alarm if you want to retry
                // $alarm->update(['is_active' => true]);
            }
        }

        return 0;
    }

    private function sendLunchNotification($employee, $alarm)
    {
        try {
            $messaging = app('firebase.messaging');
            
            $title = '🍽️ Lunch Reminder';
            $body = $alarm->message ?? 'Your lunch break is about to end in 5 minutes!';
            
            // Create notification
            $notification = Notification::create($title, $body);
            
            // Android specific configuration
            $androidConfig = AndroidConfig::fromArray([
                'priority' => 'high',
                'notification' => [
                    'sound' => 'default',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'channel_id' => 'lunch_reminders'
                ]
            ]);
            
            // iOS specific configuration  
            $apnsConfig = ApnsConfig::fromArray([
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                        'badge' => 1
                    ]
                ]
            ]);

            // Build message
            $message = CloudMessage::withTarget('token', $employee->device_token)
                ->withNotification($notification)
                ->withData([
                    'alarm_id' => (string) $alarm->id,
                    'type' => 'lunch_reminder',
                    'employee_id' => (string) $employee->id,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'sound' => 'default',
                    'priority' => 'high'
                ])
                ->withAndroidConfig($androidConfig)
                ->withApnsConfig($apnsConfig);

            // Send the message
            $messaging->send($message);
            
            return true;
            
        } catch (MessagingException $e) {
            $this->error("Firebase Messaging Error: " . $e->getMessage());
            
            // Check if token is invalid
            if (strpos($e->getMessage(), 'InvalidRegistration') !== false || 
                strpos($e->getMessage(), 'NotRegistered') !== false) {
                // Clear invalid token
                $employee->update(['device_token' => null]);
                $this->warn("Removed invalid device token for employee {$employee->id}");
            }
            return false;
            
        } catch (FirebaseException $e) {
            $this->error("Firebase Error: " . $e->getMessage());
            return false;
        }
    }
}




// namespace App\Console\Commands;

// use Illuminate\Console\Command;
// use Carbon\Carbon;
// use App\Models\LunchAlarm;
// use Kreait\Firebase\Messaging\CloudMessage;
// use Kreait\Firebase\Messaging\Notification;

// class CheckLunchAlarms extends Command
// {
//     protected $signature = 'lunch:check-alarms';
//     protected $description = 'Check and trigger lunch alarms';

//     public function handle()
//     {
//         $now = Carbon::now();

//         $activeAlarms = LunchAlarm::where('is_active', true)
//             ->where('alarm_time', '<=', $now)
//             ->get();

//         $messaging = app('firebase.messaging'); // Kreait binding

//         foreach ($activeAlarms as $alarm) {
//             // Mark alarm as triggered
//             $alarm->update(['is_active' => false]);

//             // Get employee device token (must be stored in DB)
//             $employee = $alarm->employee; // assuming relation LunchAlarm -> employee
//             if ($employee && $employee->device_token) {
//                 $title = 'Lunch Reminder';
//                 $body  = $alarm->message ?? 'Your lunch break ends in 5 minutes!';

//                 $message = CloudMessage::withTarget('token', $employee->device_token)
//                     ->withNotification(Notification::create($title, $body));

//                 $messaging->send($message);
//                 $this->info("Notification sent to employee {$alarm->employee_id} with message: {$body}");
//             } else {
//                 $this->warn("No device token for employee: {$alarm->employee_id}");
//             }
//         }

//         return 0;
//     }
// }


// namespace App\Console\Commands;

// use Illuminate\Console\Command;
// use App\Models\LunchAlarm;
// use Carbon\Carbon;

// class CheckLunchAlarms extends Command
// {
//     protected $signature = 'lunch:check-alarms';
//     protected $description = 'Check and trigger lunch alarms';

//     public function handle()
//     {
//         $now = Carbon::now();
        
//         $activeAlarms = LunchAlarm::where('is_active', true)
//             ->where('alarm_time', '<=', $now)
//             ->get();

//         foreach ($activeAlarms as $alarm) {
//             // Mark alarm as triggered
//             $alarm->update(['is_active' => false]);
            
//             $this->info("Lunch alarm triggered for employee: {$alarm->employee_id}");
//         }

//         return 0;
//     }
// }