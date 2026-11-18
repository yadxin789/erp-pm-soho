<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * 获取默认头像列表
     */
    public function defaultAvatars()
    {
        $avatars = [];
        for ($i = 1; $i <= 5; $i++) {
            $avatars[] = [
                'name' => "defaults/avatar{$i}.svg",
                'url' => asset("avatars/defaults/avatar{$i}.svg"),
            ];
        }
        return response()->json(['avatars' => $avatars]);
    }

    /**
     * 上传用户自定义头像或选择默认头像
     */
    public function updateAvatar(Request $request)
    {
        $user = Auth::user();

        // 检查是否选择了默认头像
        if ($request->has('default_avatar')) {
            $request->validate([
                'default_avatar' => 'required|string|in:defaults/avatar1.svg,defaults/avatar2.svg,defaults/avatar3.svg,defaults/avatar4.svg,defaults/avatar5.svg',
            ]);

            // 如果之前是自定义头像，删除旧文件
            if ($user->avatar && !str_starts_with($user->avatar, 'defaults/')) {
                $oldPath = str_replace('/storage/', '', $user->avatar);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // 保存默认头像名称（如 defaults/avatar1.svg）
            $user->update(['avatar' => $request->default_avatar]);

            return redirect()->back()->with('success', '头像已更换为默认头像！');
        }

        // 上传自定义头像
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // 如果之前是自定义头像，删除旧文件（但保留默认头像切换的情况）
        if ($user->avatar && !str_starts_with($user->avatar, 'defaults/')) {
            $oldPath = str_replace('/storage/', '', $user->avatar);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        // 存储新头像到 public 磁盘的 avatars 目录
        $fileName = time() . '_' . $request->file('avatar')->getClientOriginalName();
        $request->file('avatar')->storeAs('avatars', $fileName, 'public');

        // 保存文件名（不含路径前缀，方便后续判断）
        $user->update(['avatar' => $fileName]);

        return redirect()->back()->with('success', '头像上传成功！');
    }

    /**
     * 为新用户随机分配一个默认头像
     */
    public static function assignDefaultAvatar(User $user): void
    {
        $randomIndex = rand(1, 5);
        $user->update(['avatar' => "defaults/avatar{$randomIndex}.svg"]);
    }
}
