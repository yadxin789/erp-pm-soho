<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>聊天室 - 多平台电商店铺统一管理系统</title>
    <link rel="stylesheet" href="{{ asset('assets/soho/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/soho/css/font-awesome.min.css') }}">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Microsoft YaHei', -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
        }
        .chat-container {
            height: calc(100vh - 56px);
            display: flex;
            background: #fff;
        }
        .chat-sidebar {
            width: 280px;
            border-right: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            background: #f8f9fa;
        }
        .chat-sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            background: #fff;
        }
        .chat-sidebar-header h5 {
            margin: 0;
            font-weight: 600;
            color: #1f2937;
        }
        .chat-tab {
            display: flex;
            border-bottom: 1px solid #e5e7eb;
            background: #fff;
        }
        .chat-tab-item {
            flex: 1;
            padding: 12px;
            text-align: center;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
            font-size: 0.875rem;
            color: #6b7280;
        }
        .chat-tab-item.active {
            border-bottom-color: #667eea;
            color: #667eea;
            font-weight: 500;
        }
        .chat-tab-item:hover {
            background: #f9fafb;
        }
        .chat-list {
            flex: 1;
            overflow-y: auto;
        }
        .chat-item {
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .chat-item:hover {
            background: #e9ecef;
        }
        .chat-item.active {
            background: #e3f2fd;
            border-left: 3px solid #1976d2;
        }
        .chat-item-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            flex-shrink: 0;
            font-size: 14px;
        }
        .chat-item-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        .chat-item-info {
            flex: 1;
            min-width: 0;
        }
        .chat-item-name {
            font-weight: 500;
            color: #1f2937;
            margin-bottom: 2px;
            font-size: 0.9rem;
        }
        .chat-item-preview {
            font-size: 0.8rem;
            color: #6b7280;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .chat-item-time {
            font-size: 0.7rem;
            color: #9ca3af;
        }
        .online-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            position: absolute;
            bottom: 0;
            right: 0;
            border: 2px solid #fff;
        }
        .online-dot.online {
            background-color: #4caf50;
        }
        .online-dot.offline {
            background-color: #bdbdbd;
        }
        .chat-item-avatar-wrap {
            position: relative;
        }
        .user-search {
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
            background: #fff;
        }
        .user-search input {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 8px 12px;
            outline: none;
            font-size: 0.85rem;
        }
        .user-search input:focus {
            border-color: #667eea;
        }
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .chat-header {
            padding: 16px 24px;
            border-bottom: 1px solid #e5e7eb;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .chat-header-title {
            font-weight: 600;
            color: #1f2937;
        }
        .chat-header-info {
            font-size: 0.85rem;
            color: #6b7280;
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            background: #f5f5f5;
        }
        /* 消息气泡样式 - 与参考目录一致 */
        .chat-message {
            display: flex;
            margin-bottom: 16px;
            align-items: flex-start;
        }
        .chat-message.other-message {
            flex-direction: row;
        }
        .chat-message.my-message {
            flex-direction: row-reverse;
        }
        .message-avatar {
            flex-shrink: 0;
        }
        .chat-message.other-message .message-avatar {
            margin-right: 8px;
        }
        .chat-message.my-message .message-avatar {
            margin-left: 8px;
        }
        .message-avatar img,
        .message-avatar .avatar-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        .message-avatar .avatar-placeholder {
            background: #9ca3af;
            font-size: 14px;
        }
        .message-content {
            max-width: 60%;
        }
        .message-info {
            display: flex;
            align-items: center;
            margin-bottom: 3px;
            font-size: 11px;
            color: #999;
        }
        .chat-message.other-message .message-info {
            justify-content: flex-start;
        }
        .chat-message.my-message .message-info {
            justify-content: flex-end;
        }
        .message-username {
            font-weight: 600;
            margin-right: 6px;
        }
        .message-time {
            font-size: 10px;
        }
        .message-bubble {
            padding: 8px 14px;
            border-radius: 8px;
            word-wrap: break-word;
            line-height: 1.5;
            position: relative;
            font-size: 14px;
        }
        .chat-message.other-message .message-bubble {
            background-color: #fff;
            border: 1px solid #e8e8e8;
            color: #1f2937;
        }
        .chat-message.my-message .message-bubble {
            background-color: #95ec69;
            border: 1px solid #86d65d;
            color: #1f2937;
        }
        /* 文件附件样式 */
        .file-attachment {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            background: rgba(0, 0, 0, 0.05);
            border-radius: 6px;
            margin-top: 4px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s ease;
            max-width: 250px;
        }
        .file-attachment:hover {
            background: rgba(0, 0, 0, 0.1);
        }
        .file-attachment i {
            color: #1976d2;
            margin-right: 8px;
        }
        .chat-message.my-message .file-attachment {
            background: rgba(255, 255, 255, 0.5);
        }
        .chat-message.my-message .file-attachment i {
            color: #1565c0;
        }
        .chat-input-area {
            padding: 16px 24px;
            border-top: 1px solid #e5e7eb;
            background: #fff;
            position: relative;
        }
        .chat-input-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .chat-input-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .btn-emoji,
        .btn-file {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        .btn-emoji {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-emoji:hover {
            background-color: #e0a800;
        }
        .btn-file {
            background-color: #17a2b8;
            color: white;
        }
        .btn-file:hover {
            background-color: #138496;
        }
        .btn-send {
            background-color: #667eea;
            color: white;
            border: none;
            padding: 6px 18px;
            border-radius: 6px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-send:hover {
            background-color: #5568d3;
        }
        .chat-input-box {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
        }
        .chat-input-box:focus-within {
            border-color: #667eea;
        }
        .file-preview-area {
            padding: 8px 12px;
            border-bottom: 1px solid #f3f4f6;
            display: none;
            align-items: center;
            font-size: 0.85rem;
        }
        .file-preview-area.show {
            display: flex;
        }
        .file-preview-area i {
            color: #1976d2;
            margin-right: 8px;
        }
        .file-preview-area .file-name {
            flex: 1;
            color: #1f2937;
        }
        .file-preview-area .btn-clear {
            background: none;
            border: none;
            color: #ef4444;
            cursor: pointer;
            padding: 0;
            font-size: 1rem;
        }
        .chat-input-box textarea {
            width: 100%;
            border: none;
            padding: 10px 14px;
            resize: none;
            outline: none;
            font-size: 0.9rem;
            min-height: 60px;
            max-height: 120px;
        }
        /* Emoji 面板 */
        .emoji-panel {
            background-color: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 10px;
            max-height: 180px;
            overflow-y: auto;
            position: absolute;
            bottom: 100%;
            left: 24px;
            right: 24px;
            z-index: 1000;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
            display: none;
            margin-bottom: 8px;
        }
        .emoji-panel.show {
            display: block;
        }
        .emoji-grid {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 4px;
        }
        .emoji-item {
            font-size: 20px;
            cursor: pointer;
            text-align: center;
            padding: 4px;
            border-radius: 4px;
            transition: background-color 0.15s;
        }
        .emoji-item:hover {
            background-color: #f0f0f0;
        }
        /* 滚动条样式 */
        .chat-list::-webkit-scrollbar,
        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }
        .chat-list::-webkit-scrollbar-track,
        .chat-messages::-webkit-scrollbar-track {
            background: #f1f3f4;
            border-radius: 3px;
        }
        .chat-list::-webkit-scrollbar-thumb,
        .chat-messages::-webkit-scrollbar-thumb {
            background: #ced4da;
            border-radius: 3px;
        }
        .chat-list::-webkit-scrollbar-thumb:hover,
        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: #adb5bd;
        }
        .empty-chat {
            text-align: center;
            color: #9ca3af;
            padding: 40px;
        }
        .empty-chat i {
            font-size: 3rem;
            margin-bottom: 16px;
        }
        .back-btn {
            color: #6b7280;
            text-decoration: none;
            margin-right: 16px;
            font-size: 0.9rem;
        }
        .back-btn:hover {
            color: #667eea;
        }
        /* 清空留言板按钮样式 */
        .btn-clear-board {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
            border: 1px solid #ef4444;
            background-color: #fff;
            color: #ef4444;
            transition: all 0.2s;
        }
        .btn-clear-board:hover {
            background-color: #ef4444;
            color: #fff;
        }
        .btn-clear-board:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        /* 响应式 */
        @media (max-width: 768px) {
            .chat-sidebar {
                width: 200px;
            }
            .message-content {
                max-width: 75%;
            }
        }
    </style>
</head>
<body>
    <!-- 顶部导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-light" style="background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="container-fluid">
            <a href="{{ route('soho.dashboard') }}" class="back-btn">
                <i class="fa fa-arrow-left"></i> 返回
            </a>
            <span class="navbar-brand" style="color: #667eea; font-weight: 600;">
                <i class="fa fa-comments me-2"></i>聊天室
            </span>
        </div>
    </nav>

    <div class="chat-container">
        <!-- 侧边栏 -->
        <div class="chat-sidebar">
            <div class="chat-sidebar-header">
                <h5>消息列表</h5>
            </div>
            <div class="chat-tab">
                <div class="chat-tab-item active" data-tab="group">群聊</div>
                <div class="chat-tab-item" data-tab="private">私聊</div>
            </div>
            <div class="user-search">
                <input type="text" id="userSearch" placeholder="搜索用户...">
            </div>
            <div class="chat-list" id="chatList">
                <!-- 动态填充 -->
            </div>
        </div>

        <!-- 主聊天区域 -->
        <div class="chat-main">
            <div class="chat-header">
                <div>
                    <div class="chat-header-title" id="chatTitle">群聊大厅</div>
                    <div class="chat-header-info">所有在线用户</div>
                </div>
                <!-- 清空当前用户留言板按钮 -->
                <button type="button" class="btn-clear-board" id="clearBoardBtn" onclick="clearMyBoard()" title="清空我的留言板（只影响你的显示，不影响其他人）">
                    <i class="fa fa-trash"></i> 清空我的留言板
                </button>
            </div>
            @if(session('success'))
                <div class="alert alert-success py-1 mb-0 rounded-0" style="font-size: 13px;">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger py-1 mb-0 rounded-0" style="font-size: 13px;">{{ session('error') }}</div>
            @endif

            <div class="chat-messages" id="chatMessages">
                @foreach($messages as $message)
                    @php
                        $isMyMessage = $message->user_id == auth()->id();
                    @endphp
                    <div class="chat-message {{ $isMyMessage ? 'my-message' : 'other-message' }}">
                        <div class="message-avatar">
                            @if($message->user && $message->user->avatar_url)
                                <img src="{{ $message->user->avatar_url }}" alt="{{ $message->user->name }}">
                            @else
                                <div class="avatar-placeholder">{{ mb_substr($message->user->name ?? '?', 0, 1) }}</div>
                            @endif
                        </div>
                        <div class="message-content">
                            <div class="message-info">
                                <span class="message-username">{{ $message->user->name ?? '未知' }}</span>
                                <span class="message-time">{{ $message->created_at->format('H:i') }}</span>
                            </div>
                            @if($message->content)
                                <div class="message-bubble">{{ $message->content }}</div>
                            @endif
                            @if($message->file_path)
                                <div class="file-attachment"
                                     data-file-path="{{ asset('assets/soho/storage/' . $message->file_path) }}"
                                     data-file-name="{{ $message->file_name }}"
                                     ondblclick="previewFile(this)"
                                     oncontextmenu="downloadFile(event, this)">
                                    <i class="fa fa-file"></i>
                                    <span>{{ $message->file_name }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
                @if($messages->isEmpty())
                    <div class="empty-chat">
                        <i class="fa fa-comments"></i>
                        <p>暂无消息，发送第一条消息吧！</p>
                    </div>
                @endif
            </div>

            <div class="chat-input-area">
                <!-- 表情面板 -->
                <div class="emoji-panel" id="emojiPanel">
                    <div class="emoji-grid">
                        @foreach($emojis as $emoji)
                            <span class="emoji-item" onclick="insertEmoji('{{ $emoji }}')">{{ $emoji }}</span>
                        @endforeach
                    </div>
                </div>

                <form id="chatForm" onsubmit="return sendMessage(event)">
                    <div class="chat-input-toolbar">
                        <div class="chat-input-actions">
                            <button type="button" class="btn-emoji" id="emojiBtn" title="表情">
                                <i class="fa fa-smile-o"></i> 表情
                            </button>
                            <label class="btn-file" title="发送文件" style="cursor: pointer;">
                                <i class="fa fa-paperclip"></i> 文件
                                <input type="file" id="fileInput" style="display: none;" onchange="handleFileSelect(event)">
                            </label>
                        </div>
                        <button type="submit" class="btn-send">
                            <i class="fa fa-paper-plane"></i> 发送
                        </button>
                    </div>
                    <div class="chat-input-box">
                        <div class="file-preview-area" id="filePreviewArea">
                            <i class="fa fa-file"></i>
                            <span class="file-name" id="filePreviewName"></span>
                            <button type="button" class="btn-clear" onclick="clearFile()">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                        <textarea id="messageInput" placeholder="输入消息..." maxlength="1000"></textarea>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/soho/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/soho/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        // ==================== 全局状态 ====================
        const currentUserId = {{ auth()->id() }};
        let currentTab = 'group';
        let currentReceiverId = null;
        let selectedFile = null;

        // ==================== 初始化 ====================
        document.addEventListener('DOMContentLoaded', function() {
            initChatList();
            setupEventListeners();
            scrollToBottom();
        });

        // ==================== 事件监听 ====================
        function setupEventListeners() {
            // Tab 切换
            document.querySelectorAll('.chat-tab-item').forEach(tab => {
                tab.addEventListener('click', function() {
                    document.querySelectorAll('.chat-tab-item').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    currentTab = this.dataset.tab;
                    currentReceiverId = null;
                    initChatList();
                    updateChatHeader();
                });
            });

            // 表情按钮
            const emojiBtn = document.getElementById('emojiBtn');
            const emojiPanel = document.getElementById('emojiPanel');
            if (emojiBtn && emojiPanel) {
                emojiBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    emojiPanel.classList.toggle('show');
                });
                document.addEventListener('click', function(e) {
                    if (!emojiBtn.contains(e.target) && !emojiPanel.contains(e.target)) {
                        emojiPanel.classList.remove('show');
                    }
                });
            }

            // 搜索
            document.getElementById('userSearch').addEventListener('input', function(e) {
                const keyword = e.target.value.toLowerCase();
                document.querySelectorAll('#chatList .chat-item').forEach(item => {
                    const name = item.querySelector('.chat-item-name')?.textContent.toLowerCase() || '';
                    item.style.display = name.includes(keyword) ? '' : 'none';
                });
            });
        }

        // ==================== 聊天列表 ====================
        function initChatList() {
            const chatList = document.getElementById('chatList');
            chatList.innerHTML = '';

            if (currentTab === 'group') {
                // 群聊入口
                chatList.innerHTML = `
                    <div class="chat-item active" data-type="group">
                        <div class="chat-item-avatar-wrap">
                            <div class="chat-item-avatar"><i class="fa fa-users"></i></div>
                        </div>
                        <div class="chat-item-info">
                            <div class="chat-item-name">群聊大厅</div>
                            <div class="chat-item-preview">所有成员参与的公共聊天</div>
                        </div>
                    </div>
                `;
            } else {
                // 私聊列表
                loadPrivateList();
            }

            // 绑定点击事件
            chatList.querySelectorAll('.chat-item').forEach(item => {
                item.addEventListener('click', function() {
                    document.querySelectorAll('.chat-item').forEach(i => i.classList.remove('active'));
                    this.classList.add('active');

                    const type = this.dataset.type;
                    const userId = this.dataset.userId;

                    if (type === 'group') {
                        switchToGroup();
                    } else if (userId) {
                        switchToPrivate(userId);
                    }
                });
            });
        }

        function loadPrivateList() {
            fetch('/chat/conversations')
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.conversations && data.conversations.length > 0) {
                        renderPrivateList(data.conversations);
                    } else {
                        renderUserList();
                    }
                })
                .catch(() => renderUserList());
        }

        function renderPrivateList(conversations) {
            const chatList = document.getElementById('chatList');
            let html = '';
            conversations.forEach(conv => {
                const avatarHtml = conv.user.avatar
                    ? `<img src="${escapeHtml(conv.user.avatar)}" alt="">`
                    : escapeHtml(conv.user.name.charAt(0));
                html += `
                    <div class="chat-item" data-type="private" data-user-id="${conv.user.id}">
                        <div class="chat-item-avatar-wrap">
                            <div class="chat-item-avatar">${avatarHtml}</div>
                        </div>
                        <div class="chat-item-info">
                            <div class="chat-item-name">${escapeHtml(conv.user.name)}</div>
                            <div class="chat-item-preview">${escapeHtml(conv.last_message)}</div>
                        </div>
                        <div class="chat-item-time">${escapeHtml(conv.last_time)}</div>
                    </div>
                `;
            });
            chatList.innerHTML = html;
        }

        function renderUserList() {
            const chatList = document.getElementById('chatList');
            const users = @json($users);
            let html = '';
            users.forEach(user => {
                const avatarHtml = user.avatar_url
                    ? `<img src="${escapeHtml(user.avatar_url)}" alt="">`
                    : escapeHtml(user.name.charAt(0));
                html += `
                    <div class="chat-item" data-type="private" data-user-id="${user.id}">
                        <div class="chat-item-avatar-wrap">
                            <div class="chat-item-avatar">${avatarHtml}</div>
                        </div>
                        <div class="chat-item-info">
                            <div class="chat-item-name">${escapeHtml(user.name)}</div>
                            <div class="chat-item-preview">点击开始聊天</div>
                        </div>
                    </div>
                `;
            });
            chatList.innerHTML = html;
        }

        // ==================== 切换聊天 ====================
        function switchToGroup() {
            currentReceiverId = null;
            updateChatHeader('群聊大厅', '所有在线用户');
            loadMessages();
        }

        function switchToPrivate(userId) {
            currentReceiverId = userId;
            fetch(`/chat/private/${userId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const user = @json($users).find(u => u.id == userId);
                        const userName = user ? user.name : '用户';
                        updateChatHeader(userName, '私聊中');
                        renderMessages(data.messages);
                    }
                });
        }

        function updateChatHeader(title, info) {
            document.getElementById('chatTitle').textContent = title;
            document.querySelector('.chat-header-info').textContent = info || '';
        }

        // ==================== 消息加载 ====================
        function loadMessages() {
            fetch('/chat/messages', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderMessages(data.messages);
                }
            });
        }

        function renderMessages(messages) {
            const container = document.getElementById('chatMessages');
            container.innerHTML = '';

            if (!messages || messages.length === 0) {
                container.innerHTML = `
                    <div class="empty-chat">
                        <i class="fa fa-comments"></i>
                        <p>暂无消息，发送第一条消息吧！</p>
                    </div>
                `;
                return;
            }

            messages.forEach(msg => {
                container.appendChild(createMessageElement(msg));
            });
            scrollToBottom();
        }

        function createMessageElement(msg) {
            const isMyMessage = msg.is_my_message;
            const div = document.createElement('div');
            div.className = `chat-message ${isMyMessage ? 'my-message' : 'other-message'}`;

            const avatarHtml = msg.avatar
                ? `<img src="${escapeHtml(msg.avatar)}" alt="">`
                : `<div class="avatar-placeholder">${escapeHtml((msg.username || '?').charAt(0))}</div>`;

            let contentHtml = '';
            if (msg.content) {
                contentHtml += `<div class="message-bubble">${escapeHtml(msg.content)}</div>`;
            }
            if (msg.file_path) {
                contentHtml += `
                    <div class="file-attachment"
                         data-file-path="${escapeHtml(msg.file_path)}"
                         data-file-name="${escapeHtml(msg.file_name || '文件')}"
                         ondblclick="previewFile(this)"
                         oncontextmenu="downloadFile(event, this)">
                        <i class="fa fa-file"></i>
                        <span>${escapeHtml(msg.file_name || '文件')}</span>
                    </div>
                `;
            }

            div.innerHTML = `
                <div class="message-avatar">${avatarHtml}</div>
                <div class="message-content">
                    <div class="message-info">
                        <span class="message-username">${escapeHtml(msg.username || '未知')}</span>
                        <span class="message-time">${escapeHtml(msg.created_at)}</span>
                    </div>
                    ${contentHtml}
                </div>
            `;
            return div;
        }

        // ==================== 发送消息 ====================
        function sendMessage(event) {
            event.preventDefault();
            const input = document.getElementById('messageInput');
            const content = input.value.trim();

            if (!content && !selectedFile) {
                return false;
            }

            const formData = new FormData();
            formData.append('content', content);
            formData.append('is_private', currentTab === 'private' ? '1' : '0');
            if (currentReceiverId) {
                formData.append('receiver_id', currentReceiverId);
            }
            if (selectedFile) {
                formData.append('file', selectedFile);
            }

            const submitBtn = document.querySelector('.btn-send');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> 发送中...';

            fetch('/chat/send', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    clearFile();

                    // 如果是群聊，重新加载消息；如果是私聊，追加消息
                    if (currentTab === 'group') {
                        loadMessages();
                    } else if (currentReceiverId) {
                        // 追加自己的消息
                        const container = document.getElementById('chatMessages');
                        if (container.querySelector('.empty-chat')) {
                            container.innerHTML = '';
                        }
                        container.appendChild(createMessageElement(data.message));
                        scrollToBottom();
                    }
                } else if (data.error) {
                    alert(data.error);
                }
            })
            .catch(err => {
                console.error('发送失败:', err);
                alert('发送失败，请重试');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa fa-paper-plane"></i> 发送';
                input.focus();
            });

            return false;
        }

        // ==================== 文件上传 ====================
        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (file.size > 20 * 1024 * 1024) {
                alert('文件大小不能超过 20MB');
                event.target.value = '';
                return;
            }

            selectedFile = file;
            document.getElementById('filePreviewName').textContent = file.name;
            document.getElementById('filePreviewArea').classList.add('show');
        }

        function clearFile() {
            selectedFile = null;
            document.getElementById('fileInput').value = '';
            document.getElementById('filePreviewArea').classList.remove('show');
        }

        // ==================== 文件预览和下载 ====================
        // 双击预览文件
        function previewFile(element) {
            const filePath = element.dataset.filePath;
            const fileName = element.dataset.fileName;
            const ext = fileName.split('.').pop().toLowerCase();

            const previewableDocs = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
            const previewableImages = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];

            if (previewableDocs.includes(ext)) {
                // 文档类型在新窗口打开预览
                window.open(filePath, '_blank');
            } else if (previewableImages.includes(ext)) {
                // 图片类型显示在弹窗中预览
                showImagePreview(filePath, fileName);
            }
        }

        // 右键下载文件
        function downloadFile(event, element) {
            event.preventDefault();
            const filePath = element.dataset.filePath;
            const fileName = element.dataset.fileName;

            const link = document.createElement('a');
            link.href = filePath;
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // 图片预览弹窗
        function showImagePreview(src, title) {
            const overlay = document.createElement('div');
            overlay.style.cssText = `
                position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0,0,0,0.8); display: flex; align-items: center;
                justify-content: center; z-index: 10000; cursor: pointer;
            `;

            const img = document.createElement('img');
            img.src = src;
            img.title = title;
            img.style.cssText = 'max-width: 90%; max-height: 90%; object-fit: contain; border-radius: 8px;';

            const closeBtn = document.createElement('div');
            closeBtn.innerHTML = '<i class="fa fa-times" style="color: white; font-size: 24px;"></i>';
            closeBtn.style.cssText = 'position: absolute; top: 20px; right: 30px; width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;';

            overlay.appendChild(img);
            overlay.appendChild(closeBtn);
            document.body.appendChild(overlay);

            overlay.addEventListener('click', () => document.body.removeChild(overlay));
        }

        // ==================== 表情插入 ====================
        function insertEmoji(emoji) {
            const input = document.getElementById('messageInput');
            const start = input.selectionStart;
            const end = input.selectionEnd;
            input.value = input.value.substring(0, start) + emoji + input.value.substring(end);
            input.selectionStart = input.selectionEnd = start + emoji.length;
            input.focus();
        }

        // ==================== 清空当前用户留言板 ====================
        function clearMyBoard() {
            // 确认对话框
            const confirmMsg = currentTab === 'group'
                ? '确定清空你的群聊留言板吗？此操作只清空你自己的显示记录，不影响其他用户。'
                : '确定清空你的私聊留言板吗？此操作只清空你自己的显示记录，不影响对方。';

            if (!confirm(confirmMsg)) {
                return;
            }

            const btn = document.getElementById('clearBoardBtn');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> 清空中...';

            // 根据当前聊天类型选择对应的清空接口
            const url = currentTab === 'group'
                ? '/chat/clear-group'
                : `/chat/clear-private/${currentReceiverId}`;

            // 如果是私聊但未选择对方，提示用户
            if (currentTab === 'private' && !currentReceiverId) {
                alert('请先选择一个私聊对象');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                return;
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // 清空消息显示区域
                    const container = document.getElementById('chatMessages');
                    container.innerHTML = `
                        <div class="empty-chat">
                            <i class="fa fa-comments"></i>
                            <p>${escapeHtml(data.message || '留言板已清空')}</p>
                        </div>
                    `;
                    // 显示成功提示
                    showToast(data.message || '已清空留言板', 'success');
                } else if (data.error) {
                    alert(data.error);
                }
            })
            .catch(err => {
                console.error('清空失败:', err);
                alert('清空失败，请重试');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
        }

        // 显示提示信息（轻量级 toast）
        function showToast(message, type) {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? '#10b981' : '#ef4444';
            toast.style.cssText = `
                position: fixed; top: 80px; left: 50%; transform: translateX(-50%);
                background: ${bgColor}; color: white; padding: 10px 24px;
                border-radius: 8px; font-size: 0.85rem; z-index: 10000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15); opacity: 0;
                transition: opacity 0.3s;
            `;
            toast.textContent = message;
            document.body.appendChild(toast);
            // 淡入
            setTimeout(() => { toast.style.opacity = '1'; }, 10);
            // 3秒后淡出并移除
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => document.body.removeChild(toast), 300);
            }, 3000);
        }

        // ==================== 工具函数 ====================
        function scrollToBottom() {
            const container = document.getElementById('chatMessages');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        }
    </script>
</body>
</html>
