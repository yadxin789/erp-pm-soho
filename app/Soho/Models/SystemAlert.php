<?php

namespace App\Soho\Models;

use App\Soho\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 系统告警模型
 * 记录系统运行过程中产生的告警信息
 * 包括同步任务失败、API异常、队列堆积、磁盘空间不足等
 */
class SystemAlert extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'title', 'message', 'level', 'source', 'status',
        'triggered_at', 'acknowledged_by', 'acknowledged_at',
        'resolved_by', 'resolved_at', 'resolution_note', 'extra_data',
    ];

    protected function casts(): array
    {
        return [
            'extra_data' => 'array',
            'triggered_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * 关联确认人
     */
    public function acknowledger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    /**
     * 关联解决人
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * 作用域：待处理
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * 作用域：按级别筛选
     */
    public function scopeOfLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    /**
     * 作用域：按来源筛选
     */
    public function scopeOfSource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * 告警级别标签
     */
    public static function getLevelLabels(): array
    {
        return [
            'info' => '提示',
            'warning' => '警告',
            'danger' => '严重',
            'critical' => '致命',
        ];
    }

    /**
     * 告警级别颜色（Bootstrap颜色）
     */
    public static function getLevelColors(): array
    {
        return [
            'info' => 'info',
            'warning' => 'warning',
            'danger' => 'danger',
            'critical' => 'dark',
        ];
    }

    /**
     * 告警级别图标
     */
    public static function getLevelIcons(): array
    {
        return [
            'info' => 'fa-info-circle',
            'warning' => 'fa-exclamation-triangle',
            'danger' => 'fa-exclamation-circle',
            'critical' => 'fa-bug',
        ];
    }

    /**
     * 告警来源标签
     */
    public static function getSourceLabels(): array
    {
        return [
            'system' => '系统',
            'sync' => '同步任务',
            'api' => 'API接口',
            'queue' => '队列',
            'database' => '数据库',
            'disk' => '磁盘',
            'other' => '其他',
        ];
    }

    /**
     * 告警状态标签
     */
    public static function getStatusLabels(): array
    {
        return [
            'pending' => '待处理',
            'acknowledged' => '已确认',
            'resolved' => '已解决',
            'ignored' => '已忽略',
        ];
    }

    /**
     * 告警状态颜色
     */
    public static function getStatusColors(): array
    {
        return [
            'pending' => 'danger',
            'acknowledged' => 'warning',
            'resolved' => 'success',
            'ignored' => 'secondary',
        ];
    }
}
