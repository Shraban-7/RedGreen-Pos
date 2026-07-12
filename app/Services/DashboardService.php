<?php

namespace App\Services;

use App\Repositories\Expense\ExpenseRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\Sale\SaleRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    private $saleRepo;
    private $expenseRepo;
    private $productRepo;

    public function __construct(
        SaleRepositoryInterface $saleRepo,
        ExpenseRepositoryInterface $expenseRepo,
        ProductRepositoryInterface $productRepo
    ) {
        $this->saleRepo = $saleRepo;
        $this->expenseRepo = $expenseRepo;
        $this->productRepo = $productRepo;
    }

    public function getSummary(): array
    {
        return Cache::remember('dashboard_summary', 60, function () {
            $todayStart = Carbon::today()->startOfDay();
            $todayEnd = Carbon::today()->endOfDay();
            $monthStart = Carbon::today()->startOfMonth()->startOfDay();
            $monthEnd = Carbon::today()->endOfMonth()->endOfDay();

            $todaySales = $this->saleRepo->sumBetween($todayStart, $todayEnd);
            $monthSales = $this->saleRepo->sumBetween($monthStart, $monthEnd);

            $todayExpenses = $this->expenseRepo->sumBetween($todayStart, $todayEnd);
            $monthExpenses = $this->expenseRepo->sumBetween($monthStart, $monthEnd);

            $lowStockProducts = $this->productRepo->lowStock();

            $recentSales = $this->saleRepo->recent(10);

            $salesChart = $this->salesTrend($monthStart->toDateString(), $monthEnd->toDateString());

            return [
                'todaySales' => [
                    'count' => $this->saleRepo->countBetween($todayStart, $todayEnd),
                    'total' => (float) $todaySales,
                ],
                'todayExpenses' => (float) $todayExpenses,
                'monthSales' => [
                    'count' => $this->saleRepo->countBetween($monthStart, $monthEnd),
                    'total' => (float) $monthSales,
                ],
                'monthExpenses' => (float) $monthExpenses,
                'lowStockProducts' => $lowStockProducts,
                'recentSales' => $recentSales,
                'salesChart' => $salesChart,
            ];
        });
    }

    public function salesTrend($from, $to): array
    {
        $daily = $this->saleRepo->dailyTotalsBetween($from, $to);

        $start = Carbon::parse($from);
        $end = Carbon::parse($to);
        $labels = [];
        $map = [];

        while ($start->lte($end)) {
            $labels[] = $start->toDateString();
            $map[$start->toDateString()] = 0;
            $start->addDay();
        }

        foreach ($daily as $row) {
            $map[$row->date] = (float) $row->total;
        }

        return [
            'labels' => $labels,
            'data' => array_values($map),
        ];
    }
}