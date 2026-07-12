<?php

namespace App\Repositories\ExpenseCategory;

interface ExpenseCategoryRepositoryInterface
{
    public function all();
    public function paginate($limit = 20);
    public function find($id);
    public function findBySlug($slug);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}