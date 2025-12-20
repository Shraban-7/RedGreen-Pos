<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $productService;
    public function __construct(Product $productService)
    {
        $this->brandService = $productService;
    }
}
