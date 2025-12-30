<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'entry_type',
        'entry_time',
        'notes',
    ];

    protected $casts = [
        'entry_time' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'emp_id');
    }

    public $timestamps = false;

    public function scopeToday($query)
    {
        return $query->whereDate('entry_time', today());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('entry_type', $type);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public static function getLastEntryForEmployee($employeeId, $date = null)
    {
        $date = $date ?? today();
        
        return static::where('employee_id', $employeeId)
            ->whereDate('entry_time', $date)
            ->orderBy('entry_time', 'desc')
            ->first();
    }

    public static function calculateWorkingHours($employeeId, $date = null)
    {
        $date = $date ?? today();
        
        $entries = static::where('employee_id', $employeeId)
            ->whereDate('entry_time', $date)
            ->orderBy('entry_time')
            ->get();

        $totalMinutes = 0;
        $lunchMinutes = 0;
        $punchIn = null;
        $lunchStart = null;

        foreach ($entries as $entry) {
            switch ($entry->entry_type) {
                case 'punch_in':
                    $punchIn = $entry->entry_time;
                    break;
                case 'punch_out':
                    if ($punchIn) {
                        $totalMinutes += $punchIn->diffInMinutes($entry->entry_time);
                        $punchIn = null;
                    }
                    break;
                case 'lunch_start':
                    $lunchStart = $entry->entry_time;
                    break;
                case 'lunch_end':
                    if ($lunchStart) {
                        $lunchMinutes += $lunchStart->diffInMinutes($entry->entry_time);
                        $lunchStart = null;
                    }
                    break;
            }
        }

        return [
            'total_minutes' => $totalMinutes,
            'lunch_minutes' => $lunchMinutes,
            'work_minutes' => $totalMinutes - $lunchMinutes,
        ];
    }
}