<?php

namespace Database\Seeders;

use App\Core\Models\ApprovalFlow;
use App\Core\Services\ApprovalService;
use App\Core\Models\User;
use Illuminate\Database\Seeder;

/**
 * TC-11 演示数据：补齐审批流里引用的审批角色，并造两条「进行中」的审批，
 * 让「待我审批 / 我发起的」两个页签都有真实数据。
 *
 * 角色对齐（TC-07 重灌后的带前缀角色名）：
 *   erp.sales_manager     → multi_module（已有）
 *   erp.purchase_manager  → admin（本轮补）
 *   erp.admin             → admin（已有）
 *   soho.operation_manager→ multi_module（已有）
 *   soho.super_admin      → admin@soho.demo（本轮补）
 */
class ApprovalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $assign = [
            'erp.purchase_manager' => 'admin',
            'soho.super_admin' => 'admin@soho.demo',
        ];

        foreach ($assign as $role => $account) {
            $user = User::where('username', $account)->orWhere('email', $account)->first();
            if ($user && ! $user->hasRole($role)) {
                $user->assignRole($role);
            }
        }

        /** @var ApprovalService $service */
        $service = app(ApprovalService::class);

        // ERP：采购订单付款审批（发起人 multi_module，第一步采购经理=admin，第二步财务=erp.admin=admin）
        $flow = $service->flowFor('erp', 'purchase_order');
        $applicant = User::where('username', 'multi_module')->first();
        if ($flow && $applicant) {
            try {
                $service->start($flow, $applicant, 'purchase_order', 20260007,
                    '采购订单 PO-2026-0007 付款审批',
                    ['amount' => 186400.00, 'supplier' => '宁波华强电子', 'note' => '首批贴片电容，账期 30 天']);
            } catch (\Throwable $e) {
                // 已存在就跳过
            }
        }

        // SOHO：商品发布（发起人 admin@soho.demo，初审 operation_manager=multi_module，复审 super_admin=admin@soho.demo）
        $flow = $service->flowFor('soho', 'product_publish');
        $applicant = User::where('email', 'admin@soho.demo')->first() ?: User::where('username', 'admin@soho.demo')->first();
        if ($flow && $applicant) {
            try {
                $service->start($flow, $applicant, 'product_publish', 1001,
                    '商品发布：无线蓝牙耳机 X3 上架审核',
                    ['sku' => 'DDWL-X3-BLK', 'price' => 289.00, 'shop' => 'SOHO 旗舰店']);
            } catch (\Throwable $e) {
                // 已存在就跳过
            }
        }

        // 一条已完成的（用于「最近完成」区块）
        $flow = $service->flowFor('erp', 'sales_order');
        $applicant = User::where('username', 'multi_module')->first();
        if ($flow && $applicant) {
            try {
                $instance = $service->start($flow, $applicant, 'sales_order', 20260031,
                    '销售订单 SO-2026-0031 折扣审批', ['amount' => 52000.00, 'discount' => '8.5 折']);
                foreach ($instance->flow->steps as $step) {
                    $task = $instance->tasks()->where('step_order', $step->step_order)->where('status', 'pending')->first();
                    if ($task) {
                        $service->approve($task, $task->approver, '金额在授权范围内，同意。');
                    }
                }
            } catch (\Throwable $e) {
                // 已存在就跳过
            }
        }
    }
}
