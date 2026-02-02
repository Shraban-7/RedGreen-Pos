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
        $data['slug'] = str_slug('brands', 'slug', $data['name']);
        if (isset($data['image']) && $data['image']) {
            $data['image'] = upload_file($data['image'], 'brands');
        }
        return $this->brandRepo->create($data);
    }

    public function update(Brand $brand, $data)
    {
        if (isset($data['name']) && $data['name'] !== $brand->name) {
            $data['slug'] = str_slug('brands', 'slug', $data['name'], '-', $brand->id);
        }

        if (isset($data['image']) && $data['image']) {
            if ($brand->image) {
                delete_file($brand->image);
            }

            $data['image'] = upload_file($data['image'], 'brands');
        }

        return $this->brandRepo->update($brand->id, $data);
    }


    public function delete(Brand $brand)
    {
        return $this->brandRepo->delete($brand->id);
    }

    public function updateStatus(Brand $brand, $status)
    {
        return $this->brandRepo->updateStatusBySlug($brand->slug, $status);
    }

}
