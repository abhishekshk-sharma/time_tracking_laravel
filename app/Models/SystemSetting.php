<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'description',
    ];

    public static function get($key, $default = null)
    {
        $setting = static::where('setting_key', $key)->first();
        return $setting ? $setting->setting_value : $default;
    }

    public static function set($key, $value, $description = null)
    {
        return static::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $value,
                'description' => $description
            ]
        );
    }

    public static function getWorkStartTime()
    {
        return static::get('work_start_time', '10:30');
    }

    public static function getWorkEndTime()
    {
        return static::get('work_end_time', '19:30');
    }

    public static function getLunchDuration()
    {
        return static::get('lunch_duration', '60');
    }

    public static function getLateThreshold()
    {
        return static::get('late_threshold', '15');
    }

    public static function getCasualLeave()
    {
        return static::get('casual_leave', '10');
    }

    public static function getSickLeave()
    {
        return static::get('sick_leave', '10');
    }

    public static function getHalfDayTime()
    {
        return static::get('half_day_time', '04:30');
    }
}