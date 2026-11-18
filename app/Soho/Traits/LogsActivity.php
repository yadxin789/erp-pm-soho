<?php

namespace App\Soho\Traits;

use App\Soho\Models\OperationLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    /**
     * 初始化 trait - 注册模型事件监听
     */
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            $model->logActivity('created');
        });

        static::updated(function ($model) {
            $model->logActivity('updated');
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted');
        });
    }

    /**
     * 记录操作日志
     */
    protected function logActivity(string $action): void
    {
        if (method_exists($this, 'shouldLogActivity') && !$this->shouldLogActivity($action)) {
            return;
        }

        $changes = [];

        if ($action === 'updated') {
            $changes = [
                'original' => array_intersect_key($this->getOriginal(), $this->getChanges()),
                'changes'  => $this->getChanges(),
            ];
        } elseif ($action === 'deleted') {
            $changes = ['original' => $this->getOriginal()];
        } elseif ($action === 'created') {
            $changes = ['attributes' => $this->getAttributes()];
        }

        OperationLog::create([
            'user_id'      => Auth::id(),
            'action'       => $action,
            'model_type'   => static::class,
            'model_id'     => $this->getKey(),
            'description'  => $this->getActivityDescription($action),
            'changes'      => $changes,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);
    }

    /**
     * 生成操作描述
     */
    protected function getActivityDescription(string $action): string
    {
        $modelName = class_basename(static::class);
        $identifier = $this->getActivityIdentifier();

        return match ($action) {
            'created' => "创建了{$modelName}: {$identifier}",
            'updated' => "更新了{$modelName}: {$identifier}",
            'deleted' => "删除了{$modelName}: {$identifier}",
            default   => "操作了{$modelName}: {$identifier}",
        };
    }

    /**
     * 获取模型标识符（子类可重写）
     */
    protected function getActivityIdentifier(): string
    {
        $attributes = ['name', 'goods_name', 'shop_name', 'system_sku', 'platform_order_no', 'email', 'username'];
        foreach ($attributes as $attr) {
            if (isset($this->attributes[$attr])) {
                return "{$attr}: {$this->attributes[$attr]}";
            }
        }
        return 'ID: ' . $this->getKey();
    }
}
