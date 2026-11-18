<?php

namespace App\Soho\Contracts;

/**
 * 授权适配器接口
 * 
 * 用于处理电商平台的 OAuth 授权流程，包括授权测试、令牌刷新、授权 URL 生成、
 * 回调处理以及授权撤销等功能。不同平台的授权方式（授权码模式、令牌模式等）由各适配器自行实现。
 */
interface AuthorizationAdapter
{
    /**
     * 测试当前授权是否有效
     * 
     * 调用平台 API 验证当前存储的访问令牌（Access Token）是否仍然有效，
     * 返回 true 表示令牌可用，返回 false 表示令牌已过期或无效。
     * 
     * @return bool 授权是否有效
     */
    public function testAuthorization(): bool;

    /**
     * 刷新访问令牌
     * 
     * 使用刷新令牌（Refresh Token）向平台申请新的访问令牌，
     * 成功时返回 true 并自动更新本地存储的令牌信息。
     * 
     * @return bool 令牌刷新是否成功
     */
    public function refreshAccessToken(): bool;

    /**
     * 生成平台授权跳转 URL
     * 
     * 根据平台 OAuth 规范，生成用于引导商户完成授权的 URL。
     * 可通过 $params 传递额外参数，如回调地址、权限范围、state 等。
     * 
     * @param array $params 授权附加参数，如：
     *                      ['redirect_uri' => 'https://...', 'scope' => 'read_write', 'state' => 'abc123']
     * @return string 授权跳转 URL
     */
    public function getAuthorizationUrl(array $params = []): string;

    /**
     * 处理平台授权回调
     * 
     * 接收平台授权回调的参数（通常包含 code 或 token），
     * 向平台换取/验证访问令牌，并返回包含令牌信息的数组。
     * 
     * @param array $params 回调参数，通常包含：
     *                      ['code' => '授权码', 'state' => '状态校验值']
     * @return array{
     *     access_token?: string,
     *     refresh_token?: string,
     *     expires_at?: int,
     *     platform_account_id?: int,
     *     error?: string
     * } 处理结果数组，成功时包含令牌信息，失败时包含错误信息
     */
    public function handleAuthorizationCallback(array $params = []): array;

    /**
     * 撤销已授权的访问令牌
     * 
     * 调用平台的令牌撤销接口，使当前的访问令牌和刷新令牌失效，
     * 用于商户主动解除授权的场景。
     * 
     * @return bool 撤销是否成功
     */
    public function revokeAuthorization(): bool;
}