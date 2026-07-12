<?php

namespace App\Repositories\Product;

interface ProductRepositoryInterface
{
    public function all();
    public function paginate($limit = 20);
    public function find($id);
    public function findBySlug($slug);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function lowStock($limit = 10);
}
