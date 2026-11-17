<?php

namespace App\Erp\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomLine extends Model
{
    protected $table = 'erp_bom_lines';

    use HasFactory;

    protected $fillable = [
        'bom_header_id', 'line_number', 'item_id', 'quantity', 'scrap_rate',
        'net_quantity', 'unit_id', 'remark',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'scrap_rate' => 'decimal:4',
        'net_quantity' => 'decimal:4',
    ];

    public function bomHeader()
    {
        return $this->belongsTo(BomHeader::class);
    }

    public function item()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Item::class);
    }

    public function unit()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Unit::class);
    }
}
