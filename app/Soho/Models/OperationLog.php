<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SOHO 操作日志（P2-3 统一）。
 *
 * 物理表统一到 Core `operation_logs`，本模型固定 `module='soho'`，
 * 并对外保留 SOHO 旧字段名（model_type / model_id / changes），
 * 让既有调用点无需改动字段语义。
 *
 * 字段映射：model_type → target_type，model_id → target_id，changes → payload。
 */
class OperationLog extends Model
{
    protected $table = 'operation_logs';

    protected $fillable = [
        'module',
        'user_id',
        'user_name',
        'action',
        'model_type',
        'model_id',
        'description',
        'changes',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('soho', function (Builder $q) {
            $q->where('module', 'soho');
        });

        static::creating(function (self $model) {
            $model->module = 'soho';
            if (! $model->user_name && $model->user_id) {
                $model->user_name = optional(\App\Core\Models\User::find($model->user_id))->name;
            }
        });
    }

    /** 旧字段名 → Core 列名 */
    public function setAttribute($key, $value)
    {
        $key = match ($key) {
            'model_type' => 'target_type',
            'model_id' => 'target_id',
            'changes' => 'payload',
            default => $key,
        };

        return parent::setAttribute($key, $value);
    }

    public function getAttribute($key)
    {
        return match ($key) {
            'model_type' => $this->attributes['target_type'] ?? null,
            'model_id' => $this->attributes['target_id'] ?? null,
            'changes' => $this->payloadArray(),
            default => parent::getAttribute($key),
        };
    }

    /** changes 兼容：优先 payload，其次由 old_values/new_values 还原 */
    private function payloadArray(): ?array
    {
        $payload = $this->attributes['payload'] ?? null;
        if (is_string($payload)) {
            $payload = json_decode($payload, true);
        }
        if (is_array($payload)) {
            return $payload;
        }

        $old = $this->old_values;
        $new = $this->new_values;
        if (is_array($old) || is_array($new)) {
            return ['original' => $old ?? [], 'changes' => $new ?? []];
        }

        return null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Core\Models\User::class, 'user_id');
    }

    /** 操作动作的中文标签 */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'created' => '创建',
            'updated' => '更新',
            'deleted' => '删除',
            'login' => '登录',
            'logout' => '登出',
            'login_failed' => '登录失败',
            'published' => '发布',
            'synced' => '同步',
            'exported' => '导出',
            'imported' => '导入',
            default => $this->action,
        };
    }

    /** 目标模型的简短名称 */
    public function getModelNameAttribute(): string
    {
        return $this->target_type ? class_basename($this->target_type) : '';
    }
}
