<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use App\Soho\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformOrder extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use LogsActivity, BelongsToTenant;
    protected $table = 'soho_platform_orders';

    protected $fillable = [
        'tenant_id', 'platform_order_no', 'system_order_no', 'platform', 'shop_id',
        'order_status', 'pay_status', 'total_amount', 'pay_amount',
        'discount_amount', 'freight_amount', 'currency',
        'buyer_nick', 'buyer_message', 'receiver_name', 'receiver_phone',
        'receiver_province', 'receiver_city', 'receiver_district',
        'receiver_address', 'receiver_zip',
        'order_created_at', 'pay_time', 'ship_time', 'completed_time',
        'operator_remark', 'is_abnormal', 'abnormal_reason',
        'order_detail', 'platform_raw_data',
    ];

    protected function casts(): array
    {
        return [
            'total_amount'      => 'decimal:2',
            'pay_amount'        => 'decimal:2',
            'discount_amount'   => 'decimal:2',
            'freight_amount'    => 'decimal:2',
            'order_created_at'  => 'datetime',
            'pay_time'          => 'datetime',
            'ship_time'         => 'datetime',
            'completed_time'    => 'datetime',
            'is_abnormal'       => 'boolean',
            'order_detail'      => 'array',
            'platform_raw_data' => 'array',
        ];
    }

    /**
     * 获取所属租户
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * 获取所属店铺
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * 获取订单明细列表
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * 获取订单包裹列表
     */
    public function packages(): HasMany
    {
        return $this->hasMany(OrderPackage::class);
    }

    /**
     * 获取订单状态日志
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class);
    }

    /**
     * 获取发货记录
     */
    public function shipRecords(): HasMany
    {
        return $this->hasMany(SystemShipRecord::class);
    }

    /**
     * 获取售后记录
     */
    public function afterSales(): HasMany
    {
        return $this->hasMany(OrderAfterSale::class);
    }

    public function getPlatformLabelAttribute(): string
    {
        return match($this->platform) {
            'taobao'    => '淘宝',
            'jd'        => '京东',
            'pinduoduo' => '拼多多',
            'amazon'    => '亚马逊',
            default     => $this->platform,
        };
    }

    public function getOrderStatusLabelAttribute(): string
    {
        return match($this->order_status) {
            'pending'   => '待付款',
            'paid'      => '已付款',
            'shipped'   => '已发货',
            'completed' => '已完成',
            'cancelled' => '已取消',
            'frozen'    => '已冻结',
            default     => $this->order_status,
        };
    }

    /**
     * 访问器：订单号（优先系统订单号，回退平台订单号）
     */
    public function getOrderNumberAttribute(): string
    {
        return $this->system_order_no ?? $this->platform_order_no;
    }

    /**
     * 访问器：订单状态（映射 order_status）
     */
    public function getStatusAttribute(): string
    {
        return $this->order_status;
    }

    /**
     * 访问器：买家昵称（映射 buyer_nick）
     */
    public function getBuyerNicknameAttribute(): ?string
    {
        return $this->buyer_nick;
    }

    /**
     * 访问器：发货时间（映射 ship_time）
     */
    public function getShippedAtAttribute(): ?string
    {
        return $this->ship_time?->format('Y-m-d H:i:s');
    }
}
