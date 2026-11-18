<?php

namespace App\Soho\Contracts;

/**
 * 库存适配器接口
 * 
 * 用于库存数据的双向同步，包括向平台推送库存数量、
 * 从平台拉取远程库存以及查询库存相关的功能能力。
 * 适用于需要实时同步 SKU 库存的电商场景。
 */
interface InventoryAdapter
{
    /**
     * 向平台推送库存数据
     * 
     * 将本地系统的商品 SKU 库存数量同步到电商平台，支持批量推送多个 SKU。
     * 
     * @param array $goodsData 库存数据，包含：
     *                         ['platform_product_id' => 'xxx', 'skus' => [['sku_id' => 's1', 'stock' => 100, 'warehouse' => 'cn']]]
     * @return array{
     *     success_count?: int,
     *     failed_items?: array,
     *     batch_id?: string,
     *     error?: string
     * } 推送结果，包含成功数量、失败项详情和批次 ID
     */
    public function pushInventory(array $goodsData): array;

    /**
     * 从平台拉取远程库存数据
     * 
     * 根据查询参数从平台获取当前商品的远程库存数量，
     * 用于库存比对和异常监控。
     * 
     * @param array $params 查询参数，支持：
     *                      ['platform_product_id' => 'xxx', 'sku_ids' => ['s1', 's2'], 'warehouse' => 'cn']
     * @return array{
     *     list?: array,
     *     total?: int,
     *     error?: string
     * } 远程库存数据，包含各 SKU 的当前库存数量
     */
    public function fetchRemoteInventory(array $params = []): array;

    /**
     * 获取库存相关的功能能力配置
     * 
     * 返回平台库存接口的能力参数，如支持的仓库列表、
     * 最大单次推送 SKU 数量、是否支持分仓库库存等。
     * 
     * @return array{
     *     max_batch_size?: int,
     *     supports_warehouse_split?: bool,
     *     supports_reserved_stock?: bool,
     *     supported_warehouses?: array,
     *     sync_mode?: string
     * } 库存能力配置
     */
    public function getInventoryCapabilities(): array;
}