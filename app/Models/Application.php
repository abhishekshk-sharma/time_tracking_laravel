<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'req_type',
        'subject',
        'description',
        'half_day',
        'start_date',
        'end_date',
        'file',
        'status',
        'action_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public $timestamps = false;

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'emp_id');
    }

    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'action_by', 'emp_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'App_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('req_type', $type);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->req_type) {
            'leave' => 'fas fa-calendar-times',
            'sick_leave' => 'fas fa-user-injured',
            'complaint' => 'fas fa-exclamation-triangle',
            'regularization' => 'fas fa-clock',
            'half_day' => 'fas fa-calendar-day',
            default => 'fas fa-file-alt'
        };
    }
}