<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\ApprovalFlow;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApprovalFlowController extends Controller
{
    /**
     * 审批流列表
     */
    public function index(Request $request)
    {
        $approvalFlows = ApprovalFlow::with(['tenant', 'creator'])
            ->when($request->search, function($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%")
                    ->orWhere('code', 'like', "%{$request->search}%");
            })
            ->when($request->type, function($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->status !== null && $request->status !== '', function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('approval-flows.index', compact('approvalFlows'));
    }

    /**
     * 创建审批流
     */
    public function create()
    {
        return view('approval-flows.create');
    }

    /**
     * 保存新审批流
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:soho_approval_flows,code',
            'type' => 'required|string|max:50',
            'scope' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'priority' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $validated['status'] = $validated['status'] ?? true;
        $validated['priority'] = $validated['priority'] ?? 0;
        $validated['tenant_id'] = auth()->user()->tenant_id ?? null;
        $validated['created_by'] = auth()->id();

        ApprovalFlow::create($validated);

        return redirect()->route('approval_flows.index')->with('success', '审批流创建成功');
    }

    /**
     * 编辑审批流
     */
    public function edit(ApprovalFlow $approvalFlow)
    {
        return view('approval-flows.edit', compact('approvalFlow'));
    }

    /**
     * 更新审批流
     */
    public function update(Request $request, ApprovalFlow $approvalFlow)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:100', Rule::unique('soho_approval_flows', 'code')->ignore($approvalFlow->id)],
            'type' => 'required|string|max:50',
            'scope' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'priority' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $approvalFlow->update($validated);

        return redirect()->route('approval_flows.index')->with('success', '审批流更新成功');
    }
}