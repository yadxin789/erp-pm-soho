<?php

namespace App\Soho\Contracts;

/**
 * Webhook 适配器接口
 * 
 * 用于处理电商平台的 Webhook 回调通知，包括签名验证、事件解析、
 * 支持的事件类型查询以及事件响应处理等。
 * 当平台订单、库存等发生变化时，平台通过 Webhook 主动通知系统。
 */
interface WebhookAdapter
{
    /**
     * 验证 Webhook 请求的签名
     * 
     * 根据平台的签名算法（如 HMAC-SHA256、MD5 等）验证请求体的签名，
     * 确保请求来自可信的平台方且数据未被篡改。
     * 
     * @param string $payload   请求体原始内容（JSON 字符串）
     * @param string $signature 请求头中携带的签名字符串
     * @return bool 签名是否有效
     */
    public function verifySignature(string $payload, string $signature): bool;

    /**
     * 解析 Webhook 事件
     * 
     * 根据请求头和请求体解析出事件类型和事件数据，
     * 用于后续的事件路由和业务处理。
     * 
     * @param array  $headers 请求头数组，包含 X-Signature、X-Event-Type、X-Timestamp 等
     * @param string $payload 请求体原始内容（JSON 字符串）
     * @return array{
     *     event_type?: string,
     *     event_id?: string,
     *     timestamp?: string,
     *     data?: array,
     *     platform_code?: string,
     *     error?: string
     * } 解析后的事件数据
     */
    public function parseEvent(array $headers, string $payload): array;

    /**
     * 获取平台支持的 Webhook 事件类型列表
     * 
     * 返回平台可通过 Webhook 推送的所有事件类型，用于订阅配置和事件路由。
     * 
     * @return array 事件类型列表：
     *               [['type' => 'ORDER_CREATED', 'description' => '订单创建'],
     *                ['type' => 'ORDER_STATUS_CHANGED', 'description' => '订单状态变更'],
     *                ['type' => 'STOCK_UPDATED', 'description' => '库存更新']]
     */
    public function getSupportedEvents(): array;

    /**
     * 获取指定事件类型的响应配置
     * 
     * 返回处理指定事件类型时应返回的 HTTP 响应配置，
     * 包括状态码、响应体等，用于平台的事件确认机制。
     * 
     * @param string $eventType 事件类型，如 'ORDER_CREATED'
     * @return array{
     *     status_code?: int,
     *     body?: array,
     *     headers?: array
     * } 响应配置数组
     */
    public function getResponseForEvent(string $eventType): array;
}