<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    /**
     * 优惠券列表
     */
    public function index(Request $request)
    {
        $coupons = Coupon::query()
            ->when($request->search, function ($query) use ($request) {
                $query->where('code', 'like', "%{$request->search}%");
            })
            ->when($request->type, function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->status !== null && $request->status !== '', function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // 统计数据
        $stats = [
            'total' => Coupon::count(),
            'active' => Coupon::where('status', 'active')->count(),
            'inactive' => Coupon::where('status', 'inactive')->count(),
            'expired' => Coupon::where('status', 'expired')->count(),
        ];

        return view('coupon.index', compact('coupons', 'stats'));
    }

    /**
     * 创建优惠券（显示表单）
     */
    public function create()
    {
        return view('coupon.create');
    }

    /**
     * 保存新优惠券
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:soho_coupons,code',
            'type' => ['required', Rule::in(['fixed_discount', 'percentage', 'shipping'])],
            'value' => 'required|numeric|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'total_quantity' => 'required|integer|min:1',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'status' => ['required', Rule::in(['active', 'inactive', 'expired'])],
            'applicable_products' => 'nullable|array',
            'applicable_categories' => 'nullable|array',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id ?? 1;
        $validated['used_quantity'] = 0;
        $validated['min_amount'] = $validated['min_amount'] ?? 0;

        if (empty($validated['code'])) {
            $validated['code'] = $this->generateCouponCode();
        }

        Coupon::create($validated);

        return redirect()->route('soho.coupon.index')->with('success', '优惠券创建成功');
    }

    /**
     * 编辑优惠券（显示表单）
     */
    public function edit(Coupon $coupon)
    {
        return view('coupon.edit', compact('coupon'));
    }

    /**
     * 更新优惠券
     */
    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50', Rule::unique('soho_coupons', 'code')->ignore($coupon->id)],
            'type' => ['required', Rule::in(['fixed_discount', 'percentage', 'shipping'])],
            'value' => 'required|numeric|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'total_quantity' => 'required|integer|min:' . $coupon->used_quantity,
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'status' => ['required', Rule::in(['active', 'inactive', 'expired'])],
            'applicable_products' => 'nullable|array',
            'applicable_categories' => 'nullable|array',
        ]);

        $coupon->update($validated);

        return redirect()->route('soho.coupon.index')->with('success', '优惠券更新成功');
    }

    /**
     * 删除优惠券
     */
    public function destroy(Coupon $coupon)
    {
        if ($coupon->used_quantity > 0) {
            return back()->with('error', '该优惠券已有使用记录，无法删除');
        }

        $coupon->delete();

        return redirect()->route('soho.coupon.index')->with('success', '优惠券删除成功');
    }

    /**
     * 批量生成优惠券
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'count' => 'required|integer|min:1|max:1000',
            'type' => ['required', Rule::in(['fixed_discount', 'percentage', 'shipping'])],
            'value' => 'required|numeric|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'total_quantity' => 'required|integer|min:1',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'status' => ['required', Rule::in(['active', 'inactive', 'expired'])],
            'prefix' => 'nullable|string|max:20',
            'applicable_products' => 'nullable|array',
            'applicable_categories' => 'nullable|array',
        ]);

        $tenantId = auth()->user()->tenant_id ?? 1;
        $prefix = $validated['prefix'] ?? 'CPN';

        $coupons = [];
        for ($i = 0; $i < $validated['count']; $i++) {
            $coupons[] = [
                'tenant_id' => $tenantId,
                'code' => $prefix . '_' . Str::upper(Str::random(10)),
                'type' => $validated['type'],
                'value' => $validated['value'],
                'min_amount' => $validated['min_amount'] ?? 0,
                'total_quantity' => $validated['total_quantity'],
                'used_quantity' => 0,
                'start_at' => $validated['start_at'],
                'end_at' => $validated['end_at'],
                'status' => $validated['status'],
                'applicable_products' => isset($validated['applicable_products']) ? json_encode($validated['applicable_products']) : null,
                'applicable_categories' => isset($validated['applicable_categories']) ? json_encode($validated['applicable_categories']) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Coupon::insert($coupons);

        return redirect()->route('soho.coupon.index')->with('success', "成功生成 {$validated['count']} 张优惠券");
    }

    /**
     * 生成唯一的优惠券码
     */
    private function generateCouponCode(): string
    {
        do {
            $code = 'CPN_' . Str::upper(Str::random(10));
        } while (Coupon::where('code', $code)->exists());

        return $code;
    }
}
