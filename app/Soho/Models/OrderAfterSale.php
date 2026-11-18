<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAfterSale extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use BelongsToTenant;
    protected $table = 'soho_order_after_sales';

    protected $fillable = [
        'platform_order_id', 'platform', 'shop_id', 'after_sale_no',
        'type', 'status', 'refund_amount', 'reason', 'buyer_nick',
        'description', 'apply_time', 'handle_time', 'handle_result',
        'platform_raw_data',
    ];

    protected function casts(): array
    {
        return [
            'refund_amount'     => 'decimal:2',
            'apply_time'        => 'datetime',
            'handle_time'       => 'datetime',
            'platform_raw_data' => 'array',
        ];
    }

    public function platformOrder(): BelongsTo
    {
        return $this->belongsTo(PlatformOrder::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'refund'  => '退款',
            'return'  => '退货',
            'exchange'=> '换货',
            default   => $this->type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'    => '待处理',
            'processing' => '处理中',
            'completed'  => '已完成',
            'rejected'   => '已拒绝',
            default      => $this->status,
        };
    }
}
