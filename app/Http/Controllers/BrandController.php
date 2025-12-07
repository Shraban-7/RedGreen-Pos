<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use App\Services\BrandService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    private $brandService;
    public function __construct(BrandService $brandService)
    {
        $this->brandService = $brandService;
    }

    public function index()
    {
        $brands = $this->brandService->getAll();
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
}
