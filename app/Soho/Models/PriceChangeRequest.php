<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 价格变更申请模型
 * 记录商品价格变更的申请流程，包含申请、审批等全生命周期
 */
class PriceChangeRequest extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'sku_id',
        'sku_code',
        'current_price',
        'requested_price',
        'change_amount',
        'change_percentage',
        'reason',
        'remark',
        'status',
        'requested_by',
        'requested_by_name',
        'approved_by',
        'approved_by_name',
        'approval_remark',
        'approved_at',
        'requested_at',
    ];

    protected function casts(): array
    {
        return [
            'current_price' => 'decimal:2',
            'requested_price' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'change_percentage' => 'decimal:2',
            'approved_at' => 'datetime',
            'requested_at' => 'datetime',
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
     * 获取关联商品
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * 获取申请人
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * 获取审批人
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * 获取状态标签映射
     */
    public static function getStatusLabels(): array
    {
        return [
            'pending' => '待审批',
            'approved' => '已通过',
            'rejected' => '已驳回',
            'cancelled' => '已取消',
            'processing' => '处理中',
            'completed' => '已完成',
        ];
    }

    /**
     * 获取状态对应的颜色标识
     */
    public static function getStatusColors(): array
    {
        return [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'secondary',
            'processing' => 'primary',
            'completed' => 'info',
        ];
    }

    /**
     * 判断申请是否处于待审批状态
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * 判断申请是否已通过审批
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * 判断申请是否已被驳回
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * 判断申请是否需要审批
     * 当变更金额或百分比超过阈值时需要额外审批
     */
    public function needsApproval(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $threshold = config('price.approval_threshold', 10);

        return abs($this->change_percentage) >= $threshold
            || abs($this->change_amount) >= $threshold;
    }

    public function tasks()
    {
        return $this->hasMany(PriceChangeTask::class, 'price_change_request_id');
    }
}
