<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\ExportTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExportTaskController extends Controller
{
    /**
     * 导出任务列表
     */
    public function index(Request $request)
    {
        $exportTasks = ExportTask::with(['tenant', 'operator'])
            ->when($request->task_type, function ($query) use ($request) {
                $query->where('task_type', $request->task_type);
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->search, function ($query) use ($request) {
                $query->where('task_no', 'like', "%{$request->search}%")
                    ->orWhere('task_type', 'like', "%{$request->search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('export-tasks.index', compact('exportTasks'));
    }

    /**
     * 创建导出任务
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'task_type' => 'required|string|max:50',
            'filters' => 'nullable|string',
        ]);

        // 构建导出参数（包含筛选条件和导出格式）
        $params = [];
        if (!empty($validated['filters'])) {
            $decoded = json_decode($validated['filters'], true);
            $params['filters'] = is_array($decoded) ? $decoded : [];
        }

        ExportTask::create([
            'task_no' => 'EXP' . date('YmdHis') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'task_type' => $validated['task_type'],
            'params' => $params,
            'status' => 'pending',
            'tenant_id' => auth()->user()->tenant_id ?? null,
            'operator_id' => auth()->id(),
            'expires_at' => now()->addDays(7),
        ]);

        return redirect()->route('export_tasks.index')->with('success', '导出任务已创建');
    }

    /**
     * 下载导出文件
     */
    public function download(ExportTask $exportTask)
    {
        if ($exportTask->status !== 'completed') {
            return back()->with('error', '导出任务尚未完成');
        }

        if (!$exportTask->file_path || !Storage::disk('local')->exists($exportTask->file_path)) {
            return back()->with('error', '导出文件不存在或已过期');
        }

        return Storage::download($exportTask->file_path);
    }
}