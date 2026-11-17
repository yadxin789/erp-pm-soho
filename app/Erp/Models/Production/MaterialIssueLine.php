<?php

namespace App\Erp\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialIssueLine extends Model
{
    protected $table = 'erp_material_issue_lines';

    use HasFactory;

    protected $fillable = [
        'material_issue_id', 'line_number', 'item_id', 'quantity',
        'production_order_line_id', 'batch_number', 'remark',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function materialIssue()
    {
        return $this->belongsTo(MaterialIssue::class);
    }

    public function item()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Item::class);
    }

    public function productionOrderLine()
    {
        return $this->belongsTo(ProductionOrderLine::class);
    }
}
