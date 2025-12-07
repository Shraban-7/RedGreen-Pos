<?php

namespace App\Services;

use App\Models\Brand;
use App\Repositories\Brand\BrandRepositoryInterface;

class BrandService
{
    protected $brandRepo;

    public function __construct(BrandRepositoryInterface $brandRepo)
    {
        $this->brandRepo = $brandRepo;
    }

    public function getAll()
    {
        return $this->brandRepo->paginate();
    }

    public function findBySlug($slug)
    {
        return $this->brandRepo->findBySlug($slug);
    }

    public function store($data)
    {
        $data['slug'] = str_slug('brands','slug',$data['name']);
        return $this->brandRepo->create($data);
    }

    public function update(Brand $brand, $data)
    {
         if (isset($data['name']) && $data['name'] !== $brand->name) {
            $data['slug'] = str_slug('categories', 'slug', $data['name'], '-', $brand->id);
        }
        return $this->brandRepo->update($brand->id, $data);
    }

    public function delete(Brand $brand)
    {
        return $this->brandRepo->delete($brand->id);
    }
}
