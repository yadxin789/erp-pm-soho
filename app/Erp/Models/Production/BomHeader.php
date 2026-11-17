<?php

namespace App\Erp\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BomHeader extends Model
{
    protected $table = 'erp_bom_headers';

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'item_id', 'version', 'effective_date', 'expiry_date', 'quantity',
        'status', 'description', 'created_by', 'approved_by', 'approved_at', 'updated_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function item()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Item::class);
    }

    public function lines()
    {
        return $this->hasMany(BomLine::class);
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
