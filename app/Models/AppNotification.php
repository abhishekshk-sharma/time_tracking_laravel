<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    protected $table = 'notification';
    
    protected $fillable = [
        'App_id',
        'created_by', 
        'notify_to',
        'status'
    ];

    public function application()
    {
        return $this->belongsTo(Application::class, 'App_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by', 'emp_id');
    }

    public function notifyTo()
    {
        return $this->belongsTo(Employee::class, 'notify_to', 'emp_id');
    }
}