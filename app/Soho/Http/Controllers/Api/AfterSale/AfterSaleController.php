<?php

namespace App\Soho\Http\Controllers\Api\AfterSale;

use App\Soho\Models\OrderAfterSale;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

/**
 * 售后API控制器
 */
class AfterSaleController extends Controller
{
    /**
     * 允许状态流转的映射
     */
    private const ALLOWED_TRANSITIONS = [
        'pending' => ['approved', 'rejected'],
        'approved' => ['refunded'],
    ];

    /**
     * 获取售后列表
     */
    public function index(Request $request)
    {
        $afterSales = OrderAfterSale::query();

        if ($request->has('status')) {
            $afterSales->where('status', $request->status);
        }
        if ($request->has('shop_id')) {
            $afterSales->where('shop_id', $request->shop_id);
        }

        return response()->json([
            'data' => $afterSales->paginate($request->per_page ?? 20),
        ]);
    }

    /**
     * 获取售后详情
     */
    public function show(OrderAfterSale $afterSale)
    {
        return response()->json([
            'data' => $afterSale,
        ]);
    }

    /**
     * 同意售后申请
     */
    public function approve(Request $request, OrderAfterSale $afterSale)
    {
        $this->validateTransition($afterSale, 'approved');

        DB::transaction(function () use ($afterSale) {
            $afterSale->update(['status' => 'approved']);
        });

        return response()->json([
            'data' => $afterSale->fresh(),
            'message' => '售后已同意',
        ]);
    }

    /**
     * 拒绝售后申请
     */
    public function reject(Request $request, OrderAfterSale $afterSale)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $this->validateTransition($afterSale, 'rejected');

        DB::transaction(function () use ($afterSale, $request) {
            $afterSale->update([
                'status' => 'rejected',
                'reason' => $request->reason,
            ]);
        });

        return response()->json([
            'data' => $afterSale->fresh(),
            'message' => '售后已拒绝',
        ]);
    }

    /**
     * 退款
     */
    public function refund(Request $request, OrderAfterSale $afterSale)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $this->validateTransition($afterSale, 'refunded');

        DB::transaction(function () use ($afterSale, $request) {
            $afterSale->update([
                'status' => 'refunded',
                'refunded_amount' => $request->amount,
            ]);
        });

        return response()->json([
            'data' => $afterSale->fresh(),
            'message' => '退款已完成',
        ]);
    }

    /**
     * 校验状态流转是否合法
     */
    private function validateTransition(OrderAfterSale $afterSale, string $targetStatus): void
    {
        $currentStatus = $afterSale->status;
        $allowed = self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];

        if (!in_array($targetStatus, $allowed)) {
            abort(422, "当前状态 [{$currentStatus}] 不允许变更为 [{$targetStatus}]");
        }
    }
}