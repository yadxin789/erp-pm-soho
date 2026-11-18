<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationLog extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $table = 'soho_operation_logs';

    protected $fillable = [
        'user_id',
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
            'changes'  => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取操作动作的中文标签
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'created'       => '创建',
            'updated'       => '更新',
            'deleted'       => '删除',
            'login'         => '登录',
            'logout'        => '登出',
            'login_failed'  => '登录失败',
            'published'     => '发布',
            'synced'        => '同步',
            'exported'      => '导出',
            'imported'      => '导入',
            default         => $this->action,
        };
    }

    /**
     * 获取目标模型的简短名称
     */
    public function getModelNameAttribute(): string
    {
        return $this->model_type ? class_basename($this->model_type) : '';
    }
}
