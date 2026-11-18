<?php

namespace App\Soho\Http\Controllers\System;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/**
 * 系统版本控制器
 * 展示系统版本信息、运行环境、依赖包版本等
 * 只读信息页面，无需数据库表
 */
class SystemVersionController extends Controller
{
    /**
     * 显示系统版本信息
     */
    public function index()
    {
        // 收集系统环境信息
        $systemInfo = $this->collectSystemInfo();
        // 收集模块信息
        $modules = $this->collectModules();
        // 收集更新日志
        $changelog = $this->collectChangelog();

        return view('system.version.index', compact('systemInfo', 'modules', 'changelog'));
    }

    /**
     * 收集系统环境信息
     */
    private function collectSystemInfo(): array
    {
        // 数据库连接信息
        $dbDriver = config('database.default');
        $dbConnection = config("database.connections.{$dbDriver}");

        return [
            'system_name' => config('app.name', '多平台电商店铺统一管理系统'),
            'version' => 'V2.0.0',
            'release_date' => '2026-07-31',
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'CLI',
            'db_driver' => ucfirst($dbDriver),
            'db_version' => $this->getDbVersion($dbDriver),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'env' => config('app.env'),
            'debug' => config('app.debug') ? '开启' : '关闭',
            'php_sapi' => PHP_SAPI,
            'max_execution_time' => ini_get('max_execution_time') . ' 秒',
            'max_memory' => ini_get('memory_limit'),
            'upload_max' => ini_get('upload_max_filesize'),
        ];
    }

    /**
     * 获取数据库版本
     */
    private function getDbVersion(string $driver): string
    {
        try {
            switch ($driver) {
                case 'sqlite':
                    return 'SQLite ' . (DB::selectOne('SELECT sqlite_version() AS version')->version ?? '');
                case 'mysql':
                    $result = DB::selectOne('SELECT VERSION() AS version');
                    return 'MySQL ' . ($result->version ?? '');
                case 'pgsql':
                    $result = DB::selectOne('SELECT VERSION() AS version');
                    return $result->version ?? 'PostgreSQL';
                default:
                    return ucfirst($driver);
            }
        } catch (\Exception $e) {
            return ucfirst($driver) . ' (版本未知)';
        }
    }

    /**
     * 收集已安装模块信息
     */
    private function collectModules(): array
    {
        // 系统功能模块清单
        return [
            ['name' => '用户与权限管理', 'version' => '1.0', 'status' => 'stable', 'desc' => '用户、角色、权限、部门管理'],
            ['name' => '店铺与平台对接', 'version' => '1.0', 'status' => 'stable', 'desc' => '多平台店铺授权与管理'],
            ['name' => '商品中心', 'version' => '1.0', 'status' => 'stable', 'desc' => 'SPU/SKU、商品发布与审核'],
            ['name' => '订单中心', 'version' => '1.0', 'status' => 'stable', 'desc' => '订单同步、履约与拆合单'],
            ['name' => '仓储中心', 'version' => '1.0', 'status' => 'stable', 'desc' => '库区库位、库存与盘点'],
            ['name' => '价格中心', 'version' => '0.9', 'status' => 'beta', 'desc' => '价格规则与调价管理'],
            ['name' => '履约作业', 'version' => '0.9', 'status' => 'beta', 'desc' => '拣货、复核、打包作业'],
            ['name' => '采购中心', 'version' => '0.9', 'status' => 'beta', 'desc' => '采购计划、订单与供应商'],
            ['name' => '财务中心', 'version' => '1.0', 'status' => 'stable', 'desc' => '退款、结算与对账'],
            ['name' => '营销中心', 'version' => '0.8', 'status' => 'alpha', 'desc' => '促销活动管理'],
            ['name' => '安全审计', 'version' => '1.0', 'status' => 'stable', 'desc' => '安全策略与审计日志'],
            ['name' => '系统管理', 'version' => '1.0', 'status' => 'stable', 'desc' => '系统参数、功能开关、告警'],
        ];
    }

    /**
     * 收集更新日志
     */
    private function collectChangelog(): array
    {
        return [
            [
                'version' => 'V2.0.0',
                'date' => '2026-07-31',
                'type' => 'major',
                'changes' => [
                    '新增价格中心、履约作业、采购中心、安全审计菜单分组',
                    '完成 26 个角色的侧边栏菜单权限隔离（V3.1 方案）',
                    '新增系统参数、功能开关、系统告警、通知模板、文件存储模块',
                    '新增操作日志中间件，实现用户操作可追溯',
                ],
            ],
            [
                'version' => 'V1.5.0',
                'date' => '2026-07-20',
                'type' => 'minor',
                'changes' => [
                    '完成店铺、商品、订单核心业务模块',
                    '实现多平台对接（Shopee、Lazada、TikTok、Tokopedia）',
                    '新增仪表盘与统计报表',
                ],
            ],
            [
                'version' => 'V1.0.0',
                'date' => '2026-07-01',
                'type' => 'major',
                'changes' => [
                    '项目初始版本发布',
                    '搭建 Laravel 12 + Bootstrap 5 + ECharts 技术框架',
                    '完成用户认证与基础 RBAC 权限体系',
                ],
            ],
        ];
    }
}
