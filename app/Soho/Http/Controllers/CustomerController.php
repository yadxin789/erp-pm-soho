<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\Customer;
use App\Soho\Models\CustomerGroup;
use App\Soho\Models\PlatformOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * 客户列表 - 支持搜索、分组筛选、分页
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $query = Customer::with(['group']);

        // 关键词搜索：姓名、邮箱、手机号
        $query->when($request->filled('search'), function ($q) use ($request) {
            $keyword = $request->search;
            $q->where(function ($subQuery) use ($keyword) {
                $subQuery->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        });

        // 按分组筛选
        $query->when($request->filled('group_id'), function ($q) use ($request) {
            $q->where('group_id', $request->group_id);
        });

        // 按状态筛选
        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        });

        // 按消费金额排序
        $query->when($request->filled('sort'), function ($q) use ($request) {
            switch ($request->sort) {
                case 'total_spent_desc':
                    $q->orderByDesc('total_spent');
                    break;
                case 'total_spent_asc':
                    $q->orderBy('total_spent');
                    break;
                case 'order_count_desc':
                    $q->orderByDesc('order_count');
                    break;
                case 'last_order_desc':
                    $q->orderByDesc('last_order_at');
                    break;
            }
        }, function ($q) {
            // 默认按创建时间倒序
            $q->latest();
        });

        $customers = $query->paginate(20)->withQueryString();

        // 获取所有分组供筛选使用
        $groups = CustomerGroup::orderBy('sort')->get();

        return view('customers.index', compact('customers', 'groups'));
    }

    /**
     * 创建客户表单页面
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $groups = CustomerGroup::orderBy('sort')->get();

        return view('customers.create', compact('groups'));
    }

    /**
     * 保存新客户
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:150|unique:soho_customers,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6|max:100',
            'group_id' => 'nullable|exists:soho_customer_groups,id',
            'status' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        // 设置默认值
        $validated['status'] = $validated['status'] ?? true;
        $validated['total_spent'] = 0;
        $validated['order_count'] = 0;

        $customer = Customer::create($validated);

        return redirect()
            ->route('soho.customer.show', $customer)
            ->with('success', '客户创建成功');
    }

    /**
     * 客户详情页面 - 含订单历史
     *
     * @param \App\Soho\Models\Customer $customer
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Customer $customer)
    {
        $customer->load(['group']);

        // 查询该客户的订单历史（通过姓名/邮箱/手机号匹配平台订单的买家信息）
        $orderHistory = PlatformOrder::where(function ($query) use ($customer) {
            if ($customer->name) {
                $query->where('buyer_nick', 'like', "%{$customer->name}%");
            }
            if ($customer->email) {
                $query->orWhere('platform_raw_data->buyer_email', $customer->email);
            }
        })
            ->orderByDesc('order_created_at')
            ->limit(20)
            ->get();

        // 统计客户消费数据
        $stats = [
            'total_spent' => $customer->total_spent ?? 0,
            'order_count' => $customer->order_count ?? 0,
            'last_order_at' => $customer->last_order_at,
        ];

        return view('customers.show', compact('customer', 'orderHistory', 'stats'));
    }

    /**
     * 编辑客户表单页面
     *
     * @param \App\Soho\Models\Customer $customer
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Customer $customer)
    {
        $groups = CustomerGroup::orderBy('sort')->get();

        return view('customers.edit', compact('customer', 'groups'));
    }

    /**
     * 更新客户信息
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Soho\Models\Customer $customer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:150|unique:soho_customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6|max:100',
            'group_id' => 'nullable|exists:soho_customer_groups,id',
            'status' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        // 如果密码为空则不更新
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $customer->update($validated);

        return redirect()
            ->route('soho.customer.show', $customer)
            ->with('success', '客户信息更新成功');
    }

    /**
     * 删除客户
     *
     * @param \App\Soho\Models\Customer $customer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Customer $customer)
    {
        // 检查是否有订单历史，若有则不允许删除
        $hasOrders = PlatformOrder::where('buyer_nick', 'like', "%{$customer->name}%")->exists();

        if ($hasOrders) {
            return back()->with('error', '该客户存在订单记录，无法删除');
        }

        $customer->delete();

        return redirect()
            ->route('soho.customer.index')
            ->with('success', '客户已删除');
    }

    /**
     * 客户分组管理 - 展示所有分组及其客户数量
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function groups(Request $request)
    {
        $query = CustomerGroup::withCount('customers');

        // 搜索分组
        $query->when($request->filled('search'), function ($q) use ($request) {
            $keyword = $request->search;
            $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('code', 'like', "%{$keyword}%");
        });

        $groups = $query->orderBy('sort')->get();

        return view('customers.groups', compact('groups'));
    }

    /**
     * 分配客户到分组
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Soho\Models\Customer $customer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assignGroup(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'group_id' => 'required|exists:soho_customer_groups,id',
        ]);

        $customer->update([
            'group_id' => $validated['group_id'],
        ]);

        $group = CustomerGroup::findOrFail($validated['group_id']);

        return back()->with('success', "已将客户「{$customer->name}」分配到分组「{$group->name}」");
    }

    /**
     * 新建客户分组
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function groupStore(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:50',
            'code'   => 'nullable|string|max:30|unique:soho_customer_groups,code',
            'color'  => 'nullable|string|max:20',
            'sort'   => 'nullable|integer',
            'remark' => 'nullable|string|max:500',
        ]);

        CustomerGroup::create($validated);

        return redirect()
            ->route('soho.customer.groups')
            ->with('success', '分组创建成功');
    }

    /**
     * 更新客户分组
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Soho\Models\CustomerGroup $group
     * @return \Illuminate\Http\RedirectResponse
     */
    public function groupUpdate(Request $request, CustomerGroup $group)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:50',
            'code'   => 'nullable|string|max:30|unique:soho_customer_groups,code,' . $group->id,
            'color'  => 'nullable|string|max:20',
            'sort'   => 'nullable|integer',
            'remark' => 'nullable|string|max:500',
        ]);

        $group->update($validated);

        return redirect()
            ->route('soho.customer.groups')
            ->with('success', '分组更新成功');
    }

    /**
     * 删除客户分组
     *
     * @param \App\Soho\Models\CustomerGroup $group
     * @return \Illuminate\Http\RedirectResponse
     */
    public function groupDestroy(CustomerGroup $group)
    {
        // 将该分组下的客户设为未分组
        Customer::where('group_id', $group->id)->update(['group_id' => null]);

        $group->delete();

        return redirect()
            ->route('soho.customer.groups')
            ->with('success', '分组删除成功');
    }
}