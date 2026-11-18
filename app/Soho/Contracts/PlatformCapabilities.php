<?php

namespace App\Soho\Contracts;

/**
 * 平台能力声明接口
 * 
 * 用于声明各电商平台适配器所支持的功能能力、API版本、限流配置等信息。
 * 每个平台适配器实现此接口，告知系统该平台支持哪些业务操作（商品、订单、库存、发货、售后、结算等），
 * 以及各操作的具体能力参数，便于上层业务根据平台能力动态调整处理策略。
 */
interface PlatformCapabilities
{
    /**
     * 获取平台唯一编码
     * 
     * 返回用于标识平台类型的唯一字符串编码（如：taobao、jd、pinduoduo、amazon 等），
     * 该编码应与配置文件中的平台标识保持一致。
     * 
     * @return string 平台编码
     */
    public function getPlatformCode(): string;

    /**
     * 获取当前适配器所使用的 API 版本号
     * 
     * 返回适配器对接的平台 API 版本（如："2024-01-01"、"v3.0" 等），
     * 用于日志记录和兼容性判断。
     * 
     * @return string API 版本号
     */
    public function getApiVersion(): string;

    /**
     * 判断是否支持商品拉取（从平台同步商品数据到本地系统）
     * 
     * @return bool true 表示支持商品拉取功能
     */
    public function supportsProductPull(): bool;

    /**
     * 判断是否支持商品推送（将本地商品数据发布到平台）
     * 
     * @return bool true 表示支持商品推送功能
     */
    public function supportsProductPush(): bool;

    /**
     * 判断是否支持库存推送（将本地库存数据同步到平台）
     * 
     * @return bool true 表示支持库存推送功能
     */
    public function supportsInventoryPush(): bool;

    /**
     * 判断是否支持订单拉取（从平台同步订单数据到本地系统）
     * 
     * @return bool true 表示支持订单拉取功能
     */
    public function supportsOrderPull(): bool;

    /**
     * 判断是否支持发货推送（将本地发货信息同步到平台）
     * 
     * @return bool true 表示支持发货推送功能
     */
    public function supportsShipmentPush(): bool;

    /**
     * 判断是否支持售后拉取（从平台同步售后单数据到本地系统）
     * 
     * @return bool true 表示支持售后拉取功能
     */
    public function supportsAfterSalePull(): bool;

    /**
     * 判断是否支持退款推送（将本地退款/退货数据同步到平台）
     * 
     * @return bool true 表示支持退款推送功能
     */
    public function supportsRefundPush(): bool;

    /**
     * 判断是否支持结算单拉取（从平台同步结算/对账数据到本地系统）
     * 
     * @return bool true 表示支持结算单拉取功能
     */
    public function supportsSettlementPull(): bool;

    /**
     * 判断是否支持 Webhook 回调通知
     * 
     * 若平台支持 Webhook，则适配器需要实现 WebhookAdapter 接口以处理回调事件。
     * 
     * @return bool true 表示支持 Webhook 回调
     */
    public function supportsWebhook(): bool;

    /**
     * 获取平台 API 限流配置
     * 
     * 返回平台 API 的限流参数，用于请求调度和防止触发平台频率限制。
     * 
     * @return array{
     *     per_second?: int,
     *     per_minute?: int,
     *     max_concurrent?: int,
     *     retry_after?: int
     * } 限流配置数组，包含每秒/每分钟最大请求数、最大并发数、重试等待时间（秒）等
     */
    public function getRateLimit(): array;

    /**
     * 获取平台支持的订单状态列表
     * 
     * 返回平台订单的所有状态值，用于订单状态映射和筛选。
     * 
     * @return array 订单状态列表，如 ['PENDING', 'PAID', 'SHIPPED', 'COMPLETED', 'CANCELLED']
     */
    public function getSupportedOrderStatuses(): array;

    /**
     * 获取平台支持的售后类型列表
     * 
     * 返回平台支持的所有售后类型，用于售后单的筛选和处理。
     * 
     * @return array 售后类型列表，如 ['REFUND_ONLY', 'RETURN_AND_REFUND', 'EXCHANGE']
     */
    public function getSupportedAfterSaleTypes(): array;
}