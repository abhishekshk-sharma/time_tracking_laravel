<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LunchAlarm extends Model
{
    protected $fillable = [
        'employee_id',
        'lunch_start_time',
        'alarm_time',
        'is_active'
    ];

    protected $casts = [
        'lunch_start_time' => 'datetime',
        'alarm_time' => 'datetime',
        'is_active' => 'boolean'
    ];
}