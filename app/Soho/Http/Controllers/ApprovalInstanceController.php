<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\ApprovalInstance;
use Illuminate\Http\Request;

class ApprovalInstanceController extends Controller
{
    /**
     * 审批实例列表
     */
    public function index(Request $request)
    {
        $approvalInstances = ApprovalInstance::with(['flow', 'submitter', 'approver', 'shop'])
            ->when($request->search, function($query) use ($request) {
                $query->where('title', 'like', "%{$request->search}%")
                    ->orWhere('biz_no', 'like', "%{$request->search}%");
            })
            ->when($request->status, function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->type, function($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('approval-instances.index', compact('approvalInstances'));
    }

    /**
     * 批准审批实例
     */
    public function approve(Request $request, ApprovalInstance $approvalInstance)
    {
        $validated = $request->validate([
            'final_remark' => 'nullable|string|max:500',
        ]);

        $approvalInstance->update([
            'status' => 'approved',
            'final_remark' => $validated['final_remark'] ?? null,
            'approved_by' => auth()->id(),
            'completed_at' => now(),
        ]);

        return back()->with('success', '审批已通过');
    }

    /**
     * 拒绝审批实例
     */
    public function reject(Request $request, ApprovalInstance $approvalInstance)
    {
        $validated = $request->validate([
            'final_remark' => 'nullable|string|max:500',
        ]);

        $approvalInstance->update([
            'status' => 'rejected',
            'final_remark' => $validated['final_remark'] ?? null,
            'approved_by' => auth()->id(),
            'completed_at' => now(),
        ]);

        return back()->with('success', '审批已拒绝');
    }
}