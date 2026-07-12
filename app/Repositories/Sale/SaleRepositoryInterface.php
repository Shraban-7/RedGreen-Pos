<?php

namespace App\Repositories\Sale;

interface SaleRepositoryInterface
{
    public function all();
    public function paginate($limit = 20);
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function sumBetween($from, $to);
    public function countBetween($from, $to);
    public function recent($limit = 10);
    public function dailyTotalsBetween($from, $to);
    public function dailyBreakdownBetween($from, $to);
    public function topProductsBetween($from, $to, $limit = 5);
    public function paymentBreakdownBetween($from, $to);
    public function customerBreakdownBetween($from, $to, $limit = 10);
}