<?php

namespace App\Erp\Models\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    protected $table = 'erp_locations';

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'warehouse_id', 'code', 'name', 'description', 'location_type', 'active',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function inventoryBalances()
    {
        return $this->hasMany(\App\Erp\Models\Inventory\InventoryBalance::class);
    }
}
