<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\ExceptionCase;
use Illuminate\Http\Request;

class ExceptionCaseController extends Controller
{
    /**
     * 异常案例列表
     */
    public function index(Request $request)
    {
        $exceptions = ExceptionCase::with(['tenant', 'shop', 'handler'])
            ->when($request->search, function($query) use ($request) {
                $query->where('exception_code', 'like', "%{$request->search}%")
                    ->orWhere('error_message', 'like', "%{$request->search}%");
            })
            ->when($request->severity, function($query) use ($request) {
                $query->where('severity', $request->severity);
            })
            ->when($request->status, function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->shop_id, function($query) use ($request) {
                $query->where('shop_id', $request->shop_id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('exceptions.index', compact('exceptions'));
    }

    /**
     * 查看异常详情
     */
    public function show(ExceptionCase $exceptionCase)
    {
        $exceptionCase->load(['tenant', 'shop', 'order', 'handler']);
        return view('exceptions.show', compact('exceptionCase'));
    }

    /**
     * 处理异常
     */
    public function handle(Request $request, ExceptionCase $exceptionCase)
    {
        $validated = $request->validate([
            'resolution_note' => 'required|string|max:500',
        ]);

        $exceptionCase->update([
            'status' => 'handling',
            'handler_id' => auth()->id(),
            'resolution_note' => $validated['resolution_note'],
        ]);

        return back()->with('success', '异常已开始处理');
    }

    /**
     * 分配异常处理人
     */
    public function assign(Request $request, ExceptionCase $exceptionCase)
    {
        $validated = $request->validate([
            'handler_id' => 'required|exists:users,id',
        ]);

        $exceptionCase->update([
            'handler_id' => $validated['handler_id'],
        ]);

        return back()->with('success', '异常已分配');
    }

    /**
     * 解决异常
     */
    public function resolve(Request $request, ExceptionCase $exceptionCase)
    {
        $validated = $request->validate([
            'resolution_note' => 'required|string|max:500',
        ]);

        $exceptionCase->update([
            'status' => 'resolved',
            'handler_id' => $exceptionCase->handler_id ?? auth()->id(),
            'resolution_note' => $validated['resolution_note'],
            'resolved_at' => now(),
        ]);

        return back()->with('success', '异常已解决');
    }

    /**
     * 忽略异常
     */
    public function ignore(Request $request, ExceptionCase $exceptionCase)
    {
        $validated = $request->validate([
            'resolution_note' => 'nullable|string|max:500',
        ]);

        $exceptionCase->update([
            'status' => 'ignored',
            'handler_id' => auth()->id(),
            'resolution_note' => $validated['resolution_note'] ?? '已忽略',
            'resolved_at' => now(),
        ]);

        return back()->with('success', '异常已忽略');
    }
}