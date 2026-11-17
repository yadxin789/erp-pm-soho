<?php

namespace App\Erp\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stocktake extends Model
{
    protected $table = 'erp_stocktakes';

    use HasFactory;

    protected $fillable = [
        'stocktake_no', 'warehouse_id', 'stocktake_date', 'status', 'remark',
        'created_by', 'approved_by', 'approved_at',
    ];

    public function warehouse()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Warehouse::class);
    }

    public function lines()
    {
        return $this->hasMany(StocktakeLine::class);
    }
}
