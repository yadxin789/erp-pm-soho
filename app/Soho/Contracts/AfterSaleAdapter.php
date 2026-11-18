<?php

namespace App\Soho\Contracts;

/**
 * 售后适配器接口
 * 
 * 用于售后单（退款、退货、换货）的双向同步与处理，
 * 包括从平台拉取售后单、向平台推送退款申请、查询售后详情以及确认退货等。
 * 适用于订单售后服务环节的数据对接。
 */
interface AfterSaleAdapter
{
    /**
     * 从平台拉取售后单列表
     * 
     * 根据查询参数从平台获取售后单/退款单列表数据，支持分页、类型筛选、状态筛选等。
     * 
     * @param array $params 查询参数，支持：
     *                      ['page' => 1, 'page_size' => 50, 'type' => 'REFUND', 'status' => 'PENDING',
     *                       'created_start' => '2024-01-01', 'created_end' => '2024-01-31']
     * @return array{
     *     list?: array,
     *     total?: int,
     *     page?: int,
     *     page_size?: int,
     *     has_more?: bool
     * } 售后单列表数据
     */
    public function fetchAfterSales(array $params = []): array;

    /**
     * 向平台推送退款/退货申请
     * 
     * 将本地系统的退款或退货申请同步到平台，在平台上创建售后工单。
     * 
     * @param array $refundData 退款/退货数据，包含：
     *                          ['platform_order_id' => 'xxx', 'type' => 'REFUND', 
     *                           'reason' => '商品质量问题', 'amount' => 99.00,
     *                           'images' => [...], 'remark' => '详细描述']
     * @return array{
     *     after_sale_id?: string,
     *     status?: string,
     *     error?: string
     * } 推送结果，成功时返回平台售后单 ID
     */
    public function pushRefund(array $refundData): array;

    /**
     * 获取指定售后单的详细信息
     * 
     * 根据平台售后单 ID 获取该售后单的完整详情，包括退款金额、
     * 退货地址、物流信息、处理进度等。
     * 
     * @param string $afterSaleId 平台售后单 ID
     * @return array{
     *     after_sale_id?: string,
     *     type?: string,
     *     status?: string,
     *     amount?: float,
     *     reason?: string,
     *     return_address?: array,
     *     logistics?: array,
     *     timeline?: array,
     *     error?: string
     * } 售后单详情数据
     */
    public function fetchAfterSaleDetail(string $afterSaleId): array;

    /**
     * 确认收到退货商品
     * 
     * 确认已收到买家寄回的商品，触发平台的退款流程。
     * 
     * @param array $returnData 退货确认数据，包含：
     *                          ['after_sale_id' => 'xxx', 'tracking_no' => 'SF987654', 
     *                           'received_time' => '2024-01-20 15:00:00', 'remark' => '商品完好']
     * @return array{
     *     success?: bool,
     *     status?: string,
     *     refund_amount?: float,
     *     error?: string
     * } 确认结果，成功时返回退款金额
     */
    public function confirmReturn(array $returnData): array;
}