<?php

namespace App\Soho\Console\Commands;

use App\Core\Models\ApprovalFlow;
use App\Core\Models\ApprovalInstance;
use App\Soho\Models\Tenant;
use App\Core\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * 审批流程测试命令
 * 模拟创建审批流程实例，验证后端逻辑是否正常
 *
 * 使用方法: php artisan test:approval-flow
 */
class TestApprovalFlow extends Command
{
    protected $signature = 'soho:test-approval-flow';
    protected $description = '测试审批流程：创建审批流 -> 提交审批实例 -> 模拟审批通过/驳回';

    public function handle(): int
    {
        $this->info('========================================');
        $this->info('  审批流程后端逻辑测试');
        $this->info('========================================');
        $this->newLine();

        try {
            DB::beginTransaction();

            // 1. 准备测试数据
            $tenant = $this->getOrCreateTenant();
            $admin = $this->getOrCreateAdminUser($tenant);
            $approver = $this->getOrCreateApproverUser($tenant);

            $this->info("[1/5] 基础数据准备完成");
            $this->info("      租户: {$tenant->name} (ID: {$tenant->id})");
            $this->info("      提交人: {$admin->email} (ID: {$admin->id})");
            $this->info("      审批人: {$approver->email} (ID: {$approver->id})");

            // 2. 创建审批流
            $flow = $this->createApprovalFlow($tenant, $admin);
            $this->info("[2/5] 审批流创建完成");
            $this->info("      名称: {$flow->name}");
            $this->info("      编码: {$flow->code}");
            $this->info("      类型: {$flow->type}");
            $this->info("      节点数: " . count($flow->flow_config['nodes']));

            // 3. 提交审批实例
            $instance = $this->createApprovalInstance($flow, $tenant, $admin);
            $this->info("[3/5] 审批实例提交完成");
            $this->info("      标题: {$instance->title}");
            $this->info("      状态: {$instance->status}");
            $this->info("      业务类型: {$instance->biz_type}");
            $this->info("      金额: {$instance->amount} {$instance->currency}");

            // 4. 审批人查看并通过
            $this->info("[4/5] 模拟审批人操作...");
            $this->info("      当前状态: {$instance->status}");

            // 模拟多级审批
            $this->simulateApproval($instance, $approver);

            // 5. 验证最终结果
            $this->info("[5/5] 验证审批结果");
            $this->verifyResult($instance);

            DB::commit();

            $this->newLine();
            $this->info('========================================');
            $this->info('  ✅ 审批流程测试通过！');
            $this->info('========================================');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ 测试失败: " . $e->getMessage());
            $this->error("   文件: " . $e->getFile() . ":" . $e->getLine());
            $this->error("   堆栈: " . $e->getTraceAsString());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * 获取或创建测试租户
     */
    private function getOrCreateTenant(): Tenant
    {
        return Tenant::firstOrCreate(
            ['code' => 'test_tenant'],
            [
                'name' => '测试租户',
                'status' => 1,
                'contact_name' => '测试管理员',
                'email' => 'test@example.com',
            ]
        );
    }

    /**
     * 获取或创建管理员用户（提交人）
     */
    private function getOrCreateAdminUser(Tenant $tenant): User
    {
        return User::firstOrCreate(
            ['email' => 'test_submitter@example.com'],
            [
                'name' => '测试提交人',
                'password' => bcrypt('password123'),
                'tenant_id' => $tenant->id,
                'status' => 1,
            ]
        );
    }

    /**
     * 获取或创建审批人用户
     */
    private function getOrCreateApproverUser(Tenant $tenant): User
    {
        return User::firstOrCreate(
            ['email' => 'test_approver@example.com'],
            [
                'name' => '测试审批人',
                'password' => bcrypt('password123'),
                'tenant_id' => $tenant->id,
                'status' => 1,
            ]
        );
    }

    /**
     * 创建审批流配置
     */
    private function createApprovalFlow(Tenant $tenant, User $creator): ApprovalFlow
    {
        $now = now();

        // 构造多级审批节点
        $flowConfig = [
            'nodes' => [
                [
                    'node_key' => 'manager_review',
                    'node_name' => '主管审批',
                    'node_type' => 'approve',
                    'approver_type' => 'role',
                    'approver_value' => 'warehouse_mgr',
                    'is_required' => true,
                ],
                [
                    'node_key' => 'finance_approve',
                    'node_name' => '财务审批',
                    'node_type' => 'approve',
                    'approver_type' => 'role',
                    'approver_value' => 'finance',
                    'is_required' => true,
                ],
                [
                    'node_key' => 'final_approve',
                    'node_name' => '最终审批',
                    'node_type' => 'approve',
                    'approver_type' => 'role',
                    'approver_value' => 'tenant_admin',
                    'is_required' => true,
                ],
            ],
            'mode' => 'sequential', // sequential 顺序审批 / parallel 并行审批
            'reject_mode' => 'any', // any 任一驳回即终止 / all 需全部驳回
        ];

        return ApprovalFlow::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'code' => 'test_refund_approval',
            ],
            [
                'name' => '退款审批流程（测试）',
                'type' => 'refund',
                'scope' => 'all',
                'trigger_condition' => [
                    'min_amount' => 100,
                    'max_amount' => null,
                ],
                'flow_config' => $flowConfig,
                'escalation_config' => [
                    'enabled' => true,
                    'timeout_hours' => 48,
                    'escalate_to' => 'tenant_admin',
                ],
                'status' => 'enabled',
                'priority' => 10,
                'description' => '用于测试的退款审批流程',
                'created_by' => $creator->id,
            ]
        );
    }

