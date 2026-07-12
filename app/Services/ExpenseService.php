<?php

namespace App\Services;

use App\Models\Expense;
use App\Repositories\Expense\ExpenseRepositoryInterface;

class ExpenseService
{
    private $expenseRepo;

    public function __construct(ExpenseRepositoryInterface $expenseRepo)
    {
        $this->expenseRepo = $expenseRepo;
    }

    public function getExpenses()
    {
        return $this->expenseRepo->paginate();
    }

    public function find($id)
    {
        return $this->expenseRepo->find($id);
    }

    public function store(array $data)
    {
        $data['user_id'] = auth()->id();

        if (isset($data['attachment']) && $data['attachment']) {
            $data['attachment'] = upload_file($data['attachment'], 'expenses');
        }

        return $this->expenseRepo->create($data);
    }

    public function update(Expense $expense, array $data)
    {
        if (isset($data['attachment']) && $data['attachment']) {
            if ($expense->attachment) {
                delete_file($expense->attachment);
            }

            $data['attachment'] = upload_file($data['attachment'], 'expenses');
        }

        return $this->expenseRepo->update($expense->id, $data);
    }

    public function deleteExpense(Expense $expense)
    {
        if ($expense->attachment) {
            delete_file($expense->attachment);
        }

        return $this->expenseRepo->delete($expense->id);
    }

    public function sumBetween($from, $to)
    {
        return $this->expenseRepo->sumBetween($from, $to);
    }

    public function categoryBreakdownBetween($from, $to)
    {
        return $this->expenseRepo->categoryBreakdownBetween($from, $to);
    }
}