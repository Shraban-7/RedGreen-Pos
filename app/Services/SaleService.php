<?php

namespace App\Services;

use App\Enums\SaleStatus;
use App\Enums\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Repositories\Sale\SaleRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleService
{
    private $saleRepo;

    public function __construct(SaleRepositoryInterface $saleRepo)
    {
        $this->saleRepo = $saleRepo;
    }

    public function getSales()
    {
        return $this->saleRepo->paginate();
    }

    public function find($id)
    {
        return $this->saleRepo->find($id);
    }

    public function getSellableProducts()
    {
        return Product::with('category', 'brand')
            ->whereRaw('(stock_in - stock_out) > 0')
            ->get();
    }

    public function store($data)
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'];
            $subtotal = 0;
            $saleItemsData = [];

            foreach ($items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $availableStock = $product->stock_in - $product->stock_out;

                if ($availableStock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}. Available: {$availableStock}");
                }

                $unitPrice = $item['unit_price'] ?? $product->selling_price;
                $itemSubtotal = $unitPrice * $item['quantity'];

                $itemDiscountAmount = calculate_discount_amount(
                    $itemSubtotal,
                    $item['discount_type'] ?? null,
                    $item['discount_value'] ?? null
                );

                $itemDiscountedPrice = calculate_discounted_price(
                    $itemSubtotal,
                    $item['discount_type'] ?? null,
                    $item['discount_value'] ?? null
                );

                $vatPercentage = $item['vat_percentage'] ?? 0;
                $vatAmount = calculate_vat($vatPercentage, $itemDiscountedPrice);
                $itemTotal = $itemDiscountedPrice + $vatAmount;

                $subtotal += $itemSubtotal;

                $saleItemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $itemSubtotal,
                    'discount_type' => $item['discount_type'] ?? null,
                    'discount_value' => $item['discount_value'] ?? null,
                    'discount_amount' => $itemDiscountAmount ?? 0,
                    'vat_percentage' => $vatPercentage,
                    'vat_amount' => $vatAmount,
                    'total' => $itemTotal,
                ];

                $product->update([
                    'stock_out' => $product->stock_out + $item['quantity']
                ]);
            }

            $discountAmount = calculate_discount_amount(
                $subtotal,
                $data['discount_type'] ?? null,
                $data['discount_value'] ?? null
            );

            $discountedSubtotal = calculate_discounted_price(
                $subtotal,
                $data['discount_type'] ?? null,
                $data['discount_value'] ?? null
            );

            $vatPercentage = $data['vat_percentage'] ?? 0;
            $vatAmount = calculate_vat($vatPercentage, $discountedSubtotal);
            $grandTotal = $discountedSubtotal + $vatAmount;

            $paidAmount = $data['paid_amount'] ?? 0;
            $dueAmount = max($grandTotal - $paidAmount, 0);

            $sale = $this->saleRepo->create([
                'sale_code' => 'SALE-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
                'user_id' => auth()->id(),
                'customer_id' => $data['customer_id'] ?? null,
                'subtotal' => $subtotal,
                'discount_type' => $data['discount_type'] ?? null,
                'discount_value' => $data['discount_value'] ?? null,
                'discount_amount' => $discountAmount ?? 0,
                'vat_percentage' => $vatPercentage,
                'vat_amount' => $vatAmount,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'status' => SaleStatus::COMPLETED,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($saleItemsData as $itemData) {
                $sale->items()->create($itemData);
            }

            if ($paidAmount > 0) {
                $sale->payments()->create([
                    'payment_code' => 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
                    'user_id' => auth()->id(),
                    'amount' => $paidAmount,
                    'method' => $data['payment_method'] ?? PaymentMethod::CASH->value,
                    'type' => 'payment',
                ]);
            }

            return $this->saleRepo->find($sale->id);
        });
    }

    public function void($saleId)
    {
        return DB::transaction(function () use ($saleId) {
            $sale = $this->saleRepo->find($saleId);

            if ($sale->status === SaleStatus::VOIDED) {
                throw new \Exception('Sale is already voided');
            }

            $this->adjustStock($sale->items, 'restore');

            $this->saleRepo->update($saleId, [
                'status' => SaleStatus::VOIDED,
            ]);

            return $this->saleRepo->find($saleId);
        });
    }

    public function refund($saleId, $refundItems)
    {
        return DB::transaction(function () use ($saleId, $refundItems) {
            $sale = $this->saleRepo->find($saleId);

            if ($sale->status === SaleStatus::VOIDED) {
                throw new \Exception('Cannot refund a voided sale');
            }

            $refundAmount = 0;

            foreach ($refundItems as $refundItem) {
                $saleItem = $sale->items()->findOrFail($refundItem['sale_item_id']);
                $refundQty = $refundItem['quantity'];

                $availableQty = $saleItem->quantity - $saleItem->refunded_quantity;
                if ($availableQty < $refundQty) {
                    throw new \Exception("Cannot refund more than available quantity for {$saleItem->product->name}");
                }

                $saleItem->update([
                    'refunded_quantity' => $saleItem->refunded_quantity + $refundQty
                ]);

                $product = $saleItem->product;
                $product->update([
                    'stock_out' => $product->stock_out - $refundQty
                ]);

                $unitRefund = $saleItem->total / $saleItem->quantity;
                $refundAmount += $unitRefund * $refundQty;
            }

            $totalRefunded = $sale->items->sum('refunded_quantity');
            $totalQuantity = $sale->items->sum('quantity');

            $newStatus = ($totalRefunded >= $totalQuantity)
                ? SaleStatus::REFUNDED
                : SaleStatus::PARTIAL_REFUND;

            $this->saleRepo->update($saleId, [
                'status' => $newStatus,
            ]);

            $sale->payments()->create([
                'payment_code' => 'REF-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
                'user_id' => auth()->id(),
                'amount' => -$refundAmount,
                'method' => PaymentMethod::CASH->value,
                'type' => 'refund',
                'notes' => 'Refund for sale ' . $sale->sale_code,
            ]);

            return $this->saleRepo->find($saleId);
        });
    }

    public function addPayment($saleId, $data)
    {
        return DB::transaction(function () use ($saleId, $data) {
            $sale = $this->saleRepo->find($saleId);

            if ($sale->status === SaleStatus::VOIDED) {
                throw new \Exception('Cannot add payment to a voided sale');
            }

            $payment = $sale->payments()->create([
                'payment_code' => 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
                'user_id' => auth()->id(),
                'amount' => $data['amount'],
                'method' => $data['payment_method'],
                'type' => 'payment',
                'notes' => $data['notes'] ?? null,
            ]);

            $totalPaid = $sale->payments()->where('type', 'payment')->sum('amount');
            $newDueAmount = max($sale->grand_total - $totalPaid, 0);

            $this->saleRepo->update($saleId, [
                'paid_amount' => $totalPaid,
                'due_amount' => $newDueAmount,
            ]);

            return $this->saleRepo->find($saleId);
        });
    }

    protected function adjustStock($items, $action)
    {
        foreach ($items as $item) {
            $product = $item->product;
            $quantity = $item->quantity - $item->refunded_quantity;

            if ($action === 'restore') {
                $product->update([
                    'stock_out' => $product->stock_out - $quantity
                ]);
            } elseif ($action === 'deduct') {
                $product->update([
                    'stock_out' => $product->stock_out + $quantity
                ]);
            }
        }
    }
}
