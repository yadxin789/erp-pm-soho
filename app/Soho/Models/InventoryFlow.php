<?php
namespace App\Soho\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryFlow extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory;
    
    protected $fillable = [
        'flow_no', 'type', 'warehouse_id', 'sku_id', 'quantity',
        'before_quantity', 'after_quantity', 'reference_no', 'operator_id', 'remark'
    ];
    
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function sku() { return $this->belongsTo(ProductSku::class, 'sku_id'); }
    public function operator() { return $this->belongsTo(User::class, 'operator_id'); }
}
