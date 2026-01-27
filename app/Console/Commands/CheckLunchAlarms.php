<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LunchAlarm;
use Carbon\Carbon;

class CheckLunchAlarms extends Command
{
    protected $signature = 'lunch:check-alarms';
    protected $description = 'Check and trigger lunch alarms';

    public function handle()
    {
        $now = Carbon::now();
        
        $activeAlarms = LunchAlarm::where('is_active', true)
            ->where('alarm_time', '<=', $now)
            ->get();

        foreach ($activeAlarms as $alarm) {
            // Mark alarm as triggered
            $alarm->update(['is_active' => false]);
            
            $this->info("Lunch alarm triggered for employee: {$alarm->employee_id}");
        }

        return 0;
    }
}