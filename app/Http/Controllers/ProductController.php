<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $productService;
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

     public function index()
    {
        $products = $this->productService->getProducts();

        return apiResourceResponse(
            ProductResource::collection($products),
            'Prods fetched successfully',
        );
    }

    public function store(ProductRequest $request)
    {
        $product = $this->productService->store($request->all());

        return apiResourceResponse(
            new ProductResource($product),
            "Product create successfully",
            [],
            201
        );
    }

    public function show($slug)
    {
        $product = $this->productService->findBySlug($slug);

        if (!$product) {
            return errorResponse('Product not found', 404);
        }

        return apiResourceResponse(
            new ProductResource($product),
            'Product fetched successfully',
        );
    }

    public function update(ProductRequest $request, $slug)
    {
        $product = $this->productService->findBySlug($slug);

        if (!$product) {
            return errorResponse('Product not found', 404);
        }

        $updated = $this->productService->update($product, $request->validated());

        return apiResourceResponse(
            new ProductResource($updated),
            'product updated successfully',
        );
    }

    public function destroy($slug)
    {
        $product = $this->productService->findBySlug($slug);
        $this->productService->deleteProduct($product);

        return successResponse('Product deleted successfully', 200);
    }
}
