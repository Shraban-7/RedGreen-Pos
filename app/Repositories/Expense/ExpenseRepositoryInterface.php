<?php

namespace App\Repositories\Expense;

interface ExpenseRepositoryInterface
{
    public function all();
    public function paginate($limit = 20);
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function sumBetween($from, $to);
    public function dailyTotalsBetween($from, $to);
    public function categoryBreakdownBetween($from, $to);
}
