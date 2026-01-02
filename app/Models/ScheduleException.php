<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleException extends Model
{
    protected $fillable = [
        'exception_date',
        'type',
        'description',
        'admin_id',
        'superadmin_id'
    ];

    protected $casts = [
        'exception_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function admin()
    {
        return $this->belongsTo(Employee::class, 'admin_id', 'emp_id');
    }

    public function superAdmin()
    {
        return $this->belongsTo(SuperAdmin::class, 'superadmin_id');
    }
}