<?php

namespace App\Repositories\Product;

use App\Models\Product;

class ProductRepository implements ProductRepositoryInterface
{
    public function all()
    {
        return Product::with('category','brand')->get();
    }

    public function paginate($limit = 20)
    {
        return Product::latest()->paginate($limit);
    }

    public function find($id)
    {
        return Product::findOrFail($id);
    }

    public function findBySlug($slug)
    {
        return Product::where('slug', $slug)->first();
    }

    public function create(array $data)
    {
        return Product::create($data);
    }

    public function update($id, array $data)
    {
        $product = $this->find($id);
        $product->update($data);
        return $product;
    }

    public function delete($id)
    {
        $product = $this->find($id);
        return $product->delete();
    }

    public function lowStock($limit = 10)
    {
        return Product::select('id', 'name', 'stock_in', 'stock_out', 'low_stock_quantity')
            ->whereRaw('(stock_in - stock_out) <= low_stock_quantity')
            ->orderByRaw('(stock_in - stock_out) ASC')
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => max($product->stock_in - $product->stock_out, 0),
                ];
            });
    }
}
