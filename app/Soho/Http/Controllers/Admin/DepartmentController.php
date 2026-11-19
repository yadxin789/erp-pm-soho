<?php

namespace App\Soho\Http\Controllers\Admin;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $departments = Department::with('parent')
            ->visibleTo('soho')
            ->when($request->search, function($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%");
            })
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        $departments = Department::visibleTo('soho')->whereNull('parent_id')->orderBy('name')->get();
        return view('admin.departments.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:departments',
            'parent_id' => 'nullable|exists:departments,id',
            'sort_order' => 'integer|min:0',
            'status' => 'boolean',
            'remark' => 'nullable|string|max:500',
        ]);

        $validated['module'] = 'soho';
        $validated['code'] = $this->nextCode();
        $validated['status'] = $validated['status'] ?? 1;

        Department::create($validated);

        return redirect()->route('soho.admin.departments.index')->with('success', '部门创建成功');
    }

    /** SOHO 部门编码延续 soho_dept_N 序列 */
    private function nextCode(): string
    {
        $max = Department::where('code', 'like', 'soho_dept_%')
            ->pluck('code')
            ->map(fn ($code) => (int) substr($code, strlen('soho_dept_')))
            ->max() ?? 0;

        return 'soho_dept_' . ($max + 1);
    }

    public function edit(Department $department)
    {
        $departments = Department::visibleTo('soho')
            ->whereNull('parent_id')
            ->where('id', '!=', $department->id)
            ->orderBy('name')
            ->get();
        return view('admin.departments.edit', compact('department', 'departments'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('departments')->ignore($department->id)],
            'parent_id' => 'nullable|exists:departments,id',
            'sort_order' => 'integer|min:0',
            'status' => 'boolean',
            'remark' => 'nullable|string|max:500',
        ]);

        $department->update($validated);

        return redirect()->route('soho.admin.departments.index')->with('success', '部门更新成功');
    }

    public function destroy(Department $department)
    {
        if ($department->users()->count() > 0) {
            return back()->with('error', '该部门下有用户，无法删除');
        }

        if ($department->children()->count() > 0) {
            return back()->with('error', '该部门有子部门，无法删除');
        }

        $department->delete();
        return redirect()->route('soho.admin.departments.index')->with('success', '部门删除成功');
    }
}