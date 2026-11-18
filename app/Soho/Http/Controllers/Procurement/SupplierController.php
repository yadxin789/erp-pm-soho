<?php

namespace App\Soho\Http\Controllers\Procurement;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 供应商管理控制器
 * 用于管理供应商的列表展示与筛选
 */
class SupplierController extends Controller
{
    /**
     * 供应商列表
     * 支持按名称、等级筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $suppliers = DB::table('soho_suppliers')
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($level = $request->input('level'), function ($query) use ($level) {
                $query->where('level', $level);
            })
            ->when($search = $request->input('search'), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('contact_person', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        // 统计数据
        $stats = [
            'total' => DB::table('soho_suppliers')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'active' => DB::table('soho_suppliers')->where('status', true)->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'level_a' => DB::table('soho_suppliers')->where('level', 'A')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            'pending' => DB::table('soho_suppliers')->where('level', 'B')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
        ];

        $levelOptions = [
            'A' => 'A级',
            'B' => 'B级',
            'C' => 'C级',
            'D' => 'D级',
        ];

        return view('procurement.suppliers.index', compact('suppliers', 'levelOptions', 'stats'));
    }
}