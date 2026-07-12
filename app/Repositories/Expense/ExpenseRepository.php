<?php

namespace App\Repositories\Expense;

use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class ExpenseRepository implements ExpenseRepositoryInterface
{
    public function all()
    {
        return Expense::with('category', 'user')->latest()->get();
    }

    public function paginate($limit = 20)
    {
        return Expense::with('category', 'user')->latest()->paginate($limit);
    }

    public function find($id)
    {
        return Expense::with('category', 'user')->findOrFail($id);
    }

    public function create(array $data)
    {
        return Expense::create($data);
    }

    public function update($id, array $data)
    {
        $expense = $this->find($id);
        $expense->update($data);
        return $expense;
    }

    public function delete($id)
    {
        $expense = $this->find($id);
        return $expense->delete();
    }

    public function sumBetween($from, $to)
    {
        return (float) Expense::whereBetween('expense_date', [$from, $to])
            ->sum('amount');
    }

    public function dailyTotalsBetween($from, $to)
    {
        return Expense::query()
            ->select(DB::raw('expense_date as date'), DB::raw('SUM(amount) as total'))
            ->whereBetween('expense_date', [$from, $to])
            ->groupBy('expense_date')
            ->orderBy('date')
            ->get();
    }

    public function categoryBreakdownBetween($from, $to)
    {
        return Expense::query()
            ->select('expense_category_id', DB::raw('SUM(amount) as total'))
            ->whereBetween('expense_date', [$from, $to])
            ->groupBy('expense_category_id')
            ->with('category')
            ->get();
    }
}