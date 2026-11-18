<?php

namespace App\Soho\Contracts;

/**
 * 订单适配器接口
 * 
 * 用于订单数据的双向同步与管理，包括从平台拉取订单列表、
 * 获取订单详情、确认订单、取消订单以及订单状态映射等功能。
 * 不同平台的订单状态流转规则不同，由各适配器处理状态映射。
 */
interface OrderAdapter
{
    /**
     * 从平台拉取订单列表
     * 
     * 根据查询参数从平台获取订单列表数据，支持分页、状态筛选、时间范围筛选等。
     * 
     * @param array $params 查询参数，支持：
     *                      ['page' => 1, 'page_size' => 50, 'status' => 'UNPAID', 
     *                       'created_start' => '2024-01-01', 'created_end' => '2024-01-31']
     * @return array{
     *     list?: array,
     *     total?: int,
     *     page?: int,
     *     page_size?: int,
     *     has_more?: bool,
     *     next_cursor?: string
     * } 订单列表数据，包含分页信息和订单详情数组
     */
    public function fetchOrders(array $params = []): array;

    /**
     * 获取指定订单的详细信息
     * 
     * 根据平台订单 ID 获取该订单的完整详情，包括商品明细、收件信息、
     * 支付信息、物流信息、状态日志等。
     * 
     * @param string $orderId 平台订单 ID
     * @return array{
     *     order_id?: string,
     *     status?: string,
     *     items?: array,
     *     shipping_address?: array,
     *     payment?: array,
     *     logistics?: array,
     *     status_history?: array,
     *     error?: string
     * } 订单详情数据
     */
    public function fetchOrderDetail(string $orderId): array;

    /**
     * 确认订单（确认收货或确认接单）
     * 
     * 对平台上的指定订单执行确认操作，适用于需要商户确认接单或确认收货的场景。
     * 
     * @param string $orderId 平台订单 ID
     * @return array{
     *     success?: bool,
     *     status?: string,
     *     error?: string
     * } 操作结果
     */
    public function confirmOrder(string $orderId): array;

    /**
     * 取消订单
     * 
     * 取消平台上的指定订单，并提供取消原因。部分平台对已发货订单不允许取消。
     * 
     * @param string $orderId 平台订单 ID
     * @param string $reason  取消原因，将同步至平台
     * @return array{
     *     success?: bool,
     *     status?: string,
     *     error?: string
     * } 操作结果
     */
    public function cancelOrder(string $orderId, string $reason): array;

    /**
     * 获取平台订单状态到系统订单状态的映射表
     * 
     * 返回平台订单状态值与本地系统订单状态值的对应关系，
     * 用于在同步订单时进行状态转换。
     * 
     * @return array<string, string> 状态映射表，如：
     *                               ['UNPAID' => 'pending', 'PAID' => 'paid', 
     *                                'SHIPPED' => 'shipped', 'COMPLETED' => 'completed']
     */
    public function getOrderStatusMap(): array;
}