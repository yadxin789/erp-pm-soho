<?php

namespace App\Erp\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialReturnLine extends Model
{
    protected $table = 'erp_material_return_lines';

    use HasFactory;

    protected $fillable = [
        'material_return_id', 'line_number', 'item_id', 'quantity', 'batch_number', 'remark',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function materialReturn()
    {
        return $this->belongsTo(MaterialReturn::class);
    }

    public function item()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Item::class);
    }
}
