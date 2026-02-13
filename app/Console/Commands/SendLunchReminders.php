<?php
// app/Console/Commands/SendLunchReminders.php

namespace App\Console\Commands;

use App\Http\Controllers\LunchAlarmController;
use Illuminate\Console\Command;

class SendLunchReminders extends Command
{
    protected $signature = 'lunch:send-reminders';
    protected $description = 'Send web push notifications for lunch break reminders';

    protected $lunchAlarmController;

    public function __construct(LunchAlarmController $lunchAlarmController)
    {
        parent::__construct();
        $this->lunchAlarmController = $lunchAlarmController;
    }

    public function handle()
    {
        $this->info('[' . now() . '] Sending lunch reminders...');

        try {
            $response = $this->lunchAlarmController->sendLunchReminders();
            $data = $response->getData();

            if ($data->success) {
                $this->info("✓ Sent {$data->notifications_sent} lunch reminders");
                if ($data->notifications_sent > 0) {
                    $this->info("✓ Employees: " . implode(', ', $data->employees));
                }
            } else {
                $this->error('✗ Failed to send lunch reminders');
            }

        } catch (\Exception $e) {
            $this->error('✗ Error: ' . $e->getMessage());
        }

        $this->info('[' . now() . '] Completed');
        
        return Command::SUCCESS;
    }
}