<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'emp_id',
        'name',
        'full_name',
        'username',
        'email',
        'phone',
        'department',
        'department_id',
        'region_id',
        'position',
        'hire_date',
        'end_date',
        'dob',
        'status',
        'password_hash',
        'role',
        'address',
        'region',
        'referrance',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'end_date' => 'date',
        'dob' => 'datetime',
        'email_verified_at' => 'datetime',
        'password_hash' => 'hashed',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function getAuthPasswordName() 
    { 
        return 'password_hash'; // 👈 column name for updates 
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class, 'employee_id', 'emp_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'employee_id', 'emp_id');
    }

    public function leaveCount()
    {
        return $this->hasOne(LeaveCount::class, 'employee_id', 'emp_id');
    }

    public function wfhRequests(): HasMany
    {
        return $this->hasMany(Wfh::class, 'employee_id', 'emp_id');
    }

    public function salary()
    {
        return $this->hasOne(Salary::class, 'emp_id', 'emp_id')->where('is_active', true);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getTodayTimeEntries()
    {
        return $this->timeEntries()
            ->whereDate('entry_time', today())
            ->orderBy('entry_time')
            ->get();
    }

    public function assignedEmployees()
    {
        return $this->hasMany(Employee::class, 'referrance', 'emp_id');
    }

    public function entryImages(): HasMany
    {
        return $this->hasMany(EntryImage::class, 'emp_id', 'emp_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    // public function getEmployeedetails(){
    //     return $this->where('role' == 'employee');
    // }
    // public function getEmployeeRegion(){
    //     return $this->where(['role' == 'admin', 'emp']);
    // }
}