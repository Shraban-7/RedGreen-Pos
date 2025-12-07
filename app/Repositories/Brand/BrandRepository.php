<?php

namespace App\Repositories\Brand;

use App\Models\Brand;

class BrandRepository implements BrandRepositoryInterface
{
    public function all()
    {
        return Brand::latest()->get();
    }

    public function paginate($limit = 20)
    {
        return Brand::latest()->paginate($limit);
    }

    public function find($id)
    {
        return Brand::findOrFail($id);
    }

    public function findBySlug($slug)
    {
        return Brand::where('slug', $slug)->firstOrFail();
    }


    public function create(array $data)
    {
        return Brand::create($data);
    }

    public function update($id, array $data)
    {
        $brand = $this->find($id);
        $brand->update($data);
        return $brand;
    }

    public function delete($id)
    {
        $brand = $this->find($id);
        return $brand->delete();
    }
}
