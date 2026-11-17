<?php

namespace App\Erp\Models\Crm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opportunity extends Model
{
    use SoftDeletes;

    protected $table = 'erp_crm_opportunities';

    protected $fillable = [
        'name', 'lead_id', 'customer_id', 'expected_amount',
        'probability', 'expected_close_date', 'stage', 'remark',
        'assigned_to', 'created_by',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Customer::class);
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }
}
