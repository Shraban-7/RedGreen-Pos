<?php

namespace App\Services;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Repositories\Product\ProductRepositoryInterface;

class ProductService
{
    private $productRepo;
    public function __construct(ProductRepositoryInterface $productRepo)
    {
        $this->productRepo = $productRepo;
    }

    public function getProducts()
    {
        return $this->productRepo->paginate();
    }

    public function findBySlug(string $slug)
    {
        return $this->productRepo->findBySlug($slug);
    }

    public function store($data)
    {
        $data['user_id'] = auth()->id();
        $data['sku'] = 'PRD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        $data['slug'] = str_slug('products', 'slug', $data['name']);
        if (isset($data['thumbnail']) && $data['thumbnail']) {
            $data['thumbnail'] = upload_file($data['thumbnail'], 'products');
        }
        $data = $this->prepareProductData($data, true);
        return $this->productRepo->create($data);
    }

    public function update(Product $product, $data)
    {
        $data['user_id'] = auth()->id();


        if (!isset($data['sku']) || !$data['sku']) {
             $data['sku'] = 'PRD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } else {
            $data['sku'] = $product->sku;
        }


        if (isset($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = str_slug('products', 'slug', $data['name'], '-', $product->id);
        }

        if (isset($data['thumbnail']) && $data['thumbnail']) {
            if ($product->thumbnail) {
                delete_file($product->thumbnail);
            }

            $data['thumbnail'] = upload_file($data['thumbnail'], 'products');
        }

        $data = $this->prepareProductData($data, true);

        return $this->productRepo->update($product->id, $data);
    }

    public function deleteProduct(Product $product)
    {
        if ($product->thumbnail) {
            delete_file($product->thumbnail);
        }

        return $this->productRepo->delete($product->id);
    }

    protected function prepareProductData(array $data, bool $isUpdate = false): array
    {

        $data['discount_amount'] = calculate_discount_amount(
            $data['selling_price'],
            $data['discount_type'] ?? null,
            $data['discount_value'] ?? null
        );

        $data['discounted_price'] = calculate_discounted_price(
            $data['selling_price'],
            $data['discount_type'] ?? null,
            $data['discount_value'] ?? null
        );

        return $data;
    }
}
