<?php
// app/Console/Kernel.php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\SendLunchReminders::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Send lunch reminders every minute during lunch hours
        $schedule->command('lunch:send-reminders')
                 ->everyMinute()
                 ->between('11:30', '17:30')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/lunch-reminders.log'));

        // Second shift lunch hours
        $schedule->command('lunch:send-reminders')
                 ->everyMinute()
                 ->between('18:30', '21:30')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/lunch-reminders.log'));
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}