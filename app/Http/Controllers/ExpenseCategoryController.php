<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseCategoryRequest;
use App\Http\Resources\ExpenseCategoryResource;
use App\Services\ExpenseCategoryService;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    private $categoryService;

    public function __construct(ExpenseCategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        $categories = $this->categoryService->getAll();

        return apiResourceResponse(
            ExpenseCategoryResource::collection($categories),
            'Expense categories fetched successfully',
        );
    }

    public function store(ExpenseCategoryRequest $request)
    {
        $category = $this->categoryService->store($request->validated());

        return apiResourceResponse(
            new ExpenseCategoryResource($category),
            'Expense category created successfully',
            [],
            201
        );
    }

    public function show($id)
    {
        $category = $this->categoryService->find($id);

        return apiResourceResponse(
            new ExpenseCategoryResource($category),
            'Expense category fetched successfully',
        );
    }

    public function update(ExpenseCategoryRequest $request, $id)
    {
        $updated = $this->categoryService->update($id, $request->validated());

        return apiResourceResponse(
            new ExpenseCategoryResource($updated),
            'Expense category updated successfully',
        );
    }

    public function destroy($id)
    {
        $this->categoryService->delete($id);

        return successResponse('Expense category deleted successfully', 200);
    }
}