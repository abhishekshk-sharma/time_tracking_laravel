<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntryImage extends Model
{
    protected $fillable = [
        'entry_id',
        'emp_id',
        'entry_type',
        'entry_time',
        'imageFile'
    ];

    protected $casts = [
        'entry_time' => 'datetime'
    ];

    public $timestamps = false;

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'emp_id', 'emp_id');
    }
    
    public function timeEntry(): BelongsTo
    {
        return $this->belongsTo(TimeEntry::class, 'entry_id', 'id');
    }
}
