<?php
// app/Console/Commands/TestFCM.php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\FCMService;
use Illuminate\Console\Command;

class TestFCM extends Command
{
    protected $signature = 'fcm:test 
                            {--email= : Send test notification to specific email}
                            {--all : Send test notification to all users with device tokens}';
    
    protected $description = 'Test FCM push notifications';

    protected $fcmService;

    public function __construct(FCMService $fcmService)
    {
        parent::__construct();
        $this->fcmService = $fcmService;
    }

    public function handle()
    {
        if ($this->option('email')) {
            $user = User::where('email', $this->option('email'))->first();
            
            if (!$user) {
                $this->error("User not found with email: {$this->option('email')}");
                return Command::FAILURE;
            }

            if (!$user->device_token) {
                $this->error("User {$user->email} has no device token");
                return Command::FAILURE;
            }

            $this->info("Sending test notification to: {$user->email}");
            
            if ($this->fcmService->sendTestNotification($user)) {
                $this->info("✓ Test notification sent successfully");
            } else {
                $this->error("✗ Failed to send test notification");
            }

        } elseif ($this->option('all')) {
            $users = User::whereNotNull('device_token')->get();
            
            if ($users->isEmpty()) {
                $this->error("No users with device tokens found");
                return Command::FAILURE;
            }

            $this->info("Sending test notifications to {$users->count()} users...");
            
            $success = 0;
            $failed = 0;

            foreach ($users as $user) {
                if ($this->fcmService->sendTestNotification($user)) {
                    $success++;
                    $this->line("  ✓ {$user->email}");
                } else {
                    $failed++;
                    $this->line("  ✗ {$user->email}");
                }
            }

            $this->info("Completed: {$success} successful, {$failed} failed");

        } else {
            $this->error("Please specify --email or --all option");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}