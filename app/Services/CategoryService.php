<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;

class CategoryService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private CategoryRepository $categoryRepository)
    {
    }

    public function getAll()
    {
        return $this->categoryRepository->all();
    }
    public function save(array $data): Category
    {
        $data["slug"] = str_slug('categories', 'slug', $data["name"]);
        return $this->categoryRepository->create($data);
    }

    public function getBySlug(string $slug)
    {
        return $this->categoryRepository->findBySlug($slug);
    }

    public function update(Category $category, array $data): Category
    {
        if (isset($data['name']) && $data['name'] !== $category->name) {
            $data['slug'] = str_slug('categories', 'slug', $data['name'], '-', $category->id);
        }
        return $this->categoryRepository->update($category, $data);
    }

    public function delete(Category $category)
    {
        return $this->categoryRepository->delete($category);
    }
}
