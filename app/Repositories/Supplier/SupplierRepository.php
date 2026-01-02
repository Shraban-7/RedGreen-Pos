<?php

namespace App\Repositories\Supplier;

use App\Models\Supplier;

class SupplierRepository implements SupplierRepositoryInterface
{
     public function all()
    {
        return Supplier::latest()->get();
    }

    public function paginate($limit = 20)
    {
        return Supplier::latest()->paginate($limit);
    }

    public function findById($id)
    {
        return Supplier::find($id);
    }

    public function create(array $data)
    {
        return Supplier::create($data);
    }

    public function updateSupplier($id, array $data)
    {
        $supplier = Supplier::find($id);
        $supplier->update($data);
        return $supplier;
    }

    public function delete($id)
    {
        $supplier = $this->findById($id);
        return $supplier->delete();
    }
}
