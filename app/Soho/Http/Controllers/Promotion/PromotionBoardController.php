<?php

namespace App\Soho\Http\Controllers\Promotion;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 促销与营销活动（内部促销 / 促销分析 / 活动报名 / 活动规则）
 *
 * 数据源：
 * - soho_promotions：站内促销活动（fixed_discount / percentage / buy_get）
 * - soho_promotion_platform_mappings：促销与平台活动映射（报名同步）
 * - soho_coupons / soho_coupon_usages：优惠券与核销记录（分析用）
 */
class PromotionBoardController extends Controller
{
    /** 内部促销（站内活动清单与状态） */
    public function internal(Request $request)
    {
        $base = DB::table('soho_promotions')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t));

        $rows = (clone $base)
            ->when($request->input('type'), fn ($q, $ty) => $q->where('type', $ty))
            ->when($request->input('status'), fn ($q, $st) => $q->where('status', $st))
            ->when($request->input('search'), fn ($q, $kw) => $q->where('name', 'like', "%{$kw}%"))
            ->orderByRaw("FIELD(status,'active','draft','inactive','expired')")
            ->orderByDesc('start_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', 'active')->count(),
            'draft' => (clone $base)->where('status', 'draft')->count(),
            'expired' => (clone $base)->where('status', 'expired')->count(),
            'running' => (clone $base)->where('status', 'active')
                ->where('start_at', '<=', now())->where('end_at', '>=', now())->count(),
        ];

        $types = (clone $base)->select('type')->distinct()->pluck('type');

        return view('soho.promotion.internal', compact('rows', 'stats', 'types'));
    }

    /** 促销分析（活动覆盖 + 优惠券核销效果） */
    public function analysis(Request $request)
    {
        $base = DB::table('soho_promotions')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t));

        $byType = (clone $base)
            ->select('type')
            ->selectRaw('COUNT(*) as cnt')
            ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_cnt")
            ->groupBy('type')
            ->get();

        $byStatus = (clone $base)
            ->select('status')
            ->selectRaw('COUNT(*) as cnt')
            ->groupBy('status')
            ->get();

        // 优惠券效果：领取量 / 核销量 / 核销率 / 优惠金额
        $coupons = DB::table('soho_coupons as c')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('c.tenant_id', $t))
            ->leftJoin('soho_coupon_usages as u', 'u.coupon_id', '=', 'c.id')
            ->select('c.id', 'c.code', 'c.type', 'c.value', 'c.min_amount', 'c.total_quantity', 'c.used_quantity', 'c.status')
            ->selectRaw('COUNT(u.id) as usage_count')
            ->selectRaw('SUM(CASE WHEN u.status = \'used\' THEN 1 ELSE 0 END) as used_count')
            ->selectRaw('SUM(u.discount_amount) as discount_sum')
            ->groupBy('c.id', 'c.code', 'c.type', 'c.value', 'c.min_amount', 'c.total_quantity', 'c.used_quantity', 'c.status')
            ->orderByDesc('usage_count')
            ->get();

        // 平台活动同步状态分布
        $mapping = DB::table('soho_promotion_platform_mappings')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->select('sync_status')
            ->selectRaw('COUNT(*) as cnt')
            ->groupBy('sync_status')
            ->get();

        $usageTotal = (int) DB::table('soho_coupon_usages')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->count();
        $discountTotal = (float) DB::table('soho_coupon_usages')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->where('status', 'used')
            ->sum('discount_amount');

        $stats = [
            'promotions' => (clone $base)->count(),
            'active' => (clone $base)->where('status', 'active')->count(),
            'coupon_usages' => $usageTotal,
            'discount_total' => round($discountTotal, 2),
        ];

        return view('soho.promotion.analysis', compact('byType', 'byStatus', 'coupons', 'mapping', 'stats'));
    }

    /** 活动报名（站内促销报名到平台活动） */
    public function campaignJoin(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'promotion_id' => ['required', 'integer'],
                'platform' => ['required', 'string', 'max:20'],
                'shop_id' => ['required', 'integer'],
            ], [], ['promotion_id' => '促销活动', 'shop_id' => '店铺']);

            $exists = DB::table('soho_promotion_platform_mappings')
                ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
                ->where('promotion_id', $data['promotion_id'])
                ->where('platform', $data['platform'])
                ->where('shop_id', $data['shop_id'])
                ->exists();
            if ($exists) {
                return back()->with('warning', '该促销在该店铺/平台已有报名记录，无需重复报名。');
            }

            $promotion = DB::table('soho_promotions')
                ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
                ->where('id', $data['promotion_id'])
                ->first();
            if (! $promotion) {
                return back()->with('warning', '促销活动不存在。');
            }

            DB::table('soho_promotion_platform_mappings')->insert([
                'tenant_id' => $this->tenantId(),
                'promotion_id' => $data['promotion_id'],
                'platform' => $data['platform'],
                'shop_id' => $data['shop_id'],
                'sync_status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('soho.marketing.campaign.join')
                ->with('success', "「{$promotion->name}」已提交报名，等待与平台活动同步。");
        }

        $base = DB::table('soho_promotion_platform_mappings')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t));

        $rows = (clone $base)
            ->when($request->input('platform'), fn ($q, $p) => $q->where('platform', $p))
            ->when($request->input('sync_status'), fn ($q, $st) => $q->where('sync_status', $st))
            ->orderByRaw("FIELD(sync_status,'pending','syncing','failed','synced')")
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->where('sync_status', 'pending')->count(),
            'synced' => (clone $base)->where('sync_status', 'synced')->count(),
            'failed' => (clone $base)->where('sync_status', 'failed')->count(),
        ];

        $promotions = DB::table('soho_promotions')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->whereIn('status', ['active', 'draft'])
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'status']);
        $shops = $this->shops();
        $platforms = (clone $base)->select('platform')->distinct()->pluck('platform');

        return view('soho.marketing.campaign-join', compact('rows', 'stats', 'promotions', 'shops', 'platforms'));
    }

    /** 活动规则（促销条件与叠加规则配置） */
    public function campaignRule(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'id' => ['required', 'integer'],
                'min_amount' => ['nullable', 'numeric', 'min:0'],
                'stackable' => ['nullable', 'in:0,1'],
                'conditions' => ['nullable', 'string', 'max:500'],
            ], [], ['id' => '促销', 'min_amount' => '门槛金额']);

            $promotion = DB::table('soho_promotions')
                ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
                ->where('id', $data['id'])
                ->first();
            if (! $promotion) {
                return back()->with('warning', '促销活动不存在。');
            }

            $conditions = trim((string) ($data['conditions'] ?? ''));
            if ($conditions !== '') {
                $decoded = json_decode($conditions, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return back()->with('warning', '活动条件必须是合法 JSON，例如 {"category": "all"}。');
                }
            }

            DB::table('soho_promotions')
                ->where('id', $data['id'])
                ->update([
                    'min_amount' => $data['min_amount'] ?? 0,
                    'stackable' => (int) ($data['stackable'] ?? 0),
                    'conditions_json' => $conditions !== '' ? $conditions : null,
                    'updated_at' => now(),
                ]);

            return redirect()->route('soho.marketing.campaign.rule')->with('success', "「{$promotion->name}」活动规则已更新。");
        }

        $rows = DB::table('soho_promotions')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->when($request->input('status'), fn ($q, $st) => $q->where('status', $st))
            ->orderByRaw("FIELD(status,'active','draft','inactive','expired')")
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        $editId = (int) $request->input('edit');
        $editing = $editId ? DB::table('soho_promotions')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->where('id', $editId)->first() : null;

        $stats = [
            'total' => DB::table('soho_promotions')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->count(),
            'stackable' => DB::table('soho_promotions')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->where('stackable', 1)->count(),
            'with_threshold' => DB::table('soho_promotions')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->where('min_amount', '>', 0)->count(),
            'with_conditions' => DB::table('soho_promotions')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->whereNotNull('conditions_json')->count(),
        ];

        return view('soho.marketing.campaign-rule', compact('rows', 'stats', 'editing'));
    }

    // ---------------------------------------------------------------- 内部方法

    private function tenantId(): ?int
    {
        return Auth::user()->tenant_id;
    }

    private function shops()
    {
        return DB::table('soho_shops')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->get(['id', 'shop_name', 'platform'])
            ->keyBy('id');
    }
}
