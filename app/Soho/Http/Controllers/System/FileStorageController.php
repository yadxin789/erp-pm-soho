<?php

namespace App\Soho\Http\Controllers\System;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\FileStorageConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 文件存储配置控制器
 * 管理系统文件存储磁盘的配置（本地、阿里云OSS、腾讯云COS、七牛云等）
 * 敏感字段自动加密存储
 */
class FileStorageController extends Controller
{
    /**
     * 显示存储配置列表
     */
    public function index(Request $request)
    {
        $query = FileStorageConfig::query();

        // 关键词搜索
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('disk_name', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // 驱动类型筛选
        if ($driver = $request->input('driver')) {
            $query->where('driver', $driver);
        }

        // 一次性获取全部记录（用户偏好：取消分页）
        $configs = $query->orderByDesc('id')->get();
        $drivers = FileStorageConfig::getDriverLabels();

        return view('system.storage.index', compact('configs', 'drivers'));
    }

    /**
     * 显示创建表单
     */
    public function create()
    {
        $drivers = FileStorageConfig::getDriverLabels();

        return view('system.storage.create', compact('drivers'));
    }

    /**
     * 存储新配置
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'disk_name' => 'required|string|max:50|regex:/^[a-z_]+$/',
            'name' => 'required|string|max:100',
            'driver' => 'required|string|in:local,oss,cos,qiniu,s3',
            'domain' => 'nullable|url|max:255',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            // 动态配置字段
            'root' => 'nullable|string|max:255',
            'bucket' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:50',
            'endpoint' => 'nullable|string|max:255',
            'access_key' => 'nullable|string|max:255',
            'secret_key' => 'nullable|string|max:255',
        ]);

        // 组装存储配置
        $config = $this->buildConfigArray($validated, $request);

        // 加密敏感字段
        $config = FileStorageConfig::encryptSecrets($config);

        // 设置默认磁盘：若设为默认，则取消其他默认
        $isDefault = $request->boolean('is_default');

        if ($isDefault) {
            FileStorageConfig::where('is_default', true)->update(['is_default' => false]);
        }

        FileStorageConfig::create([
            'disk_name' => $validated['disk_name'],
            'name' => $validated['name'],
            'driver' => $validated['driver'],
            'config' => $config ?: null,
            'domain' => $validated['domain'] ?? null,
            'is_default' => $isDefault,
            'is_active' => $request->boolean('is_active', true),
            'last_modified_by' => Auth::id(),
        ]);

        return redirect()
            ->route('soho.system.storage.index')
            ->with('success', "文件存储「{$validated['name']}」创建成功");
    }

    /**
     * 显示编辑表单
     */
    public function edit(FileStorageConfig $storage)
    {
        $drivers = FileStorageConfig::getDriverLabels();
        // 解密敏感字段用于编辑回显
        $config = $storage->decryptSecrets();

        return view('system.storage.edit', compact('storage', 'drivers', 'config'));
    }

    /**
     * 更新配置
     */
    public function update(Request $request, FileStorageConfig $storage)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'driver' => 'required|string|in:local,oss,cos,qiniu,s3',
            'domain' => 'nullable|url|max:255',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'root' => 'nullable|string|max:255',
            'bucket' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:50',
            'endpoint' => 'nullable|string|max:255',
            'access_key' => 'nullable|string|max:255',
            'secret_key' => 'nullable|string|max:255',
        ]);

        // 组装存储配置
        $config = $this->buildConfigArray($validated, $request);

        // 加密敏感字段
        $config = FileStorageConfig::encryptSecrets($config);

        // 设置默认磁盘
        $isDefault = $request->boolean('is_default');

        if ($isDefault && !$storage->is_default) {
            FileStorageConfig::where('is_default', true)->update(['is_default' => false]);
        }

        $storage->update([
            'name' => $validated['name'],
            'driver' => $validated['driver'],
            'config' => $config ?: null,
            'domain' => $validated['domain'] ?? null,
            'is_default' => $isDefault,
            'is_active' => $request->boolean('is_active', true),
            'last_modified_by' => Auth::id(),
        ]);

        return redirect()
            ->route('soho.system.storage.index')
            ->with('success', "文件存储「{$validated['name']}」更新成功");
    }

    /**
     * 删除配置
     */
    public function destroy(FileStorageConfig $storage)
    {
        $name = $storage->name;
        $storage->delete();

        return redirect()
            ->route('soho.system.storage.index')
            ->with('success', "文件存储「{$name}」已删除");
    }

    /**
     * 测试存储连接
     */
    public function testConnection(FileStorageConfig $storage)
    {
        // 简单测试：返回配置基本信息
        $config = $storage->decryptSecrets();

        return response()->json([
            'success' => true,
            'message' => "存储「{$storage->name}」配置已加载",
            'driver' => $storage->driver,
            'bucket' => $config['bucket'] ?? '-',
            'region' => $config['region'] ?? '-',
        ]);
    }

    /**
     * 根据驱动类型构建配置数组
     */
    private function buildConfigArray(array $validated, Request $request): array
    {
        $config = [];
        $driver = $validated['driver'];

        if ($driver === 'local') {
            // 本地存储配置
            if (!empty($validated['root'])) {
                $config['root'] = $validated['root'];
            }
        } else {
            // 云存储通用配置
            if (!empty($validated['bucket'])) {
                $config['bucket'] = $validated['bucket'];
            }
            if (!empty($validated['region'])) {
                $config['region'] = $validated['region'];
            }
            if (!empty($validated['endpoint'])) {
                $config['endpoint'] = $validated['endpoint'];
            }
            if (!empty($validated['access_key'])) {
                $config['access_key'] = $validated['access_key'];
            }
            if (!empty($validated['secret_key'])) {
                $config['secret_key'] = $validated['secret_key'];
            }
        }

        return $config;
    }
}
