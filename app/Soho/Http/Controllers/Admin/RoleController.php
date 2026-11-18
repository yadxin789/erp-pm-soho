<?php

namespace App\Soho\Http\Controllers\Admin;

use App\Soho\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::with('permissions')
            ->when($request->search, function($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function($item) {
            return explode('.', $item->name)[0];
        });
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
        
        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('admin.roles.index')->with('success', '角色创建成功');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function($item) {
            return explode('.', $item->name)[0];
        });
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('success', '角色更新成功');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) {
            return back()->with('error', '该角色下有用户，无法删除');
        }

        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', '角色删除成功');
    }
}