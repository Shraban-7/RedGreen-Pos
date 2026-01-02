<?php

namespace App\Repositories\Supplier;

interface SupplierRepositoryInterface
{
    public function all();
    public function paginate($limit = 20);
    public function findById($id);
    public function create(array $data);
    public function updateSupplier($id, array $data);
    public function delete($id);
}
