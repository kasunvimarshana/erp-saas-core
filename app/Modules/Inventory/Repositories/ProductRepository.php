<?php

namespace App\Modules\Inventory\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Inventory\Models\Product;

/**
 * Product Repository
 * 
 * Handles data access for product operations.
 */
class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    /**
     * Find product by SKU.
     *
     * @param string $sku
     * @return Product|null
     */
    public function findBySku(string $sku): ?Product
    {
        return $this->model->where('sku', $sku)->first();
    }

    /**
     * Get products by category.
     *
     * @param int $categoryId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByCategory(int $categoryId)
    {
        return $this->model->where('category_id', $categoryId)->active()->get();
    }

    /**
     * Get products by brand.
     *
     * @param int $brandId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByBrand(int $brandId)
    {
        return $this->model->where('brand_id', $brandId)->active()->get();
    }

    /**
     * Get products with low stock.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowStockProducts()
    {
        return $this->model
            ->where('track_inventory', true)
            ->whereColumn('min_stock_level', '>', 'reorder_point')
            ->active()
            ->get();
    }

    /**
     * Search products.
     *
     * @param string $query
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search(string $query)
    {
        return $this->model
            ->where('sku', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%")
            ->orWhere('barcode', 'like', "%{$query}%")
            ->active()
            ->get();
    }
}
