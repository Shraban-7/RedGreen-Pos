<?php

namespace App\Repositories\ExpenseCategory;

use App\Models\ExpenseCategory;

class ExpenseCategoryRepository implements ExpenseCategoryRepositoryInterface
{
    public function all()
    {
        return ExpenseCategory::latest()->get();
    }

    public function paginate($limit = 20)
    {
        return ExpenseCategory::latest()->paginate($limit);
    }

    public function find($id)
    {
        return ExpenseCategory::findOrFail($id);
    }

    public function findBySlug($slug)
    {
        return ExpenseCategory::where('slug', $slug)->first();
    }

    public function create(array $data)
    {
        return ExpenseCategory::create($data);
    }

    public function update($id, array $data)
    {
        $category = $this->find($id);
        $category->update($data);
        return $category;
    }

    public function delete($id)
    {
        $category = $this->find($id);
        return $category->delete();
    }
}