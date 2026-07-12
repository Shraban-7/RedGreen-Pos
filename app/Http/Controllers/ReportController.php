<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function sales(Request $request)
    {
        $data = $this->reportService->salesReport(
            $request->query('from'),
            $request->query('to'),
            $request->query('customer_id'),
            $request->query('status')
        );

        return apiResponse($data, 'Sales report fetched successfully');
    }

    public function customers(Request $request)
    {
        $data = $this->reportService->customersReport(
            $request->query('from'),
            $request->query('to')
        );

        return apiResponse($data, 'Customers report fetched successfully');
    }

    public function expenses(Request $request)
    {
        $data = $this->reportService->expensesReport(
            $request->query('from'),
            $request->query('to'),
            $request->query('category_id')
        );

        return apiResponse($data, 'Expenses report fetched successfully');
    }

    public function overall(Request $request)
    {
        $data = $this->reportService->overallReport(
            $request->query('from'),
            $request->query('to')
        );

        return apiResponse($data, 'Overall report fetched successfully');
    }
}