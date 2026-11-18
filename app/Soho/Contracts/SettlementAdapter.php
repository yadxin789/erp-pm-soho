<?php

namespace App\Soho\Contracts;

/**
 * 结算适配器接口
 * 
 * 用于结算单/对账数据的同步，包括从平台拉取结算单列表、
 * 获取结算单详情以及查询结算周期等功能。
 * 适用于财务对账和结算管理场景。
 */
interface SettlementAdapter
{
    /**
     * 从平台拉取结算单列表
     * 
     * 根据查询参数从平台获取结算单/对账列表数据，支持分页、结算周期筛选等。
     * 
     * @param array $params 查询参数，支持：
     *                      ['page' => 1, 'page_size' => 50, 'period_start' => '2024-01-01',
     *                       'period_end' => '2024-01-31', 'status' => 'SETTLED']
     * @return array{
     *     list?: array,
     *     total?: int,
     *     page?: int,
     *     page_size?: int,
     *     has_more?: bool
     * } 结算单列表数据
     */
    public function fetchSettlements(array $params = []): array;

    /**
     * 获取指定结算单的详细信息
     * 
     * 根据平台结算单 ID 获取该结算单的完整详情，包括结算金额、
     * 订单明细、退款扣除、平台服务费等。
     * 
     * @param string $settlementId 平台结算单 ID
     * @return array{
     *     settlement_id?: string,
     *     period_start?: string,
     *     period_end?: string,
     *     total_amount?: float,
     *     orders?: array,
     *     refunds?: array,
     *     fees?: array,
     *     net_amount?: float,
     *     status?: string,
     *     error?: string
     * } 结算单详情数据
     */
    public function fetchSettlementDetail(string $settlementId): array;

    /**
     * 获取平台支持的结算周期列表
     * 
     * 返回平台的结算周期配置，用于对账任务的调度和结算单的按周期查询。
     * 
     * @return array 结算周期列表：
     *               [['code' => 'DAILY', 'name' => '日结', 'description' => '每日结算一次'],
     *                ['code' => 'WEEKLY', 'name' => '周结'],
     *                ['code' => 'MONTHLY', 'name' => '月结']]
     */
    public function getSettlementPeriods(): array;
}