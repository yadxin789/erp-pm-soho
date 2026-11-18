<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\ChatClearState;
use App\Soho\Models\ChatMessage;
use App\Soho\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    /**
     * 聊天室首页
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();

        // 如果是 AJAX 请求，返回 JSON 格式的群聊消息
        if ($request->ajax()) {
            $messages = $this->getGroupMessages($currentUser);

            return response()->json([
                'success' => true,
                'messages' => $this->formatMessages($messages),
            ]);
        }

        // 群聊消息（过滤已清空的）
        $messages = $this->getGroupMessages($currentUser)->reverse();

        // 可聊天用户列表（排除自己）
        $users = User::where('id', '!=', $currentUser->id)
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'avatar']);

        // 最近私聊会话
        $conversations = $this->getRecentConversations($currentUser);

        // 表情列表
        $emojis = $this->getEmojis();

        return view('chat.index', compact('messages', 'users', 'conversations', 'emojis'));
    }

    /**
     * 获取群聊消息（过滤当前用户已清空时间点之前的消息）
     */
    private function getGroupMessages($currentUser)
    {
        $query = ChatMessage::with('user')
            ->group()
            ->orderBy('created_at', 'asc')
            ->limit(100);

        // 查询当前用户的群聊清空状态，只显示清空时间之后的消息
        $clearState = ChatClearState::where('user_id', $currentUser->id)
            ->where('chat_type', 'group')
            ->first();
        if ($clearState) {
            $query->where('created_at', '>', $clearState->cleared_at);
        }

        return $query->get();
    }

    /**
     * 发送消息（支持文字和文件）
     */
    public function send(Request $request)
    {
        $request->validate([
            'content' => 'nullable|string|max:1000',
            'receiver_id' => 'nullable|integer|exists:users,id',
            'is_private' => 'required|boolean',
            'file' => 'nullable|file|max:20480', // 最大 20MB
        ]);

        // 文字和文件不能同时为空
        if (empty($request->content) && !$request->hasFile('file')) {
            return response()->json(['error' => '消息内容或文件不能同时为空'], 422);
        }

        $data = [
            'user_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'is_private' => $request->is_private,
            'content' => $request->content ?? '',
        ];

        // 处理文件上传
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_path'] = $file->store('chat_files', 'public');
        }

        $message = ChatMessage::create($data);
        $message->load('user');

        return response()->json([
            'success' => true,
            'message' => $this->formatMessage($message),
        ]);
    }

    /**
     * 获取与指定用户的私聊记录（过滤当前用户已清空时间点之前的消息）
     */
    public function privateMessages($userId)
    {
        $currentUser = Auth::user();

        $query = ChatMessage::with('user')
            ->between($currentUser->id, $userId)
            ->orderBy('created_at', 'asc');

        // 查询当前用户与该用户的私聊清空状态，只显示清空时间之后的消息
        $clearState = ChatClearState::where('user_id', $currentUser->id)
            ->where('chat_type', 'private')
            ->where('conversation_key', ChatClearState::privateConversationKey($userId))
            ->first();
        if ($clearState) {
            $query->where('created_at', '>', $clearState->cleared_at);
        }

        $messages = $query->get();

        return response()->json([
            'success' => true,
            'messages' => $this->formatMessages($messages),
        ]);
    }

    /**
     * 获取最近的私聊会话列表
     */
    public function conversations()
    {
        $currentUser = Auth::user();
        $conversations = $this->getRecentConversations($currentUser);

        return response()->json([
            'success' => true,
            'conversations' => $conversations,
        ]);
    }

    /**
     * 清空当前用户的群聊留言板
     * 只清空当前用户的显示记录，不影响其他用户
     */
    public function clearGroup(Request $request)
    {
        $currentUser = Auth::user();

        // 创建或更新当前用户的群聊清空状态记录
        ChatClearState::updateOrCreate(
            [
                'user_id' => $currentUser->id,
                'chat_type' => 'group',
                'conversation_key' => null,
            ],
            [
                'other_user_id' => null,
                'cleared_at' => now(),
            ]
        );

        // 根据请求类型返回
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => '已清空你的群聊留言板',
            ]);
        }

        return redirect()->route('soho.chat.index')->with('success', '已清空你的群聊留言板');
    }

    /**
     * 清空当前用户与指定用户的私聊留言板
     * 只清空当前用户的显示记录，不影响对方
     */
    public function clearPrivate(Request $request, $userId)
    {
        $currentUser = Auth::user();

        // 验证对方用户存在
        $targetUser = User::findOrFail($userId);

        // 创建或更新当前用户与该用户的私聊清空状态记录
        ChatClearState::updateOrCreate(
            [
                'user_id' => $currentUser->id,
                'chat_type' => 'private',
                'conversation_key' => ChatClearState::privateConversationKey($userId),
            ],
            [
                'other_user_id' => $userId,
                'cleared_at' => now(),
            ]
        );

        // 根据请求类型返回
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "已清空你与 {$targetUser->name} 的私聊留言板",
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => '已清空你的私聊留言板',
        ]);
    }

    /**
     * 删除消息（仅允许删除自己发送的消息）
     */
    public function destroy(ChatMessage $message)
    {
        // 权限检查：只能删除自己发送的消息
        if ($message->user_id != Auth::id()) {
            return response()->json(['error' => '无权删除此消息'], 403);
        }

        // 删除关联的文件
        if ($message->file_path) {
            Storage::disk('public')->delete($message->file_path);
        }

        $message->delete();

        return response()->json(['success' => true]);
    }

    /**
     * 格式化消息列表
     */
    private function formatMessages($messages): array
    {
        return $messages->map(function ($msg) {
            return $this->formatMessage($msg);
        })->toArray();
    }

    /**
     * 格式化单条消息
     */
    private function formatMessage($message): array
    {
        $user = $message->user;
        $avatarUrl = $user ? $user->avatar_url : null;

        return [
            'id' => $message->id,
            'content' => $message->content,
            'user_id' => $message->user_id,
            'username' => $user ? $user->name : '未知',
            'avatar' => $avatarUrl,
            'is_my_message' => $message->user_id == Auth::id(),
            'file_path' => $message->file_path ? asset('storage/' . $message->file_path) : null,
            'file_name' => $message->file_name,
            'created_at' => $message->created_at->format('H:i'),
            'full_time' => $message->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * 获取最近的私聊会话列表
     */
    private function getRecentConversations($currentUser): array
    {
        $privateMessages = ChatMessage::private()
            ->where(function ($q) use ($currentUser) {
                $q->where('user_id', $currentUser->id)
                    ->orWhere('receiver_id', $currentUser->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $conversations = [];
        $seen = [];
        foreach ($privateMessages as $message) {
            $otherUserId = $message->user_id != $currentUser->id ? $message->user_id : $message->receiver_id;
            if (!in_array($otherUserId, $seen) && $otherUserId) {
                $seen[] = $otherUserId;
                $user = User::find($otherUserId);
                if ($user) {
                    // 检查当前用户与该对方的私聊清空状态
                    $clearState = ChatClearState::where('user_id', $currentUser->id)
                        ->where('chat_type', 'private')
                        ->where('conversation_key', ChatClearState::privateConversationKey($otherUserId))
                        ->first();

                    // 如果该消息在清空时间之前，跳过（寻找下一条该会话的消息）
                    if ($clearState && $message->created_at <= $clearState->cleared_at) {
                        continue;
                    }

                    $conversations[] = [
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'avatar' => $user->avatar_url,
                        ],
                        'last_message' => $message->content ?: '[文件] ' . $message->file_name,
                        'last_time' => $message->created_at->format('H:i'),
                    ];
                }
            }
        }

        return $conversations;
    }

    /**
     * 获取表情列表
     */
    private function getEmojis(): array
    {
        return [
            '😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂',
            '🙂', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗',
            '😚', '😙', '🥲', '😋', '😛', '😜', '🤪', '😝',
            '🤑', '🤗', '🤭', '🤫', '🤔', '🤐', '🤨', '😐',
            '😑', '😶', '😏', '😒', '🙄', '😬', '🤥', '😌',
            '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕', '🤢',
            '👍', '👎', '👌', '✌️', '🤞', '🤟', '🤘', '🤙',
            '👈', '👉', '👆', '👇', '☝️', '👋', '🤚', '🖐️',
            '💪', '❤️', '💔', '💕', '💖', '💗', '💯', '🔥',
        ];
    }
}
