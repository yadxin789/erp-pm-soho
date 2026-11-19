<?php

/*
|--------------------------------------------------------------------------
| 语音会议配置（合并后：站内信令，零外部媒体服务器依赖）
|--------------------------------------------------------------------------
| 原 PM 走 Swoole 信令服务 + Janus AudioBridge，需外部进程与端口；
| 合并后统一为 Core 应用内信令（voice_signals 表 + 2 秒轮询），
| 以下仅保留可选 ICE 配置与令牌 TTL。
|
| 已下线（代码不再读取，env 中残留无影响）：
|   signal_ws / janus_ws / janus_admin / janus_admin_secret
|   signal_listen / signal_port / pm_internal_url
*/

return [
    // STUN/TURN（纯内网一般不需要 TURN；留空则用浏览器默认/无 ICE 服务器）
    'stun' => env('VOICE_STUN'),
    'turn' => env('VOICE_TURN'),
    'turn_secret' => env('VOICE_TURN_SECRET'),

    // 入会 token TTL（秒）
    'token_ttl' => env('VOICE_TOKEN_TTL', 600),
];
