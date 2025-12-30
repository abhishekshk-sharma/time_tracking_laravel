<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notification';
    public $timestamps = false;

    protected $fillable = [
        'App_id',
        'created_by',
        'notify_to',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class, 'App_id');
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by', 'emp_id');
    }

    public function recipient()
    {
        return $this->belongsTo(Employee::class, 'notify_to', 'emp_id');
    }
}