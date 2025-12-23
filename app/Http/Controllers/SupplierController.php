<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\SupplierService;
use App\Http\Requests\SupplierRequest;
use App\Http\Resources\SupplierResource;

class SupplierController extends Controller
{
    private $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    public function index()
    {
        $suppliers = $this->supplierService->getSuppliers();

        return apiResourceResponse(
            SupplierResource::collection($suppliers),
            'Suppliers fetched successfully'
        );
    }

    public function store(SupplierRequest $request)
    {
        $supplier = $this->supplierService->saveSupplier(
            $request->validated()
        );

        return apiResourceResponse(
            new SupplierResource($supplier),
            'Supplier created successfully',
            [],
            201
        );
    }

    public function show(Supplier $supplier)
    {
        return apiResourceResponse(
            new SupplierResource($supplier),
            'Supplier fetched successfully'
        );
    }

    public function update(SupplierRequest $request, Supplier $supplier)
    {
        $updatedSupplier = $this->supplierService->updateSupplier(
            $supplier,
            $request->validated()
        );

        return apiResourceResponse(
            new SupplierResource($updatedSupplier),
            'Supplier updated successfully'
        );
    }

    public function destroy(Supplier $supplier)
    {
        $this->supplierService->deleteSupplier($supplier->id);

        return successResponse('Supplier deleted successfully');
    }
}
