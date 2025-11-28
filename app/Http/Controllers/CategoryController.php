<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categoryService)
    {
    }

    public function index()
    {
        $categories = $this->categoryService->getAll();

        return apiResourceResponse(
            CategoryResource::collection($categories),
            'Categories fetched successfully',
        );
    }

    public function store(CategoryRequest $request)
    {
        $category = $this->categoryService->save($request->validated());

        return apiResourceResponse(
            new CategoryResource($category),
            'Category created successfully',
            [],
            201
        );
    }

    public function show($slug)
    {
        $category = $this->categoryService->getBySlug($slug);

        if (!$category) {
            return errorResponse('Category not found', 404);
        }

        return apiResourceResponse(
            new CategoryResource($category),
            'Category fetched successfully',
        );
    }

    public function update(CategoryRequest $request, $slug)
    {
        $category = $this->categoryService->getBySlug($slug);

        if (!$category) {
            return errorResponse('Category not found', 404);
        }

        $updated = $this->categoryService->update($category, $request->validated());

        return apiResourceResponse(
            new CategoryResource($updated),
            'Category updated successfully',
        );
    }

    public function destroy($slug)
    {
        $category = $this->categoryService->getBySlug($slug);
        $this->categoryService->delete($category);

        return successResponse('Category deleted successfully', 200);
    }
}
