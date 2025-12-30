<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveCount extends Model
{
    use HasFactory;

    protected $table = 'leavecount';
    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'casual_leave',
        'sick_leave',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'emp_id');
    }
}