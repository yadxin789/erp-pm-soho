<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\Package;
use App\Soho\Models\ExpressCompany;
use App\Soho\Models\PlatformOrder;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * 包裹列表
     */
    public function index(Request $request)
    {
        $packages = Package::with(['order.shop', 'expressCompany'])
            ->when($request->search, function($query) use ($request) {
                $query->where('package_no', 'like', "%{$request->search}%")
                    ->orWhere('tracking_number', 'like', "%{$request->search}%");
            })
            ->when($request->status, function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->express_company_id, function($query) use ($request) {
                $query->where('express_company_id', $request->express_company_id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('package.index', compact('packages'));
    }

    /**
     * 创建包裹
     */
    public function create()
    {
        $orders = PlatformOrder::where('order_status', 'paid')
            ->whereDoesntHave('packages')
            ->orderBy('order_created_at', 'desc')
            ->get();
        $expressCompanies = ExpressCompany::where('status', true)->orderBy('sort_order')->get();

        return view('package.create', compact('orders', 'expressCompanies'));
    }

    /**
     * 保存包裹
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:soho_platform_orders,id',
            'express_company_id' => 'required|exists:soho_express_company,id',
            'tracking_number' => 'required|string|max:200|unique:soho_packages,tracking_number',
            'weight' => 'nullable|numeric|min:0',
            'remark' => 'nullable|string|max:500',
        ]);

        // 生成包裹编号
        $packageNo = 'PKG' . date('YmdHis') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        $package = Package::create([
            'package_no' => $packageNo,
            'order_id' => $request->order_id,
            'express_company_id' => $request->express_company_id,
            'tracking_number' => $request->tracking_number,
            'weight' => $request->weight,
            'status' => 'shipped',
            'ship_time' => now(),
            'operator_id' => auth()->id(),
            'remark' => $request->remark,
        ]);

        // 更新订单状态
        $order = PlatformOrder::find($request->order_id);
        if ($order) {
            $order->update([
                'order_status' => 'shipped',
                'ship_time' => now(),
            ]);
        }

        return redirect()->route('package.index')->with('success', '包裹创建成功');
    }

    /**
     * 编辑包裹
     */
    public function edit(Package $package)
    {
        $expressCompanies = ExpressCompany::where('status', true)->orderBy('sort_order')->get();

        return view('package.edit', compact('package', 'expressCompanies'));
    }

    /**
     * 更新包裹
     */
    public function update(Request $request, Package $package)
    {
        $request->validate([
            'express_company_id' => 'required|exists:soho_express_company,id',
            'tracking_number' => 'required|string|max:200|unique:soho_packages,tracking_number,' . $package->id,
            'weight' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,shipped,delivered,returned',
            'remark' => 'nullable|string|max:500',
        ]);

        $package->update($request->only([
            'express_company_id', 'tracking_number', 'weight', 'status', 'remark'
        ]));

        return redirect()->route('package.index')->with('success', '包裹信息已更新');
    }
}
