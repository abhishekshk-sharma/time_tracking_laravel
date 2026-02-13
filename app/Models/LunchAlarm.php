<?php
// app/Models/LunchAlarm.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LunchAlarm extends Model
{
    protected $fillable = [
        'employee_id',
        'lunch_start_time',
        'alarm_time',
        'is_active',
        'message',
        'notification_sent_at'
    ];

    protected $casts = [
        'lunch_start_time' => 'datetime',
        'alarm_time' => 'datetime',
        'is_active' => 'boolean',
        'notification_sent_at' => 'datetime'
    ];

    public function employee()
    {
        return $this->belongsTo(employee::class, 'emp_id');
    }
}