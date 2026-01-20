<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $table = 'taxs';
    public $timestamps = true;
    
    protected $fillable = [
        'income_from',
        'income_to', 
        'tax_rate'
    ];
    
    protected $casts = [
        'income_from' => 'integer',
        'income_to' => 'integer',
        'tax_rate' => 'decimal:2'
    ];
}