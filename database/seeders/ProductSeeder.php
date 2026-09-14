<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Quần jean nam',       'category_id' => 1, 'price' => 350000, 'quantity' => 100],
            ['name' => 'Áo thun nữ',          'category_id' => 2, 'price' => 250000, 'quantity' => 80],
            ['name' => 'Phụ kiện thời trang', 'category_id' => 3, 'price' => 150000, 'quantity' => 120],
            ['name' => 'Áo sơ mi nam',        'category_id' => 2, 'price' => 300000, 'quantity' => 50],
            ['name' => 'Quần short nữ',       'category_id' => 1, 'price' => 280000, 'quantity' => 60],
            ['name' => 'Balo mini',           'category_id' => 3, 'price' => 400000, 'quantity' => 40],
            ['name' => 'Áo khoác jean',       'category_id' => 2, 'price' => 500000, 'quantity' => 30],
            ['name' => 'Vòng tay đá',         'category_id' => 3, 'price' => 180000, 'quantity' => 70],
            ['name' => 'Quần tây nam',        'category_id' => 1, 'price' => 320000, 'quantity' => 90],
            ['name' => 'Áo hoodie',           'category_id' => 2, 'price' => 450000, 'quantity' => 55],
        ];

        foreach ($products as $product) {
            Product::create(array_merge($product, [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]));
        }
    }
}
