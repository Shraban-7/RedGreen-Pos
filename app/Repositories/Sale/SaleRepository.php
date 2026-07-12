<?php

namespace App\Repositories\Sale;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class SaleRepository implements SaleRepositoryInterface
{
    public function all()
    {
        return Sale::with('items.product', 'payments', 'customer', 'user')->latest()->get();
    }

    public function paginate($limit = 20)
    {
        return Sale::with('items.product', 'payments', 'customer', 'user')->latest()->paginate($limit);
    }

    public function find($id)
    {
        return Sale::with('items.product', 'payments', 'customer', 'user')->findOrFail($id);
    }

    public function create(array $data)
    {
        return Sale::create($data);
    }

    public function update($id, array $data)
    {
        $sale = Sale::findOrFail($id);
        $sale->update($data);
        return $sale;
    }

    public function delete($id)
    {
        $sale = $this->find($id);
        return $sale->delete();
    }

    public function sumBetween($from, $to)
    {
        return (float) Sale::whereBetween('created_at', [$from, $to])
            ->sum('grand_total');
    }

    public function countBetween($from, $to)
    {
        return Sale::whereBetween('created_at', [$from, $to])->count();
    }

    public function recent($limit = 10)
    {
        return Sale::with('customer', 'user')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function dailyTotalsBetween($from, $to)
    {
        return Sale::query()
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(grand_total) as total'))
            ->whereBetween('created_at', [$from, $to])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();
    }

    public function dailyBreakdownBetween($from, $to)
    {
        return Sale::query()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(subtotal) as subtotal'),
                DB::raw('SUM(discount_amount) as discount'),
                DB::raw('SUM(vat_amount) as vat'),
                DB::raw('SUM(grand_total) as net')
            )
            ->whereBetween('created_at', [$from, $to])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();
    }

    public function topProductsBetween($from, $to, $limit = 5)
    {
        return SaleItem::query()
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total) as total_revenue')
            )
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereBetween('sales.created_at', [$from, $to])
            ->groupBy('product_id')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->with('product')
            ->get()
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'name' => optional($item->product)->name,
                    'quantity' => (int) $item->total_quantity,
                    'revenue' => (float) $item->total_revenue,
                ];
            });
    }

    public function paymentBreakdownBetween($from, $to)
    {
        return \App\Models\Payment::query()
            ->select('method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->join('sales', 'sales.id', '=', 'payments.sale_id')
            ->whereBetween('sales.created_at', [$from, $to])
            ->groupBy('method')
            ->get();
    }

    public function customerBreakdownBetween($from, $to, $limit = 10)
    {
        return Sale::query()
            ->select(
                'customer_id',
                DB::raw('COUNT(*) as sales_count'),
                DB::raw('SUM(grand_total) as total_spent'),
                DB::raw('MAX(created_at) as last_purchase')
            )
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->with('customer')
            ->get()
            ->map(function ($row) {
                return [
                    'customer_id' => $row->customer_id,
                    'name' => optional($row->customer)->name,
                    'sales_count' => (int) $row->sales_count,
                    'total_spent' => (float) $row->total_spent,
                    'average_order_value' => $row->sales_count
                        ? round($row->total_spent / $row->sales_count, 2)
                        : 0,
                    'last_purchase' => $row->last_purchase,
                ];
            });
    }
}