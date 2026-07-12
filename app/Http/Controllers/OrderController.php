<?php

namespace App\Http\Controllers;

use App\Http\Resources\SaleResource;
use App\Services\SaleService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private $saleService;

    public function __construct(SaleService $saleService)
    {
        $this->saleService = $saleService;
    }

    public function index(Request $request)
    {
        $sales = $this->saleService->getSales();

        return apiResourceResponse(
            SaleResource::collection($sales),
            'Orders fetched successfully',
        );
    }

    public function show($id)
    {
        $sale = $this->saleService->find($id);

        return apiResourceResponse(
            new SaleResource($sale),
            'Order fetched successfully',
        );
    }
}