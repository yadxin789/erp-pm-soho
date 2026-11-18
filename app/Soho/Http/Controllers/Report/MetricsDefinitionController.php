<?php

namespace App\Soho\Http\Controllers\Report;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 指标定义管理控制器
 * 展示各业务指标的名称、代码、计算公式、单位、描述，支持关键词搜索
 */
class MetricsDefinitionController extends Controller
{
    /**
     * 指标定义列表
     * 展示系统内置的业务指标定义，支持按指标分类筛选与关键词搜索
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 分类筛选参数
        $category = $request->input('category');

        // 关键词搜索参数
        $keyword = $request->input('keyword');

        // 分类选项
        $categoryOptions = [
            'sales'      => '销售指标',
            'order'      => '订单指标',
            'aftersale'  => '售后指标',
            'inventory'  => '库存指标',
            'logistics'  => '物流指标',
            'finance'    => '财务指标',
        ];

        // 查询 system_configs 表中的指标定义数据（如果存在 metrics 类型配置）
        $query = DB::table('soho_system_configs')
            ->where('tenant_id', $tenantId)
            ->where('type', 'metric')
            ->when($category, fn($q) => $q->where('category', $category))
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('name', 'like', "%{$keyword}%")
                        ->orWhere('code', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%");
                });
            });

        $items = (clone $query)->orderBy('category')->orderBy('code')->paginate(20);

        // 如果数据库中无指标定义，则使用系统默认指标作为展示数据
        if ($items->isEmpty()) {
            $defaultMetrics = $this->getDefaultMetrics();
            // 应用筛选
            $filtered = collect($defaultMetrics)
                ->when($category, fn($c) => $c->where('category', $category))
                ->when($keyword, function ($c) use ($keyword) {
                    return $c->filter(function ($item) use ($keyword) {
                        return stripos($item['name'], $keyword) !== false
                            || stripos($item['code'], $keyword) !== false
                            || stripos($item['description'], $keyword) !== false;
                    });
                })
                ->values();

            // 手动分页
            $page = $request->input('page', 1);
            $perPage = 20;
            $items = new \Illuminate\Pagination\LengthAwarePaginator(
                $filtered->forPage($page, $perPage),
                $filtered->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        // 汇总统计
        $stats = [
            'total'     => $items->total(),
            'sales'     => collect($this->getDefaultMetrics())->where('category', 'sales')->count(),
            'order'     => collect($this->getDefaultMetrics())->where('category', 'order')->count(),
            'aftersale' => collect($this->getDefaultMetrics())->where('category', 'aftersale')->count(),
        ];

        return view('report.data.metrics-definition', compact('items', 'stats', 'categoryOptions'));
    }

    /**
     * 获取系统默认指标定义
     * 当数据库未配置指标时，提供内置的标准指标定义
     */
    private function getDefaultMetrics(): array
    {
        return [
            ['id' => 1, 'category' => 'sales', 'name' => '销售额', 'code' => 'sales_amount', 'formula' => 'SUM(pay_amount)', 'unit' => '元', 'description' => '订单实际支付金额总和'],
            ['id' => 2, 'category' => 'sales', 'name' => '客单价', 'code' => 'avg_order_value', 'formula' => '销售额 / 订单数', 'unit' => '元', 'description' => '平均每个订单的销售金额'],
            ['id' => 3, 'category' => 'order', 'name' => '订单数', 'code' => 'order_count', 'formula' => 'COUNT(*)', 'unit' => '笔', 'description' => '订单总数'],
            ['id' => 4, 'category' => 'order', 'name' => '支付转化率', 'code' => 'conversion_rate', 'formula' => '已支付订单数 / 订单数 × 100%', 'unit' => '%', 'description' => '下单后完成支付的订单占比'],
            ['id' => 5, 'category' => 'order', 'name' => '订单完成率', 'code' => 'completion_rate', 'formula' => '已完成订单数 / 订单数 × 100%', 'unit' => '%', 'description' => '已完成状态的订单占比'],
            ['id' => 6, 'category' => 'aftersale', 'name' => '退款率', 'code' => 'refund_rate', 'formula' => '退款单数 / 订单数 × 100%', 'unit' => '%', 'description' => '发生退款的订单占比'],
            ['id' => 7, 'category' => 'aftersale', 'name' => '退货率', 'code' => 'return_rate', 'formula' => '退货单数 / 订单数 × 100%', 'unit' => '%', 'description' => '发生退货的订单占比'],
            ['id' => 8, 'category' => 'aftersale', 'name' => '投诉率', 'code' => 'dispute_rate', 'formula' => '争议单数 / 订单数 × 100%', 'unit' => '%', 'description' => '发生投诉争议的订单占比'],
            ['id' => 9, 'category' => 'inventory', 'name' => '库存周转率', 'code' => 'turnover_rate', 'formula' => '销售成本 / 平均库存', 'unit' => '次', 'description' => '一定时期内库存周转次数'],
            ['id' => 10, 'category' => 'inventory', 'name' => '库存周转天数', 'code' => 'turnover_days', 'formula' => '统计天数 / 周转率', 'unit' => '天', 'description' => '库存周转一次所需的天数'],
            ['id' => 11, 'category' => 'logistics', 'name' => '发货及时率', 'code' => 'ship_on_time_rate', 'formula' => '24小时内发货数 / 已发货数 × 100%', 'unit' => '%', 'description' => '下单后24小时内完成发货的订单占比'],
            ['id' => 12, 'category' => 'logistics', 'name' => '平均发货时长', 'code' => 'avg_ship_hours', 'formula' => 'AVG(发货时间 - 下单时间)', 'unit' => '小时', 'description' => '从下单到发货的平均时长'],
            ['id' => 13, 'category' => 'finance', 'name' => '毛利率', 'code' => 'gross_profit_rate', 'formula' => '(销售额 - 销售成本) / 销售额 × 100%', 'unit' => '%', 'description' => '销售收入减去销售成本的毛利占比'],
            ['id' => 14, 'category' => 'finance', 'name' => '净利润', 'code' => 'net_profit', 'formula' => '销售额 - 成本 - 费用', 'unit' => '元', 'description' => '扣除所有成本费用后的利润'],
        ];
    }
}
