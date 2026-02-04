<?php

namespace App\Repositories\Brand;

use App\Models\Brand;

class BrandRepository implements BrandRepositoryInterface
{
    public function getBrands()
    {
        return Brand::latest()->get();
    }

    public function paginate($limit = 20)
    {
        return Brand::latest()->paginate($limit);
    }

    public function findBrandById($id)
    {
        return Brand::findOrFail($id);
    }

    public function findBrandBySlug($slug)
    {
        return Brand::where('slug', $slug)->first();
    }


    public function createBrand(array $data)
    {
        return Brand::create($data);
    }

    public function updateBrand($id, array $data)
    {
        $brand = $this->findBrandById($id);
        $brand->update($data);
        return $brand;
    }

    public function deleteBrand($id)
    {
        $brand = $this->findBrandById($id);
        return $brand->delete();
    }

    public function updateStatusBySlug($slug, $status)
    {
        $brand = Brand::where('slug', $slug)->firstOrFail();
        $brand->update(['status' => $status]);
        return $brand;
    }
}
