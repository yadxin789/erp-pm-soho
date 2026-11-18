<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 价格变更任务模型
 * 记录价格变更的具体执行任务，支持定时执行和批量处理
 */
class PriceChangeTask extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'request_id',
        'task_type',
        'target_type',
        'target_id',
        'target_code',
        'target_price',
        'original_price',
        'status',
        'scheduled_at',
        'executed_at',
        'executed_by',
        'result_message',
        'result_data',
    ];

    protected function casts(): array
    {
        return [
            'target_price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'scheduled_at' => 'datetime',
            'executed_at' => 'datetime',
            'result_data' => 'array',
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
     * 获取关联的价格变更申请
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(PriceChangeRequest::class, 'request_id');
    }

    /**
     * 获取执行人
     */
    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    /**
     * 获取状态标签映射
     */
    public static function getStatusLabels(): array
    {
        return [
            'pending' => '待执行',
            'processing' => '执行中',
            'completed' => '已完成',
            'failed' => '执行失败',
            'cancelled' => '已取消',
        ];
    }

    /**
     * 获取任务类型标签映射
     */
    public static function getTaskTypeLabels(): array
    {
        return [
            'price_update' => '价格更新',
            'price_revert' => '价格回滚',
            'batch_adjust' => '批量调整',
            'sync_platform' => '同步到平台',
            'rule_apply' => '规则应用',
        ];
    }

    /**
     * 获取目标类型标签映射
     */
    public static function getTargetTypeLabels(): array
    {
        return [
            'product' => '商品',
            'sku' => 'SKU',
            'category' => '类目',
            'shop' => '店铺',
            'platform' => '平台',
        ];
    }

    /**
     * 判断任务是否处于待执行状态
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * 判断任务是否已完成
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * 判断任务是否可以执行
     * 需要满足：状态为待执行，且定时执行时间已到（或未设置定时时间）
     */
    public function canExecute(): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        if ($this->scheduled_at && $this->scheduled_at->isFuture()) {
            return false;
        }

        return true;
    }
}