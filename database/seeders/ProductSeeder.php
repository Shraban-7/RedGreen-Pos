<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch required references
        $category = Category::first();
        $user = User::first();

        if (!$category || !$user) {
            $this->command->error('Category or User missing. Seed them first.');
            return;
        }

        $products = [
            [
                'name' => 'Red T-Shirt',
                'buying_price' => 200,
                'selling_price' => 350,
                'discount_type' => 'percent',
                'discount_value' => 10,
            ],
            [
                'name' => 'Blue Jeans',
                'buying_price' => 650,
                'selling_price' => 950,
                'discount_type' => 'flat',
                'discount_value' => 50,
            ],
            [
                'name' => 'Running Shoes',
                'buying_price' => 1200,
                'selling_price' => 1800,
                'discount_type' => null,
                'discount_value' => null,
            ],
        ];

        foreach ($products as $data) {

            $discountAmount = 0;
            $discountedPrice = $data['selling_price'];

            // ==== Discount calculation ====
            if ($data['discount_type'] === 'percent') {
                $discountAmount = ($data['selling_price'] * $data['discount_value']) / 100;
                $discountedPrice = $data['selling_price'] - $discountAmount;
            } elseif ($data['discount_type'] === 'flat') {
                $discountAmount = $data['discount_value'];
                $discountedPrice = $data['selling_price'] - $discountAmount;
            }

            Product::create([
                'category_id' => $category->id,
                'brand_id' => null, // You said only 1 category and no brand
                'user_id' => $user->id,

                'name' => $data['name'],
                'slug' => Str::slug($data['name']) . '-' . Str::random(5),
                'sku' => 'SKU-' . strtoupper(Str::random(8)),
                'description' => 'Sample description for ' . $data['name'],
                'thumbnail' => null,

                'buying_price' => $data['buying_price'],
                'selling_price' => $data['selling_price'],

                'discount_type' => $data['discount_type'],
                'discount_value' => $data['discount_value'],
                'discount_amount' => $discountAmount,
                'discounted_price' => $discountedPrice,

                'stock_in' => rand(30, 100),
                'stock_out' => rand(0, 10),
                'low_stock_quantity' => 5,
            ]);
        }

        $this->command->info('Products seeded successfully!');
    }
}
