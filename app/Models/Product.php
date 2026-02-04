<?php

namespace App\Models;

use App\Services\ProductService;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function __construct(ProductService $productService) {
    }
    public function index()
    {

    }
}
