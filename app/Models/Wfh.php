<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wfh extends Model
{
    use HasFactory;

    protected $table = 'wfh';
    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'title',
        'description',
        'date',
        'status',
        'admin_remarks',
        'action_by',
        'action_date',
    ];

    protected $casts = [
        'date' => 'date',
        'created_at' => 'datetime',
        'action_date' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'emp_id');
    }
}