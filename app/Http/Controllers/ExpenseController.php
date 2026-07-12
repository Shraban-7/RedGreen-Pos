<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Services\ExpenseService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    private $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    public function index()
    {
        $expenses = $this->expenseService->getExpenses();

        return apiResourceResponse(
            ExpenseResource::collection($expenses),
            'Expenses fetched successfully',
        );
    }

    public function store(ExpenseRequest $request)
    {
        $expense = $this->expenseService->store($request->validated());

        return apiResourceResponse(
            new ExpenseResource($expense),
            'Expense created successfully',
            [],
            201
        );
    }

    public function show($id)
    {
        $expense = $this->expenseService->find($id);

        return apiResourceResponse(
            new ExpenseResource($expense),
            'Expense fetched successfully',
        );
    }

    public function update(ExpenseRequest $request, $id)
    {
        $expense = $this->expenseService->find($id);
        $updated = $this->expenseService->update($expense, $request->validated());

        return apiResourceResponse(
            new ExpenseResource($updated),
            'Expense updated successfully',
        );
    }

    public function destroy($id)
    {
        $expense = $this->expenseService->find($id);
        $this->expenseService->deleteExpense($expense);

        return successResponse('Expense deleted successfully', 200);
    }
}