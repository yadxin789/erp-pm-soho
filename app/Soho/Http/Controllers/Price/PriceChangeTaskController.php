<?php

namespace App\Soho\Http\Controllers\Price;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\PriceChangeRequest;
use App\Soho\Models\PriceChangeTask;
use App\Soho\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 调价任务控制器
 * 管理价格变更任务的创建、查看、执行和取消，支持从已批准的申请创建任务
 */
class PriceChangeTaskController extends Controller
{
    /**
     * 调价任务列表
     * 支持按状态、类型、时间筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $tasks = PriceChangeTask::with(['request:id,reason,status', 'executor:id,name'])
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($search = $request->input('search'), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('target_code', 'like', "%{$search}%")
                      ->orWhere('result_message', 'like', "%{$search}%");
                });
            })
            ->when($status = $request->input('status'), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($taskType = $request->input('task_type'), function ($query) use ($taskType) {
                $query->where('task_type', $taskType);
            })
            ->when($dateFrom = $request->input('date_from'), function ($query) use ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom . ' 00:00:00');
            })
            ->when($dateTo = $request->input('date_to'), function ($query) use ($dateTo) {
                $query->where('created_at', '<=', $dateTo . ' 23:59:59');
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        $statusLabels = PriceChangeTask::getStatusLabels();
        $taskTypeLabels = PriceChangeTask::getTaskTypeLabels();
        $targetTypeLabels = PriceChangeTask::getTargetTypeLabels();

        return view('price.price-change-tasks.index', compact(
            'tasks',
            'statusLabels',
            'taskTypeLabels',
            'targetTypeLabels',
            'search'
        ));
    }

    /**
     * 创建调价任务
     * 支持从已批准的调价申请创建任务，或直接创建任务
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $approvedRequests = PriceChangeRequest::where('tenant_id', $tenantId)
            ->where('status', 'approved')
            ->whereDoesntHave('tasks')
            ->orderByDesc('approved_at')
            ->get(['id', 'product_id', 'sku_id', 'sku_code', 'requested_price', 'original_price', 'reason']);

        $products = Product::where('tenant_id', $tenantId)
            ->with(['skus'])
            ->orderBy('name')
            ->get();

        $taskTypeLabels = PriceChangeTask::getTaskTypeLabels();
        $targetTypeLabels = PriceChangeTask::getTargetTypeLabels();

        $preselectedRequest = null;
        if ($request->input('request_id')) {
            $preselectedRequest = PriceChangeRequest::where('tenant_id', $tenantId)
                ->where('status', 'approved')
                ->find($request->input('request_id'));
        }

        return view('price.price-change-tasks.create', compact(
            'approvedRequests',
            'products',
            'taskTypeLabels',
            'targetTypeLabels',
            'preselectedRequest'
        ));
    }

    /**
     * 保存任务
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_id' => 'nullable|exists:soho_price_change_requests,id',
            'task_type' => 'required|in:price_update,price_revert,batch_adjust,sync_platform,rule_apply',
            'target_type' => 'required|in:product,sku,category,shop,platform',
            'target_id' => 'required|integer',
            'target_code' => 'nullable|string|max:100',
            'target_price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $user = Auth::user();

        $requestId = $validated['request_id'] ?? null;

        if ($requestId) {
            $changeRequest = PriceChangeRequest::where('tenant_id', $tenantId)
                ->where('status', 'approved')
                ->findOrFail($requestId);

            $validated['request_id'] = $changeRequest->id;
            $validated['target_id'] = $changeRequest->product_id;
            $validated['target_code'] = $changeRequest->sku_code ?? $changeRequest->product_id;
            $validated['target_price'] = $changeRequest->requested_price;
            $validated['original_price'] = $changeRequest->current_price;
        }

        $validated['tenant_id'] = $tenantId;
        $validated['status'] = 'pending';

        PriceChangeTask::create($validated);

        if ($requestId) {
            $changeRequest = PriceChangeRequest::find($requestId);
            if ($changeRequest) {
                $changeRequest->update(['status' => 'processing']);
            }
        }

        return redirect()->route('price-change-tasks.index')->with('success', '调价任务创建成功');
    }

    /**
     * 任务详情
     *
     * @param PriceChangeTask $priceChangeTask
     * @return \Illuminate\View\View
     */
    public function show(PriceChangeTask $priceChangeTask)
    {
        $tenantId = Auth::user()->tenant_id;
        if ($tenantId && $priceChangeTask->tenant_id !== $tenantId) {
            abort(403, '无权查看此任务');
        }

        $priceChangeTask->load(['request:id,reason,status', 'executor:id,name,email']);

        $statusLabels = PriceChangeTask::getStatusLabels();
        $taskTypeLabels = PriceChangeTask::getTaskTypeLabels();
        $targetTypeLabels = PriceChangeTask::getTargetTypeLabels();

        return view('price.price-change-tasks.show', compact(
            'priceChangeTask',
            'statusLabels',
            'taskTypeLabels',
            'targetTypeLabels'
        ));
    }

