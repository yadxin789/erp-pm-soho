<?php

namespace App\Erp\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturnLine extends Model
{
    protected $table = 'erp_sales_return_lines';

    use HasFactory;

    protected $fillable = [
        'sales_return_id', 'line_number', 'item_id', 'quantity', 'sales_order_line_id',
        'batch_number', 'remark',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class);
    }

    public function item()
    {
        return $this->belongsTo(\App\Erp\Models\MasterData\Item::class);
    }

    public function salesOrderLine()
    {
        return $this->belongsTo(SalesOrderLine::class);
    }
}
