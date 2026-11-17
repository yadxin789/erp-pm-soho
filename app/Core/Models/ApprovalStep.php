<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 审批步骤（线性多级）。
 *
 * approver_type：
 *   role       —— approver_value 是带前缀的角色名（erp.admin / pm.pm_director / soho.operation_manager）
 *   user       —— approver_value 是 user id
 *   department —— approver_value 是部门 id（默认交给该部门负责人，没有负责人则交给部门全部成员）
 *   applicant  —— 回给发起人确认（如「提交人复核」）
 */
class ApprovalStep extends Model
{
    public const TYPE_ROLE = 'role';
    public const TYPE_USER = 'user';
    public const TYPE_DEPARTMENT = 'department';
    public const TYPE_APPLICANT = 'applicant';

    protected $fillable = ['approval_flow_id', 'step_order', 'name', 'approver_type', 'approver_value'];

    protected $casts = ['step_order' => 'integer'];

    public function flow(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlow::class, 'approval_flow_id');
    }

    public function typeLabel(): string
    {
        return match ($this->approver_type) {
            self::TYPE_ROLE => '角色',
            self::TYPE_USER => '指定人',
            self::TYPE_DEPARTMENT => '部门',
            self::TYPE_APPLICANT => '发起人',
            default => $this->approver_type,
        };
    }
}
