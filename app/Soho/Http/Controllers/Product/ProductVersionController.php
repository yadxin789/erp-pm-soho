<?php

namespace App\Soho\Http\Controllers\Product;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 商品版本控制器
 * 展示商品版本历史，支持按版本号、状态、日期区间筛选
 */
class ProductVersionController extends Controller
{
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于数据隔离
        $tenantId = Auth::user()->tenant_id;

        $table = 'soho_product_versions';
        $hasProductName = Schema::hasTable('soho_products') && Schema::hasColumn('soho_products', 'name');

        $query = DB::table($table)
            ->when($tenantId, function ($q) use ($tenantId, $table) {
                $q->where($table.'.tenant_id', $tenantId);
            })
            ->when($search = $request->input('search'), function ($q) use ($search, $table) {
                $q->where(function ($sub) use ($search, $table) {
                    $sub->where($table.'.version_no', 'like', "%{$search}%")
                        ->orWhere($table.'.title', 'like', "%{$search}%")
                        ->orWhere($table.'.description', 'like', "%{$search}%");
                });
            })
            ->when($versionNo = $request->input('version_no'), function ($q) use ($versionNo, $table) {
                $q->where($table.'.version_no', 'like', "%{$versionNo}%");
            })
            ->when($status = $request->input('status'), function ($q) use ($status, $table) {
                $q->where($table.'.status', $status);
            })
            ->when($dateFrom = $request->input('date_from'), function ($q) use ($dateFrom, $table) {
                $q->where($table.'.created_at', '>=', $dateFrom.' 00:00:00');
            })
            ->when($dateTo = $request->input('date_to'), function ($q) use ($dateTo, $table) {
                $q->where($table.'.created_at', '<=', $dateTo.' 23:59:59');
            });

        if ($hasProductName) {
            $query->leftJoin('soho_products', 'soho_products.id', '=', $table.'.product_id')
                ->addSelect($table.'.*', 'soho_products.name as product_name', 'soho_products.product_code as product_code');
        } else {
            $query->addSelect($table.'.*', DB::raw('null as product_name'), DB::raw('null as product_code'));
        }

        $versions = $query->orderByDesc($table.'.created_at')->paginate(20)->withQueryString();

        // 状态字典
        $changeTypeOptions = [
            'draft' => '草稿',
            'published' => '已发布',
            'offline' => '已下架',
        ];

        // 统计卡片数据
        $stats = [
            'total' => DB::table($table)->when($tenantId, function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })->count(),
            'today' => DB::table($table)->when($tenantId, function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })->whereDate('created_at', now()->toDateString())->count(),
        ];

        return view('soho.product.versions.index', compact('versions', 'changeTypeOptions', 'stats'));
    }
}
