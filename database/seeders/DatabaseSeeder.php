<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $categories = [
            'Minuman',
            'Makanan',
            'Snack',
            'Peralatan',
            'Elektronik',
        ];

        foreach ($categories as $name) {
            Category::create(['name' => $name]);
        }


        $products = [
            // Minuman
            ['name' => 'Aqua Botol 600ml', 'category' => 'Minuman', 'purchase_price' => 2000, 'sale_price' => 3500, 'stock' => 50],
            ['name' => 'Teh Pucuk Harum', 'category' => 'Minuman', 'purchase_price' => 2500, 'sale_price' => 4000, 'stock' => 40],
            ['name' => 'Coca-Cola 330ml', 'category' => 'Minuman', 'purchase_price' => 3000, 'sale_price' => 5000, 'stock' => 30],
            ['name' => 'Sprite Botol', 'category' => 'Minuman', 'purchase_price' => 3000, 'sale_price' => 5000, 'stock' => 25],

            // Makanan
            ['name' => 'Indomie Goreng', 'category' => 'Makanan', 'purchase_price' => 2500, 'sale_price' => 3500, 'stock' => 100],
            ['name' => 'Nasi Bungkus Ayam', 'category' => 'Makanan', 'purchase_price' => 10000, 'sale_price' => 15000, 'stock' => 30],
            ['name' => 'Soto Ayam Instan', 'category' => 'Makanan', 'purchase_price' => 3000, 'sale_price' => 4500, 'stock' => 60],
            ['name' => 'Mie Ayam Cup', 'category' => 'Makanan', 'purchase_price' => 8000, 'sale_price' => 12000, 'stock' => 20],

            // Snack
            ['name' => 'Chitato BBQ', 'category' => 'Snack', 'purchase_price' => 5000, 'sale_price' => 7500, 'stock' => 60],
            ['name' => 'Taro Net', 'category' => 'Snack', 'purchase_price' => 4000, 'sale_price' => 6000, 'stock' => 70],
            ['name' => 'Beng-Beng Mini', 'category' => 'Snack', 'purchase_price' => 1500, 'sale_price' => 2500, 'stock' => 100],
            ['name' => 'SilverQueen Kecil', 'category' => 'Snack', 'purchase_price' => 7000, 'sale_price' => 10000, 'stock' => 40],

            // Peralatan
            ['name' => 'Sendok Plastik', 'category' => 'Peralatan', 'purchase_price' => 300, 'sale_price' => 500, 'stock' => 200],
            ['name' => 'Gelas Plastik', 'category' => 'Peralatan', 'purchase_price' => 500, 'sale_price' => 800, 'stock' => 150],
            ['name' => 'Tissue Gulung', 'category' => 'Peralatan', 'purchase_price' => 4000, 'sale_price' => 7000, 'stock' => 50],
            ['name' => 'Korek Api', 'category' => 'Peralatan', 'purchase_price' => 1000, 'sale_price' => 1500, 'stock' => 100],

            // Elektronik
            ['name' => 'Kipas Angin Mini USB', 'category' => 'Elektronik', 'purchase_price' => 25000, 'sale_price' => 40000, 'stock' => 10],
            ['name' => 'Lampu LED Emergency', 'category' => 'Elektronik', 'purchase_price' => 15000, 'sale_price' => 25000, 'stock' => 15],
            ['name' => 'Power Bank 10000mAh', 'category' => 'Elektronik', 'purchase_price' => 60000, 'sale_price' => 85000, 'stock' => 8],
            ['name' => 'Charger HP Universal', 'category' => 'Elektronik', 'purchase_price' => 20000, 'sale_price' => 30000, 'stock' => 12],
        ];

        foreach ($products as $item) {
            $category = Category::where('name', $item['category'])->first();

            if ($category) {
                Product::create([
                    'name' => $item['name'],
                    'purchase_price' => $item['purchase_price'],
                    'sale_price' => $item['sale_price'],
                    'stock' => $item['stock'],
                    'category_id' => $category->id,
                ]);
            }
        }
    }
}
