<?php

namespace App\Erp\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NumberSequence extends Model
{
    protected $table = 'erp_number_sequences';

    use HasFactory;

    protected $fillable = [
        'code', 'name', 'prefix', 'number_length', 'current_number',
        'year_format', 'year_reset', 'month_reset', 'active',
        'last_reset_year', 'last_reset_month',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'year_reset' => 'boolean',
        'month_reset' => 'boolean',
        'active' => 'boolean',
        'last_reset_year' => 'integer',
        'last_reset_month' => 'integer',
    ];
}
