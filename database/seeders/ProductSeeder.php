<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = [];

        $faker = Faker::create();
        foreach (range(1, 10) as $index) {
            $product = [
                'user_id' => $index,
                'title' => $faker->word(),
                'description' => $faker->sentence(),
                'price' => $faker->randomFloat(2, 10, 10000),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $products[] = $product;
        }

        DB::table('products')->delete();
        DB::table('products')->insert($products);
    }
}
