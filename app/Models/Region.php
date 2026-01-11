<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = [
        'name',
        'pin_code',
        'ip_address',
        'latitude',
        'longitude'
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'region_id', 'id');
    }

    public function scheduleExceptions()
    {
        return $this->hasMany(ScheduleException::class);
    }
}
