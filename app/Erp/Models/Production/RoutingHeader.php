<?php

namespace App\Erp\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoutingHeader extends Model
{
    protected $table = 'erp_routing_headers';

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'item_id', 'version', 'effective_date', 'expiry_date', 'status',
        'description', 'created_by', 'approved_by', 'approved_at', 'updated_by',
    ];

    public function item()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Item::class);
    }

    public function operations()
    {
        return $this->hasMany(RoutingOperation::class);
    }

    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class);
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }
}
