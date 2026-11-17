<?php

/**
 * TC-11 审批引擎验收脚本。
 *
 * 断言「引擎规则」而不是「页面好看」：
 *   1. 流定义：两源 10 条流 / 20 步全部入库，角色名带模块前缀，停用的 SOHO 数据删除流保持停用
 *   2. 顺序推进：第 1 步同意后 current_step=2，且第 1 步不残留 pending
 *   3. 同一步多人任一人同意 → 其余置 skipped（与 ERP「任一审批」一致）
 *   4. 驳回 → 整单 rejected，所有 pending 清零
 *   5. 撤销 → cancelled；已结束的单子不能再撤销
 *   6. 单据进行中不允许重复发起
 *   7. 越权：非该步审批人处理 → 抛错
 *   8. 按单据查实例（forDocument）能拿到最新一条
 *
 * 用完自清所有 [verify] 实例。
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Core\Models\ApprovalFlow;
use App\Core\Models\ApprovalInstance;
use App\Core\Models\ApprovalTask;
use App\Core\Models\User;
use App\Core\Services\ApprovalService;

$service = app(ApprovalService::class);
$pass = 0;
$fail = 0;

function ok(bool $cond, string $label, string $extra = ''): void
{
    global $pass, $fail;
    if ($cond) {
        $pass++;
        echo "  OK   {$label}".($extra ? "  [{$extra}]" : '')."\n";
    } else {
        $fail++;
        echo "  FAIL {$label}".($extra ? "  [{$extra}]" : '')."\n";
    }
}

function cleanup(): void
{
    foreach (ApprovalInstance::where('title', 'like', '%[verify]%')->get() as $i) {
        ApprovalTask::where('approval_instance_id', $i->id)->delete();
        $i->delete();
    }
}

cleanup();

echo "== 1. 流定义\n";
$flows = ApprovalFlow::with('steps')->orderBy('module')->orderBy('id')->get();
ok($flows->count() === 10, '两源共 10 条流入库', '实际 '.$flows->count());
ok((int) ApprovalFlow::where('module', 'erp')->count() === 2, 'ERP 2 条流');
ok((int) ApprovalFlow::where('module', 'soho')->count() === 8, 'SOHO 8 条流');
$totalSteps = $flows->sum(fn ($f) => $f->steps->count());
ok($totalSteps === 20, '步骤合计 20 条', '实际 '.$totalSteps);
ok(ApprovalFlow::where('module', 'erp')->where('document_type', 'sales_order')->first()?->steps->pluck('approver_value')->all() === ['erp.sales_manager', 'erp.admin'], 'ERP 销售流步骤与角色前缀正确');
ok(ApprovalFlow::where('document_type', 'data_delete')->first()?->enabled === false, 'SOHO 数据删除流保持停用');

echo "== 2. 顺序推进（ERP 销售订单流）\n";
$flow = $service->flowFor('erp', 'sales_order');
$applicant = User::where('username', 'admin')->first();
$inst = $service->start($flow, $applicant, 'sales_order', 88001, '[verify] 顺序推进', ['amount' => 1000]);
$t1 = $inst->tasks()->where('step_order', 1)->where('status', 'pending')->first();
ok($t1 !== null, '第 1 步生成待办', 'approver='.($t1?->approver_id));
ok($inst->fresh()->current_step === 1 && $inst->fresh()->status === 'pending', '发起后状态 pending / 当前步 1');
$service->approve($t1, $t1->approver, '第一步同意');
$fresh = $inst->fresh();
ok($fresh->current_step === 2, '同意后推进到第 2 步', 'current_step='.$fresh->current_step);
ok($fresh->status === 'pending', '第 2 步未处理时仍 pending');
ok(ApprovalTask::where('approval_instance_id', $inst->id)->where('step_order', 1)->where('status', 'pending')->count() === 0, '第 1 步无残留 pending');
$t2 = $inst->tasks()->where('step_order', 2)->where('status', 'pending')->first();
$service->approve($t2, $t2->approver, '第二步同意');
$fresh = $inst->fresh();
ok($fresh->status === 'approved' && $fresh->finished_at !== null, '最后一步同意后 approved 且写结束时间');
ok($fresh->current_step === 2, '结束时 current_step 停在末步');

echo "== 3. 单据进行中不可重复发起\n";
$service->start($flow, $applicant, 'sales_order', 88002, '[verify] 重复发起基单');
$threw = false;
try {
    $service->start($flow, $applicant, 'sales_order', 88002, '[verify] 重复发起');
} catch (Throwable $e) {
    $threw = str_contains($e->getMessage(), '进行中');
}
ok($threw, '同一单据第二次发起被拦下');

echo "== 4. 驳回整单结束\n";
$rej = $service->start($flow, $applicant, 'sales_order', 88003, '[verify] 驳回');
$t = $rej->tasks()->where('status', 'pending')->first();
$service->reject($t, $t->approver, '金额超标');
$rej = $rej->fresh();
ok($rej->status === 'rejected', '驳回后整单 rejected');
ok(ApprovalTask::where('approval_instance_id', $rej->id)->where('status', 'pending')->count() === 0, '驳回后无残留 pending');

echo "== 5. 撤销\n";
$can = $service->start($flow, $applicant, 'sales_order', 88004, '[verify] 撤销');
$service->cancel($can, $applicant);
ok($can->fresh()->status === 'cancelled', '发起人撤销成功');
$again = false;
try {
    $service->cancel($can->fresh(), $applicant);
} catch (Throwable $e) {
    $again = str_contains($e->getMessage(), '已结束');
}
ok($again, '已结束的单子不能再撤销');

echo "== 6. 越权处理\n";
$auth = $service->start($flow, $applicant, 'sales_order', 88005, '[verify] 越权');
$t = $auth->tasks()->where('status', 'pending')->first();
$stranger = User::where('id', '!=', $t->approver_id)->first();
$blocked = false;
try {
    $service->approve($t, $stranger, '我不是审批人');
} catch (Throwable $e) {
    $blocked = str_contains($e->getMessage(), '不属于');
}
ok($blocked, '非该步审批人无法处理');

echo "== 7. 同一步多人·任一人同意\n";
$flowMulti = ApprovalFlow::where('module', 'soho')->where('document_type', 'data_export')->first();
// 造一条「部门」步骤的临时流，验证多人生成多条待办且任一人同意后其余 skipped
$tmpFlow = ApprovalFlow::create(['module' => 'core', 'name' => '[verify] 多审批人', 'document_type' => 'verify_multi', 'enabled' => true]);
\App\Core\Models\ApprovalStep::create(['approval_flow_id' => $tmpFlow->id, 'step_order' => 1, 'name' => '全员审批', 'approver_type' => 'department', 'approver_value' => (string) (User::whereNotNull('department_id')->first()?->department_id ?? 1)]);
$multi = $service->start($tmpFlow, $applicant, 'verify_multi', 88006, '[verify] 多人一步');
$pendingCount = $multi->tasks()->where('status', 'pending')->count();
ok($pendingCount >= 1, '部门步骤生成待办', 'pending='.$pendingCount);
$t = $multi->tasks()->where('status', 'pending')->first();
$service->approve($t, $t->approver, '任一人同意');
ok($multi->fresh()->status === 'approved', '任一人同意即推进到结束');
ok(ApprovalTask::where('approval_instance_id', $multi->id)->where('status', 'pending')->count() === 0, '同步骤其余待办已清理（skipped/cancelled）');

echo "== 8. forDocument\n";
$doc = $service->forDocument('erp', 'sales_order', 88005);
ok($doc !== null && $doc->title === '[verify] 越权', 'forDocument 拿到最新实例');

echo "== 9. 演示数据\n";
echo "  流 ".ApprovalFlow::count()." 条 / 步骤 ".\App\Core\Models\ApprovalStep::count()." 条\n";
echo "  实例 ".ApprovalInstance::count()." 条（pending ".\App\Core\Models\ApprovalInstance::where('status', 'pending')->count()." / approved ".\App\Core\Models\ApprovalInstance::where('status', 'approved')->count()."）\n";
echo "  待办 ".ApprovalTask::count()." 条（pending ".\App\Core\Models\ApprovalTask::where('status', 'pending')->count()."）\n";
foreach (ApprovalInstance::with('flow')->orderBy('id')->get() as $i) {
    echo "    #{$i->id} [{$i->module}] {$i->title} · {$i->status} · 当前步 {$i->current_step}\n";
}

cleanup();
ApprovalTask::whereHas('instance', fn ($q) => $q->where('document_type', 'verify_multi'))->delete();
ApprovalInstance::where('document_type', 'verify_multi')->delete();
ApprovalFlow::where('document_type', 'verify_multi')->delete();

echo "\n通过 {$pass} 项，失败 {$fail} 项\n";
exit($fail === 0 ? 0 : 1);
