<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\UserDataScope;
use Illuminate\Http\Request;

class DataScopeController extends Controller
{
    /**
     * 数据权限列表
     */
    public function index(Request $request)
    {
        $dataScopes = UserDataScope::with(['tenant', 'user', 'role'])
            ->when($request->search, function($query) use ($request) {
                $query->where('domain', 'like', "%{$request->search}%")
                    ->orWhere('action', 'like', "%{$request->search}%");
            })
            ->when($request->subject_type, function($query) use ($request) {
                $query->where('subject_type', $request->subject_type);
            })
            ->when($request->status !== null && $request->status !== '', function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('data-scopes.index', compact('dataScopes'));
    }

    /**
     * 创建数据权限
     */
    public function create()
    {
        return view('data-scopes.create');
    }

    /**
     * 保存新数据权限
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject_type' => 'required|string|max:50',
            'domain' => 'required|string|max:50',
            'action' => 'required|string|max:50',
            'scope_strategy' => 'required|string|max:50',
            'status' => 'boolean',
        ]);

        $validated['status'] = $validated['status'] ?? true;
        $validated['tenant_id'] = auth()->user()->tenant_id ?? null;

        UserDataScope::create($validated);

        return redirect()->route('data_scopes.index')->with('success', '数据权限创建成功');
    }

    /**
     * 编辑数据权限
     */
    public function edit(UserDataScope $dataScope)
    {
        return view('data-scopes.edit', compact('dataScope'));
    }

    /**
     * 更新数据权限
     */
    public function update(Request $request, UserDataScope $dataScope)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject_type' => 'required|string|max:50',
            'domain' => 'required|string|max:50',
            'action' => 'required|string|max:50',
            'scope_strategy' => 'required|string|max:50',
            'status' => 'boolean',
        ]);

        $dataScope->update($validated);

        return redirect()->route('data_scopes.index')->with('success', '数据权限更新成功');
    }
}