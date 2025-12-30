<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'emp_id',
        'basic_salary',
        'hra',
        'pf',
        'is_pf',
        'pt',
        'ta',
        'conveyance_allowance',
        'gross_salary',
        'effective_from',
        'is_active',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'hra' => 'decimal:2',
        'pf' => 'decimal:2',
        'pt' => 'decimal:2',
        'ta' => 'decimal:2',
        'conveyance_allowance' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'effective_from' => 'date',
        'is_pf' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_id', 'emp_id');
    }
}