<?php

namespace App\Services;

use App\Models\Supplier;
use App\Repositories\Supplier\SupplierRepositoryInterface;

class SupplierService
{
    private $supplierRepository;

    public function __construct(SupplierRepositoryInterface $supplierRepository)
    {
        $this->supplierRepository = $supplierRepository;
    }

    public function getSuppliers()
    {
        return $this->supplierRepository->paginate();
    }

    public function saveSupplier(array $data)
    {
        return $this->supplierRepository->create($data);
    }

    public function showSupplier(Supplier $supplier)
    {
        return $this->supplierRepository->find($supplier->id);
    }

    public function updateSupplier(Supplier $supplier, array $data): Supplier
    {
        $this->supplierRepository->update($supplier, $data);

        return $supplier->fresh();
    }


    public function deleteSupplier(Supplier $supplier)
    {
        return $this->supplierRepository->delete($supplier);
    }
}
