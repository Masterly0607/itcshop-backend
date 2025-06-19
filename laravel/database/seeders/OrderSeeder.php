<?php
// database/seeders/OrderSeeder.php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // make sure you have products and customers
        $products = Product::all();
        $customers = Customer::all();

        if ($products->count() == 0 || $customers->count() == 0) {
            $this->command->warn('❌ No products or customers to seed orders.');
            return;
        }

        // Create 10 fake orders
        for ($i = 0; $i < 10; $i++) {
            $customer = $customers->random();

            $order = Order::create([
                'customer_id' => $customer->id,
                'total' => 0, // we'll update this below
                'status' => 'pending',
                'payment_method' => 'cash',
                'payment_status' => 'unpaid',
                'shipping_address' => 'Phnom Penh, Cambodia'
            ]);

            $items = $products->random(rand(1, 3)); // 1-3 products per order
            $total = 0;

            foreach ($items as $product) {
                $qty = rand(1, 5);
                $price = $product->price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'price' => $price
                ]);

                $total += $qty * $price;
            }

            $order->update(['total' => $total]);
        }
    }
}
