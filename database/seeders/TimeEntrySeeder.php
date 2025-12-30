<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TimeEntry;
use App\Models\Employee;
use Carbon\Carbon;

class TimeEntrySeeder extends Seeder
{
    public function run()
    {
        $employees = Employee::where('role', 'employee')->get();
        
        foreach ($employees as $employee) {
            // Create entries for the last 30 days
            for ($i = 0; $i < 30; $i++) {
                $date = Carbon::now()->subDays($i);
                
                // Skip weekends
                if ($date->isWeekend()) {
                    continue;
                }
                
                // 80% chance of having entries
                if (rand(1, 100) <= 80) {
                    $punchIn = $date->copy()->setTime(9, rand(0, 30), 0);
                    $lunchStart = $punchIn->copy()->addHours(4)->addMinutes(rand(0, 60));
                    $lunchEnd = $lunchStart->copy()->addMinutes(rand(30, 90));
                    $punchOut = $lunchEnd->copy()->addHours(4)->addMinutes(rand(0, 60));
                    
                    TimeEntry::create([
                        'employee_id' => $employee->emp_id,
                        'entry_type' => 'punch_in',
                        'entry_time' => $punchIn,
                        'notes' => 'punch_in'
                    ]);
                    
                    TimeEntry::create([
                        'employee_id' => $employee->emp_id,
                        'entry_type' => 'lunch_start',
                        'entry_time' => $lunchStart,
                        'notes' => 'lunch_start'
                    ]);
                    
                    TimeEntry::create([
                        'employee_id' => $employee->emp_id,
                        'entry_type' => 'lunch_end',
                        'entry_time' => $lunchEnd,
                        'notes' => 'lunch_end'
                    ]);
                    
                    TimeEntry::create([
                        'employee_id' => $employee->emp_id,
                        'entry_type' => 'punch_out',
                        'entry_time' => $punchOut,
                        'notes' => 'punch_out'
                    ]);
                }
            }
        }
    }
}