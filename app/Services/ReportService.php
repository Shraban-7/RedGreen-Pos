<?php

namespace App\Services;

use App\Repositories\Expense\ExpenseRepositoryInterface;
use App\Repositories\Sale\SaleRepositoryInterface;
use Carbon\Carbon;

class ReportService
{
    private $saleRepo;
    private $expenseRepo;

    public function __construct(
        SaleRepositoryInterface $saleRepo,
        ExpenseRepositoryInterface $expenseRepo
    ) {
        $this->saleRepo = $saleRepo;
        $this->expenseRepo = $expenseRepo;
    }

    private function range($from, $to)
    {
        $from = $from ? Carbon::parse($from)->startOfDay() : Carbon::today()->startOfMonth()->startOfDay();
        $to = $to ? Carbon::parse($to)->endOfDay() : Carbon::today()->endOfDay();
        return [$from, $to];
    }

    public function salesReport($from, $to, $customerId = null, $status = null)
    {
        [$from, $to] = $this->range($from, $to);

        $gross = (float) $this->saleRepo->sumBetween($from, $to);
        $count = $this->saleRepo->countBetween($from, $to);
        $breakdown = $this->saleRepo->dailyBreakdownBetween($from, $to);
        $topProducts = $this->saleRepo->topProductsBetween($from, $to);
        $paymentMethods = $this->saleRepo->paymentBreakdownBetween($from, $to);

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'gross_total' => $gross,
            'sale_count' => $count,
            'daily_breakdown' => $breakdown,
            'top_products' => $topProducts,
            'payment_methods' => $paymentMethods,
        ];
    }

    public function customersReport($from, $to)
    {
        [$from, $to] = $this->range($from, $to);

        $customers = $this->saleRepo->customerBreakdownBetween($from, $to);

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'customers' => $customers,
        ];
    }

    public function expensesReport($from, $to, $categoryId = null)
    {
        [$from, $to] = $this->range($from, $to);

        $total = $this->expenseRepo->sumBetween($from, $to);
        $byCategory = $this->expenseRepo->categoryBreakdownBetween($from, $to);

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'total' => (float) $total,
            'by_category' => $byCategory,
        ];
    }

    public function overallReport($from, $to)
    {
        [$from, $to] = $this->range($from, $to);

        $salesTotal = (float) $this->saleRepo->sumBetween($from, $to);
        $expensesTotal = (float) $this->expenseRepo->sumBetween($from, $to);

        $salesDaily = $this->saleRepo->dailyTotalsBetween($from, $to);
        $expensesDaily = $this->expenseRepo->dailyTotalsBetween($from, $to);

        $start = $from->copy();
        $labels = [];
        $salesMap = [];
        $expenseMap = [];

        while ($start->lte($to)) {
            $labels[] = $start->toDateString();
            $salesMap[$start->toDateString()] = 0;
            $expenseMap[$start->toDateString()] = 0;
            $start->addDay();
        }

        foreach ($salesDaily as $row) {
            $salesMap[$row->date] = (float) $row->total;
        }
        foreach ($expensesDaily as $row) {
            $expenseMap[$row->date] = (float) $row->total;
        }

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'total_sales' => $salesTotal,
            'total_expenses' => $expensesTotal,
            'net_profit' => round($salesTotal - $expensesTotal, 2),
            'trend' => [
                'labels' => $labels,
                'sales' => array_values($salesMap),
                'expenses' => array_values($expenseMap),
            ],
        ];
    }
}