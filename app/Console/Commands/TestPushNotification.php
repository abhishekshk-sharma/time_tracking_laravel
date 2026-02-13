<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Notifications\LunchReminderFCM;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestPushNotification extends Command
{
    protected $signature = 'push:test {empId}';
    protected $description = 'Send a test push notification to an employee';

    public function handle()
    {
        $empId = $this->argument('empId');
        
        $employee = Employee::where('emp_id', $empId)->first();
        
        if (!$employee) {
            $this->error("Employee not found: {$empId}");
            return Command::FAILURE;
        }
        
        $subscriptionCount = $employee->pushSubscriptions()->count();
        $this->info("Employee: {$employee->full_name}");
        $this->info("Push Subscriptions: {$subscriptionCount}");
        
        if ($subscriptionCount === 0) {
            $this->error("Employee has no push subscriptions. Please enable notifications first.");
            return Command::FAILURE;
        }
        
        try {
            $lunchEndTime = Carbon::now()->addMinutes(5);
            
            $employee->notify(new LunchReminderFCM(
                $lunchEndTime,
                $employee->full_name ?? $employee->username ?? $employee->emp_id
            ));
            
            $this->info("✓ Test notification sent successfully!");
            $this->info("Check your browser for the notification.");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Failed to send notification: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