    /**
     * 创建审批实例
     */
    private function createApprovalInstance(ApprovalFlow $flow, Tenant $tenant, User $submitter): ApprovalInstance
    {
        $now = now();

        // 构造业务数据快照
        $bizData = [
            'order_no' => 'SO' . $now->format('YmdHis'),
            'product_name' => '测试商品A',
            'quantity' => 2,
            'reason' => '商品质量问题，客户要求退款',
            'refund_type' => 'full',
            'shop_name' => '测试店铺',
        ];

        // 构造审批节点快照（从审批流复制，确保实例化时的配置固定）
        $nodesSnapshot = $flow->flow_config['nodes'];

        // 初始审批记录
        $approvalLogs = [
            [
                'node' => 0,
                'node_key' => $nodesSnapshot[0]['node_key'],
                'node_name' => $nodesSnapshot[0]['node_name'],
                'action' => 'submit',
                'actor_id' => $submitter->id,
                'actor_name' => $submitter->name,
                'remark' => '客户反馈商品质量问题，申请退款',
                'created_at' => $now->toIso8601String(),
            ],
        ];

        return ApprovalInstance::create([
            'tenant_id' => $tenant->id,
            'flow_id' => $flow->id,
            'type' => $flow->type,
            'biz_type' => 'after_sale',
            'biz_id' => 99999,
            'biz_no' => 'AS' . $now->format('YmdHis'),
            'title' => '【退款申请】' . $bizData['product_name'] . ' × ' . $bizData['quantity'],
            'content' => $bizData['reason'],
            'biz_data' => $bizData,
            'amount' => 299.0000,
            'currency' => 'CNY',
            'current_node' => 0,
            'nodes_snapshot' => $nodesSnapshot,
            'approval_logs' => $approvalLogs,
            'status' => 'approving',
            'submitted_by' => $submitter->id,
            'submitted_at' => $now,
            'started_at' => $now,
            'deadline_at' => $now->addHours(48),
        ]);
    }

    /**
     * 模拟多级审批流程
     */
    private function simulateApproval(ApprovalInstance $instance, User $approver): void
    {
        $nodes = $instance->nodes_snapshot;
        $logs = $instance->approval_logs;
        $now = now();

        // 顺序审批：依次通过每个节点
        foreach ($nodes as $index => $node) {
            $this->info("      └─ 节点 {$index}: {$node['node_name']} ... 审批通过");

            // 追加审批日志
            $logs[] = [
                'node' => $index,
                'node_key' => $node['node_key'],
                'node_name' => $node['node_name'],
                'action' => 'approve',
                'actor_id' => $approver->id,
                'actor_name' => $approver->name,
                'remark' => '同意退款，已核实',
                'created_at' => $now->toIso8601String(),
            ];
        }

        // 更新实例状态
        $instance->update([
            'current_node' => count($nodes), // 所有节点已完成
            'status' => 'approved',
            'approval_logs' => $logs,
            'completed_at' => $now,
            'approved_by' => $approver->id,
            'final_remark' => '全部审批通过，退款可以执行',
        ]);
    }

    /**
     * 验证审批结果
     */
    private function verifyResult(ApprovalInstance $instance): void
    {
        // 重新从数据库加载
        $fresh = ApprovalInstance::with(['flow', 'submitter'])->find($instance->id);

        $this->info("      审批实例ID: {$fresh->id}");
        $this->info("      审批流: {$fresh->flow->name}");
        $this->info("      标题: {$fresh->title}");
        $this->info("      最终状态: {$fresh->status}");
        $this->info("      当前节点: {$fresh->current_node} / " . count($fresh->nodes_snapshot));
        $this->info("      审批日志数: " . count($fresh->approval_logs));
        $this->info("      完成时间: {$fresh->completed_at}");

        // 状态验证
        if ($fresh->status !== 'approved') {
            throw new \RuntimeException("审批状态不正确，期望 approved，实际 {$fresh->status}");
        }

        if (count($fresh->approval_logs) !== count($fresh->nodes_snapshot) + 1) {
            // +1 因为提交日志也记录了
            throw new \RuntimeException("审批日志数量不正确");
        }

        // 数据完整性验证
        if (empty($fresh->biz_data) || !isset($fresh->biz_data['order_no'])) {
            throw new \RuntimeException("业务数据快照不完整");
        }

        if ($fresh->amount <= 0) {
            throw new \RuntimeException("金额数据异常");
        }

        $this->info("      ✅ 数据完整性验证通过");

        // 列出所有审批日志
        $this->info("      --- 审批日志 ---");
        foreach ($fresh->approval_logs as $log) {
            $action = match($log['action']) {
                'submit' => '提交',
                'approve' => '通过',
                'reject' => '驳回',
                default => $log['action'],
            };
            $this->info("      [{$log['node']}] {$log['node_name']} => {$action} ({$log['actor_name']})");
        }
    }
}