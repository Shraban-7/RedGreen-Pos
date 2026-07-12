<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Http\Requests\RefundRequest;
use App\Http\Requests\SaleRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\SaleResource;
use App\Services\SaleService;
use Illuminate\Http\Request;

class PosController extends Controller
{
    private $saleService;

    public function __construct(SaleService $saleService)
    {
        $this->saleService = $saleService;
    }

    public function getSellableProducts()
    {
        $products = $this->saleService->getSellableProducts();

        return apiResponse(
            $products,
            'Sellable products fetched successfully'
        );
    }

    public function index(Request $request)
    {
        $sales = $this->saleService->getSales();

        return apiResourceResponse(
            SaleResource::collection($sales),
            'Sales fetched successfully'
        );
    }

    public function show($id)
    {
        $sale = $this->saleService->find($id);

        return apiResourceResponse(
            new SaleResource($sale),
            'Sale fetched successfully'
        );
    }

    public function store(SaleRequest $request)
    {
        try {
            $sale = $this->saleService->store($request->validated());

            return apiResourceResponse(
                new SaleResource($sale),
                'Sale created successfully',
                [],
                201
            );
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), 400);
        }
    }

    public function update(SaleRequest $request, $id)
    {
        // TODO: Implement update sale functionality
        return errorResponse('Update sale functionality not implemented yet', 501);
    }

    public function void(Request $request, $id)
    {
        try {
            $sale = $this->saleService->void($id);

            return apiResourceResponse(
                new SaleResource($sale),
                'Sale voided successfully'
            );
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), 400);
        }
    }

    public function refund(RefundRequest $request, $id)
    {
        try {
            $sale = $this->saleService->refund($id, $request->validated()['items']);

            return apiResourceResponse(
                new SaleResource($sale),
                'Sale refunded successfully'
            );
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), 400);
        }
    }

    public function addPayment(PaymentRequest $request, $id)
    {
        try {
            $sale = $this->saleService->addPayment($id, $request->validated());

            return apiResourceResponse(
                new SaleResource($sale),
                'Payment added successfully'
            );
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), 400);
        }
    }
}
