<?php

namespace App\Soho\Contracts;

/**
 * 发货适配器接口
 * 
 * 用于发货数据的同步与物流跟踪，包括向平台推送发货信息、
 * 查询发货状态以及获取平台支持的物流公司和发货方式。
 * 适用于订单履约环节的物流对接。
 */
interface ShipmentAdapter
{
    /**
     * 向平台推送发货信息
     * 
     * 将本地订单的发货信息（物流单号、物流公司、发货时间等）同步到平台，
     * 完成订单的发货确认。
     * 
     * @param array $orderData 发货数据，包含：
     *                         ['platform_order_id' => 'xxx', 'tracking_no' => 'SF123456', 
     *                          'carrier_code' => 'SF', 'ship_time' => '2024-01-15 10:30:00',
     *                          'packages' => [['weight' => 1.5, 'length' => 30, 'width' => 20, 'height' => 15]]]
     * @return array{
     *     success?: bool,
     *     shipment_id?: string,
     *     status?: string,
     *     error?: string
     * } 推送结果，成功时返回平台发货单 ID
     */
    public function pushShipment(array $orderData): array;

    /**
     * 查询发货状态
     * 
     * 根据发货信息查询平台上的当前发货状态，用于物流跟踪和状态回写。
     * 
     * @param array $shipmentData 发货查询参数，包含：
     *                            ['platform_order_id' => 'xxx', 'shipment_id' => 'yyy', 'tracking_no' => 'SF123456']
     * @return array{
     *     shipment_id?: string,
     *     status?: string,
     *     tracking_no?: string,
     *     carrier?: string,
     *     estimated_delivery?: string,
     *     tracking_events?: array,
     *     error?: string
     * } 发货状态详情
     */
    public function fetchShipmentStatus(array $shipmentData): array;

    /**
     * 获取平台支持的物流公司列表
     * 
     * 返回平台认可的物流公司编码及名称，用于发货时的物流公司选择。
     * 
     * @return array 物流公司列表：
     *               [['code' => 'SF', 'name' => '顺丰速运'], ['code' => 'YT', 'name' => '圆通速递']]
     */
    public function getSupportedCarriers(): array;

    /**
     * 获取平台支持的发货方式列表
     * 
     * 返回平台支持的发货方式，如自配送、FBA 配送、门店自提等。
     * 
     * @return array 发货方式列表：
     *               [['code' => 'SELF', 'name' => '卖家自配送'], ['code' => 'FBA', 'name' => 'FBA 配送']]
     */
    public function getSupportedShipmentMethods(): array;
}