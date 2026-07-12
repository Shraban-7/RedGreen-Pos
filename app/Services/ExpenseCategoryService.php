<?php

namespace App\Services;

use App\Repositories\ExpenseCategory\ExpenseCategoryRepositoryInterface;

class ExpenseCategoryService
{
    private $categoryRepo;

    public function __construct(ExpenseCategoryRepositoryInterface $categoryRepo)
    {
        $this->categoryRepo = $categoryRepo;
    }

    public function getAll()
    {
        return $this->categoryRepo->all();
    }

    public function getPaginated()
    {
        return $this->categoryRepo->paginate();
    }

    public function find($id)
    {
        return $this->categoryRepo->find($id);
    }

    public function store(array $data)
    {
        $data['slug'] = str_slug('expense_categories', 'slug', $data['name']);
        return $this->categoryRepo->create($data);
    }

    public function update($id, array $data)
    {
        if (isset($data['name'])) {
            $data['slug'] = str_slug('expense_categories', 'slug', $data['name'], '-', $id);
        }
        return $this->categoryRepo->update($id, $data);
    }

    public function delete($id)
    {
        return $this->categoryRepo->delete($id);
    }
}