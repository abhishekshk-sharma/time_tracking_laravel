<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'emp_id', 'emp_name', 'designation', 'department', 'admin_id', 'region_id',
        'report_date', 'month', 'year', 'total_working_days', 'present_days', 
        'absent_days', 'half_days', 'sick_leave', 'casual_leave', 'regularization', 
        'holidays', 'short_attendance', 'payable_days', 'basic_salary', 'hra', 
        'conveyance_allowance', 'pf', 'pt', 'payable_basic_salary', 'gross_salary', 
        'total_deductions', 'net_salary', 'has_negative_salary', 'has_missing_data', 
        'needs_review', 'status', 'is_released', 'bank_name', 'bank_account', 'ifsc_code', 
        'bank_branch', 'uan', 'pf_no', 'esic_no'
    ];

    protected $casts = [
        'report_date' => 'date',
        'has_negative_salary' => 'boolean',
        'has_missing_data' => 'boolean',
        'needs_review' => 'boolean',
        'is_released' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_id', 'emp_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}