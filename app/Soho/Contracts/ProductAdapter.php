<?php

namespace App\Soho\Contracts;

/**
 * 商品适配器接口
 * 
 * 用于商品数据的双向同步，包括从平台拉取商品、向平台发布商品、
 * 更新商品价格、更新商品库存以及获取平台商品分类等功能。
 * 不同平台的商品模型差异较大，由各适配器自行处理字段映射。
 */
interface ProductAdapter
{
    /**
     * 从平台拉取商品列表
     * 
     * 根据查询参数从平台获取商品列表数据，支持分页、状态筛选、时间范围筛选等。
     * 
     * @param array $params 查询参数，支持：
     *                      ['page' => 1, 'page_size' => 50, 'status' => 'ON_SALE', 'updated_since' => '2024-01-01']
     * @return array{
     *     list?: array,
     *     total?: int,
     *     page?: int,
     *     page_size?: int,
     *     has_more?: bool
     * } 商品列表数据，包含分页信息和商品详情数组
     */
    public function fetchProducts(array $params = []): array;

    /**
     * 向平台发布新商品
     * 
     * 将本地系统的商品数据发布到电商平台，创建新的商品 Listing。
     * 
     * @param array $productData 商品数据，包含：
     *                           ['title' => '商品标题', 'description' => '商品描述', 'price' => 99.00, 
     *                            'images' => [...], 'category_id' => 123, 'sku_list' => [...]]
     * @return array{
     *     platform_product_id?: string,
     *     status?: string,
     *     error?: string
     * } 发布结果，成功时返回平台商品 ID 和状态
     */
    public function publishProduct(array $productData): array;

    /**
     * 更新商品价格
     * 
     * 批量或单个更新平台上已发布商品的价格信息。
     * 
     * @param array $priceData 价格数据，包含：
     *                         ['platform_product_id' => 'xxx', 'skus' => [['sku_id' => 's1', 'price' => 89.00]]]
     * @return array{
     *     success_count?: int,
     *     failed_items?: array,
     *     error?: string
     * } 更新结果，包含成功数量和失败项详情
     */
    public function updateProductPrice(array $priceData): array;

    /**
     * 更新商品库存
     * 
     * 批量更新平台上商品的 SKU 库存数量。
     * 
     * @param array $inventoryData 库存数据，包含：
     *                             ['platform_product_id' => 'xxx', 'skus' => [['sku_id' => 's1', 'stock' => 100]]]
     * @return array{
     *     success_count?: int,
     *     failed_items?: array,
     *     error?: string
     * } 更新结果，包含成功数量和失败项详情
     */
    public function updateProductInventory(array $inventoryData): array;

    /**
     * 获取平台商品分类列表
     * 
     * 从平台获取可用的商品分类/品类树，用于商品发布时的分类选择。
     * 
     * @return array 分类列表，通常为树形结构：
     *               [['id' => 1, 'name' => '服装', 'children' => [['id' => 2, 'name' => '男装', ...]]]]
     */
    public function getProductCategories(): array;
}