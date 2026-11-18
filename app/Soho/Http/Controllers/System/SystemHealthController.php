<?php

namespace App\Soho\Http\Controllers\System;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * 系统健康检查控制器
 * 检查系统各组件运行状态：数据库、缓存、队列、存储、磁盘、PHP环境等
 * 只读监控页面，提供系统整体健康度评分
 */
class SystemHealthController extends Controller
{
    /**
     * 显示系统健康状态
     */
    public function index()
    {
        // 执行各项健康检查
        $checks = $this->runHealthChecks();
        // 计算整体健康度
        $healthScore = $this->calculateHealthScore($checks);
        // 系统资源信息
        $resources = $this->getSystemResources();

        return view('system.health.index', compact('checks', 'healthScore', 'resources'));
    }

    /**
     * 运行所有健康检查
     */
    private function runHealthChecks(): array
    {
        return [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
            'log_dir' => $this->checkLogDirectory(),
            'php_extensions' => $this->checkPhpExtensions(),
        ];
    }

    /**
     * 检查数据库连接
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return [
                'name' => '数据库连接',
                'status' => 'healthy',
                'message' => '数据库连接正常',
                'detail' => config('database.default') . ' · ' . DB::connection()->getDriverName(),
            ];
        } catch (\Exception $e) {
            return [
                'name' => '数据库连接',
                'status' => 'critical',
                'message' => '数据库连接失败',
                'detail' => $e->getMessage(),
            ];
        }
    }

    /**
     * 检查缓存服务
     */
    private function checkCache(): array
    {
        try {
            Cache::put('health_check_test', 'ok', 10);
            $value = Cache::get('health_check_test');
            Cache::forget('health_check_test');

            if ($value === 'ok') {
                return [
                    'name' => '缓存服务',
                    'status' => 'healthy',
                    'message' => '缓存读写正常',
                    'detail' => '驱动：' . config('cache.default'),
                ];
            }
            return [
                'name' => '缓存服务',
                'status' => 'warning',
                'message' => '缓存读写异常',
                'detail' => '写入值与读取值不一致',
            ];
        } catch (\Exception $e) {
            return [
                'name' => '缓存服务',
                'status' => 'critical',
                'message' => '缓存服务不可用',
                'detail' => $e->getMessage(),
            ];
        }
    }

    /**
     * 检查文件存储
     */
    private function checkStorage(): array
    {
        try {
            $disk = Storage::disk(config('filesystems.default'));
            // 测试写入与删除
            $disk->put('health_check.txt', 'ok');
            $content = $disk->get('health_check.txt');
            $disk->delete('health_check.txt');

            if ($content === 'ok') {
                return [
                    'name' => '文件存储',
                    'status' => 'healthy',
                    'message' => '文件存储读写正常',
                    'detail' => '磁盘：' . config('filesystems.default'),
                ];
            }
            return [
                'name' => '文件存储',
                'status' => 'warning',
                'message' => '文件存储读写异常',
                'detail' => '内容不一致',
            ];
        } catch (\Exception $e) {
            return [
                'name' => '文件存储',
                'status' => 'critical',
                'message' => '文件存储不可用',
                'detail' => $e->getMessage(),
            ];
        }
    }

    /**
     * 检查队列服务
     */
    private function checkQueue(): array
    {
        try {
            // 检查 jobs 表是否可访问
            if (DB::getSchemaBuilder()->hasTable('jobs')) {
                $pending = DB::table('jobs')->count();
                return [
                    'name' => '队列服务',
                    'status' => 'healthy',
                    'message' => '队列服务正常',
                    'detail' => "连接：" . config('queue.default') . " · 待处理：{$pending}",
                ];
            }
            return [
                'name' => '队列服务',
                'status' => 'warning',
                'message' => '队列表不存在',
                'detail' => 'jobs 表未创建',
            ];
        } catch (\Exception $e) {
            return [
                'name' => '队列服务',
                'status' => 'critical',
                'message' => '队列服务异常',
                'detail' => $e->getMessage(),
            ];
        }
    }

    /**
     * 检查日志目录权限
     */
    private function checkLogDirectory(): array
    {
        $logPath = storage_path('logs');

        if (!is_dir($logPath)) {
            return [
                'name' => '日志目录',
                'status' => 'critical',
                'message' => '日志目录不存在',
                'detail' => $logPath,
            ];
        }

        if (!is_writable($logPath)) {
            return [
                'name' => '日志目录',
                'status' => 'critical',
                'message' => '日志目录不可写',
                'detail' => $logPath,
            ];
        }

        return [
            'name' => '日志目录',
            'status' => 'healthy',
            'message' => '日志目录可读写',
            'detail' => $logPath,
        ];
    }

    /**
     * 检查 PHP 扩展
     */
    private function checkPhpExtensions(): array
    {
        // Laravel 必需的 PHP 扩展
        $required = ['pdo', 'mbstring', 'openssl', 'tokenizer', 'json', 'ctype', 'curl'];
        $missing = [];

        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }

        if (empty($missing)) {
            return [
                'name' => 'PHP 扩展',
                'status' => 'healthy',
                'message' => '必需扩展已全部安装',
                'detail' => implode('、', $required),
            ];
        }

        return [
            'name' => 'PHP 扩展',
            'status' => 'critical',
            'message' => '缺少必需扩展',
            'detail' => '缺失：' . implode('、', $missing),
        ];
    }

    /**
     * 计算整体健康度评分
     */
    private function calculateHealthScore(array $checks): array
    {
        $total = count($checks);
        $healthy = 0;
        $warning = 0;

        foreach ($checks as $check) {
            if ($check['status'] === 'healthy') {
                $healthy++;
            } elseif ($check['status'] === 'warning') {
                $warning++;
            }
        }

        // 健康度 = 健康项 + 警告项*0.5
        $score = $total > 0 ? round(($healthy + $warning * 0.5) / $total * 100) : 0;

        // 整体状态判定
        $status = 'healthy';
        if ($score < 60) {
            $status = 'critical';
        } elseif ($score < 100) {
            $status = 'warning';
        }

        return [
            'score' => $score,
            'status' => $status,
            'healthy' => $healthy,
            'warning' => $warning,
            'critical' => $total - $healthy - $warning,
            'total' => $total,
        ];
    }

    /**
     * 获取系统资源信息
     */
    private function getSystemResources(): array
    {
        // 磁盘空间（项目所在盘）
        $diskTotal = @disk_total_space(base_path()) ?: 0;
        $diskFree = @disk_free_space(base_path()) ?: 0;
        $diskUsed = $diskTotal - $diskFree;

        return [
            'disk_total' => $this->formatBytes($diskTotal),
            'disk_used' => $this->formatBytes($diskUsed),
            'disk_free' => $this->formatBytes($diskFree),
            'disk_usage_percent' => $diskTotal > 0 ? round($diskUsed / $diskTotal * 100, 1) : 0,
            'memory_limit' => ini_get('memory_limit'),
            'memory_usage' => $this->formatBytes(memory_get_usage(true)),
            'memory_peak' => $this->formatBytes(memory_get_peak_usage(true)),
            'cpu_cores' => function_exists('sys_getloadavg') ? 'N/A' : 'N/A',
        ];
    }

    /**
     * 格式化字节数为可读字符串
     */
    private function formatBytes(float $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}
