<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BrandService;
use App\Http\Requests\BrandRequest;
use App\Http\Resources\BrandResource;

class BrandController extends Controller
{
    public function __construct(private BrandService $brandService) {
    }

    public function index()
    {
        $brands = $this->brandService->getAll();

        return apiResourceResponse(
            BrandResource::collection($brands),
            'Brands fetched successfully',
        );
    }

    public function store(BrandRequest $request)
    {
        $brand = $this->brandService->store($request->all());

        return apiResourceResponse(
            new BrandResource($brand),
            "Brand create successfully",
            [],
            201
        );
    }

    public function show($slug)
    {
        $brand = $this->brandService->findBySlug($slug);

        if (!$brand) {
            return errorResponse('Brand not found', 404);
        }

        return apiResourceResponse(
            new BrandResource($brand),
            'Brand fetched successfully',
        );
    }

    public function update(BrandRequest $request, $slug)
    {
        $brand = $this->brandService->findBySlug($slug);

        if (!$brand) {
            return errorResponse('Brand not found', 404);
        }

        $updated = $this->brandService->update($brand, $request->validated());

        return apiResourceResponse(
            new BrandResource($updated),
            'Brand updated successfully',
        );
    }

    public function destroy($slug)
    {
        $brand = $this->brandService->findBySlug($slug);
        $this->brandService->delete($brand);

        return successResponse('Brand deleted successfully', 200);
    }

    public function status(Request $request, $slug)
    {
        $request->validate([
            'status' => 'required|boolean'
        ]);

        $brand = $this->brandService->findBySlug($slug);
        if (!$brand) {
            errorResponse('Brand not found.');
        }

        $updated = $this->brandService->updateStatus($brand, $request->status);

        return successResponse('Brand ' . $updated->status . ' successfully');
    }
}
