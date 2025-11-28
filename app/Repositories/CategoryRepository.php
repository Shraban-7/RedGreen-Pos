<?php
namespace App\Repositories;

use App\Models\Category;

class CategoryRepository
{
    public function all()
    {
        return Category::with('children')->latest()->get();
    }

    public function find($id)
    {
        return Category::findOrFail($id);
    }

    public function findBySlug($slug)
    {
        return Category::whereSlug($slug)->first();
    }

    public function create(array $data)
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data)
    {
        $category->update($data);
        return $category;
    }

    public function delete(Category $category)
    {
        return $category->delete();
    }
}

