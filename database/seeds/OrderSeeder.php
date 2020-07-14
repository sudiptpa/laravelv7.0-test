<?php

use App\Order;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (range(1, 100) as $key => $number) {
            $query = Order::create([
                'transaction_id' => null,
                'amount' => 5 + $number,
                'payment_status' => 0,
            ]);
        }
    }
}
