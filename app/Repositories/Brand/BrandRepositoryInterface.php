<?php

namespace App\Repositories\Brand;

interface BrandRepositoryInterface
{
    public function getBrands();
    public function paginate($limit = 20);
    public function findBrandById($id);
    public function findBrandBySlug($slug);
    public function createBrand(array $data);
    public function updateBrand($id, array $data);
    public function deleteBrand($id);

    public function updateStatusBySlug($slug, $status);

}
