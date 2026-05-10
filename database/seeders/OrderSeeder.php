<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\StockMove;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $user      = User::first();
        $customers = Customer::all();
        $products  = Product::all();

        $orders = [
            // Order 1 — completed
            [
                'customer'       => $customers[0],
                'status'         => 'completed',
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'order_date'     => now()->subDays(10),
                'items'          => [
                    ['product' => $products->where('sku', 'IND-GRG')->first(), 'qty' => 5],
                    ['product' => $products->where('sku', 'AQU-600')->first(), 'qty' => 3],
                    ['product' => $products->where('sku', 'ORE-ORI')->first(), 'qty' => 2],
                ],
            ],
            // Order 2 — completed
            [
                'customer'       => $customers[1],
                'status'         => 'completed',
                'payment_method' => 'qris',
                'payment_status' => 'paid',
                'order_date'     => now()->subDays(8),
                'items'          => [
                    ['product' => $products->where('sku', 'RNS-800')->first(),  'qty' => 1],
                    ['product' => $products->where('sku', 'LFB-110')->first(),  'qty' => 3],
                    ['product' => $products->where('sku', 'PEP-190')->first(),  'qty' => 2],
                ],
            ],
            // Order 3 — completed
            [
                'customer'       => $customers[2],
                'status'         => 'completed',
                'payment_method' => 'debit',
                'payment_status' => 'paid',
                'order_date'     => now()->subDays(7),
                'items'          => [
                    ['product' => $products->where('sku', 'AQU-1500')->first(), 'qty' => 4],
                    ['product' => $products->where('sku', 'NES-250')->first(),  'qty' => 2],
                    ['product' => $products->where('sku', 'CHT-SPI')->first(),  'qty' => 3],
                ],
            ],
            // Order 4 — completed
            [
                'customer'       => $customers[3],
                'status'         => 'completed',
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'order_date'     => now()->subDays(6),
                'items'          => [
                    ['product' => $products->where('sku', 'SRI-TAW')->first(),  'qty' => 2],
                    ['product' => $products->where('sku', 'SOS-450')->first(),  'qty' => 4],
                    ['product' => $products->where('sku', 'IND-STO')->first(),  'qty' => 5],
                ],
            ],
            // Order 5 — completed
            [
                'customer'       => $customers[4],
                'status'         => 'completed',
                'payment_method' => 'credit',
                'payment_status' => 'paid',
                'order_date'     => now()->subDays(5),
                'items'          => [
                    ['product' => $products->where('sku', 'PAN-170')->first(),  'qty' => 2],
                    ['product' => $products->where('sku', 'REX-150')->first(),  'qty' => 1],
                    ['product' => $products->where('sku', 'VAS-200')->first(),  'qty' => 2],
                ],
            ],
            // Order 6 — completed
            [
                'customer'       => $customers[5],
                'status'         => 'completed',
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'order_date'     => now()->subDays(4),
                'items'          => [
                    ['product' => $products->where('sku', 'POC-500')->first(),  'qty' => 3],
                    ['product' => $products->where('sku', 'MIZ-500')->first(),  'qty' => 2],
                    ['product' => $products->where('sku', 'LMN-600')->first(),  'qty' => 4],
                ],
            ],
            // Order 7 — completed
            [
                'customer'       => $customers[6],
                'status'         => 'completed',
                'payment_method' => 'qris',
                'payment_status' => 'paid',
                'order_date'     => now()->subDays(3),
                'items'          => [
                    ['product' => $products->where('sku', 'WIP-800')->first(),  'qty' => 1],
                    ['product' => $products->where('sku', 'SOK-900')->first(),  'qty' => 2],
                    ['product' => $products->where('sku', 'SUN-170')->first(),  'qty' => 1],
                ],
            ],
            // Order 8 — processing
            [
                'customer'       => $customers[7],
                'status'         => 'processing',
                'payment_method' => 'cash',
                'payment_status' => 'unpaid',
                'order_date'     => now()->subDays(1),
                'items'          => [
                    ['product' => $products->where('sku', 'IND-GRG')->first(), 'qty' => 10],
                    ['product' => $products->where('sku', 'IND-KAR')->first(), 'qty' => 10],
                    ['product' => $products->where('sku', 'AQU-600')->first(), 'qty' => 6],
                ],
            ],
            // Order 9 — processing
            [
                'customer'       => $customers[0],
                'status'         => 'processing',
                'payment_method' => 'qris',
                'payment_status' => 'unpaid',
                'order_date'     => now()->subHours(5),
                'items'          => [
                    ['product' => $products->where('sku', 'ORB-SIK')->first(), 'qty' => 2],
                    ['product' => $products->where('sku', 'PEP-190')->first(), 'qty' => 3],
                    ['product' => $products->where('sku', 'ROM-MAR')->first(), 'qty' => 4],
                ],
            ],
            // Order 10 — cancelled
            [
                'customer'       => $customers[2],
                'status'         => 'cancelled',
                'payment_method' => 'cash',
                'payment_status' => 'unpaid',
                'order_date'     => now()->subDays(2),
                'items'          => [
                    ['product' => $products->where('sku', 'NES-250')->first(), 'qty' => 5],
                    ['product' => $products->where('sku', 'SOS-450')->first(), 'qty' => 5],
                ],
            ],
            // Order 11 — new
            [
                'customer'       => $customers[3],
                'status'         => 'new',
                'payment_method' => 'cash',
                'payment_status' => 'unpaid',
                'order_date'     => now(),
                'items'          => [
                    ['product' => $products->where('sku', 'AQU-1500')->first(), 'qty' => 2],
                    ['product' => $products->where('sku', 'CHT-SPI')->first(),  'qty' => 2],
                    ['product' => $products->where('sku', 'ORE-ORI')->first(),  'qty' => 3],
                ],
            ],
        ];

        foreach ($orders as $orderData) {
            $details    = $orderData['items'];
            $customer   = $orderData['customer'];
            $status     = $orderData['status'];

            // Hitung total
            $totalPrice = collect($details)->sum(fn($i) => $i['product']->price * $i['qty']);

            $order = Order::create([
                'customer_id'    => $customer->id,
                'order_date'     => $orderData['order_date'],
                'total_price'    => $totalPrice,
                'discount'       => 0,
                'discount_amount' => 0,
                'total_payment'  => $totalPrice,
                'status'         => 'new', // mulai dari new, lalu update agar boot() tidak auto trigger
                'payment_method' => $orderData['payment_method'],
                'payment_status' => $orderData['payment_status'],
            ]);

            // Buat order details
            foreach ($details as $item) {
                OrderDetail::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['product']->id,
                    'quantity'   => $item['qty'],
                    'subtotal'   => $item['product']->price * $item['qty'],
                ]);
            }

            // Buat stock move & update status sesuai kondisi
            if ($status === 'processing') {
                foreach ($order->orderDetails as $detail) {
                    StockMove::create([
                        'product_id'      => $detail->product_id,
                        'user_id'         => $user->id,
                        'quantity'        => $detail->quantity,
                        'type'            => 'out',
                        'order_detail_id' => $detail->id,
                        'reference'       => $order->order_number,
                        'state'           => 'draft',
                    ]);
                }
                $order->updateQuietly(['status' => 'processing']);
            } elseif ($status === 'completed') {
                foreach ($order->orderDetails as $detail) {
                    StockMove::create([
                        'product_id'      => $detail->product_id,
                        'user_id'         => $user->id,
                        'quantity'        => $detail->quantity,
                        'type'            => 'out',
                        'order_detail_id' => $detail->id,
                        'reference'       => $order->order_number,
                        'state'           => 'done',
                    ]);
                }
                $order->updateQuietly(['status' => 'completed']);
            } elseif ($status === 'cancelled') {
                $order->updateQuietly(['status' => 'cancelled']);
            }
            // status 'new' → tidak perlu update
        }
    }
}
