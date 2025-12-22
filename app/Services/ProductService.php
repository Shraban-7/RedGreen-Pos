<?php

namespace App\Services;
use App\Models\Product;
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
        $data['slug'] = str_slug('products', 'slug', $data['name']);
        if (isset($data['image']) && $data['image']) {
            $data['image'] = upload_file($data['image'], 'brands');
        }
        $data = $this->prepareProductData($data, true);
        return $this->productRepo->create($data);
    }

    public function update(Product $product, $data)
    {
        if (isset($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = str_slug('products', 'slug', $data['name'], '-', $product->id);
        }

        if (isset($data['image']) && $data['image']) {
            if ($product->image) {
                delete_file($product->image);
            }

            $data['image'] = upload_file($data['image'], 'brands');
        }

        $data = $this->prepareProductData($data, true);

        return $this->productRepo->update($product->id, $data);
    }

    public function deleteProduct(Product $product)
    {
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