    /**
     * 执行调价任务
     *
     * @param PriceChangeTask $priceChangeTask
     * @return \Illuminate\Http\RedirectResponse
     */
    public function execute(PriceChangeTask $priceChangeTask)
    {
        $tenantId = Auth::user()->tenant_id;
        if ($tenantId && $priceChangeTask->tenant_id !== $tenantId) {
            abort(403, '无权执行此任务');
        }

        if (!$priceChangeTask->canExecute()) {
            return back()->with('error', '当前任务无法执行（状态不为待执行或定时时间未到）');
        }

        $user = Auth::user();

        $priceChangeTask->update([
            'status' => 'processing',
            'executed_by' => $user->id,
            'executed_at' => now(),
        ]);

        $executionResult = $this->performPriceUpdate($priceChangeTask);

        if ($executionResult['success']) {
            $priceChangeTask->update([
                'status' => 'completed',
                'result_message' => $executionResult['message'],
                'result_data' => $executionResult['data'] ?? null,
            ]);

            if ($priceChangeTask->request_id) {
                $changeRequest = PriceChangeRequest::find($priceChangeTask->request_id);
                if ($changeRequest) {
                    $changeRequest->update(['status' => 'completed']);
                }
            }

            return redirect()->route('price-change-tasks.show', $priceChangeTask)
                ->with('success', '调价任务执行成功');
        } else {
            $priceChangeTask->update([
                'status' => 'failed',
                'result_message' => $executionResult['message'],
                'result_data' => $executionResult['data'] ?? null,
            ]);

            return redirect()->route('price-change-tasks.show', $priceChangeTask)
                ->with('error', '调价任务执行失败：' . $executionResult['message']);
        }
    }

    /**
     * 取消任务
     *
     * @param PriceChangeTask $priceChangeTask
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(PriceChangeTask $priceChangeTask)
    {
        $tenantId = Auth::user()->tenant_id;
        if ($tenantId && $priceChangeTask->tenant_id !== $tenantId) {
            abort(403, '无权取消此任务');
        }

        if (!$priceChangeTask->isPending() && $priceChangeTask->status !== 'failed') {
            return back()->with('error', '仅待执行或失败状态的任务可以取消');
        }

        $priceChangeTask->update(['status' => 'cancelled']);

        if ($priceChangeTask->request_id) {
            $changeRequest = PriceChangeRequest::find($priceChangeTask->request_id);
            if ($changeRequest && $changeRequest->status === 'processing') {
                $changeRequest->update(['status' => 'approved']);
            }
        }

        return redirect()->route('price-change-tasks.index')->with('success', '调价任务已取消');
    }

    /**
     * 执行价格更新的实际逻辑
     *
     * @param PriceChangeTask $task
     * @return array
     */
    private function performPriceUpdate(PriceChangeTask $task): array
    {
        try {
            if ($task->target_type === 'product') {
                $product = Product::where('tenant_id', $task->tenant_id)
                    ->find($task->target_id);

                if (!$product) {
                    return ['success' => false, 'message' => '找不到目标商品'];
                }

                $task->update(['original_price' => $product->retail_price]);

                $product->update(['retail_price' => $task->target_price]);

                return [
                    'success' => true,
                    'message' => '商品价格更新成功',
                    'data' => [
                        'product_id' => $product->id,
                        'old_price' => $task->original_price,
                        'new_price' => $task->target_price,
                    ],
                ];
            }

            return ['success' => false, 'message' => '暂不支持的目标类型：' . $task->target_type];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => '执行异常：' . $e->getMessage()];
        }
    }
}