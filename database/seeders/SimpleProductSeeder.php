<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Seeder;

class SimpleProductSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::first();
        $category = Category::first();

        if ($brand && $category) {
            $products = [
                [
                    'name' => 'Premium Floor Tiles',
                    'slug' => 'premium-floor-tiles',
                    'short_description' => 'High-quality ceramic floor tiles',
                    'description' => 'Premium ceramic floor tiles perfect for modern homes. Durable and easy to maintain.',
                    'regular_price' => 1500.00,
                    'sale_price' => 1200.00,
                    'SKU' => 'FT001',
                    'stock_status' => 'instock',
                    'featured' => 1,
                    'quantity' => 50,
                ],
                [
                    'name' => 'Kitchen Sink Stainless Steel',
                    'slug' => 'kitchen-sink-stainless-steel',
                    'short_description' => 'Durable stainless steel kitchen sink',
                    'description' => 'High-grade stainless steel kitchen sink with modern design. Perfect for any kitchen.',
                    'regular_price' => 2500.00,
                    'sale_price' => 2000.00,
                    'SKU' => 'KS001',
                    'stock_status' => 'instock',
                    'featured' => 1,
                    'quantity' => 25,
                ],
                [
                    'name' => 'Bathroom Wall Tiles',
                    'slug' => 'bathroom-wall-tiles',
                    'short_description' => 'Elegant bathroom wall tiles',
                    'description' => 'Beautiful ceramic wall tiles perfect for bathroom decoration. Water-resistant and easy to clean.',
                    'regular_price' => 800.00,
                    'sale_price' => 650.00,
                    'SKU' => 'WT001',
                    'stock_status' => 'instock',
                    'featured' => 0,
                    'quantity' => 100,
                ],
                [
                    'name' => 'Granite Countertop',
                    'slug' => 'granite-countertop',
                    'short_description' => 'Natural granite countertop',
                    'description' => 'Premium natural granite countertop for kitchen and bathroom. Durable and beautiful.',
                    'regular_price' => 5000.00,
                    'sale_price' => 4500.00,
                    'SKU' => 'GC001',
                    'stock_status' => 'instock',
                    'featured' => 1,
                    'quantity' => 15,
                ],
                [
                    'name' => 'Toilet Bowl Modern',
                    'slug' => 'toilet-bowl-modern',
                    'short_description' => 'Modern design toilet bowl',
                    'description' => 'Contemporary toilet bowl with water-saving technology. Perfect for modern bathrooms.',
                    'regular_price' => 3500.00,
                    'sale_price' => 3000.00,
                    'SKU' => 'TB001',
                    'stock_status' => 'instock',
                    'featured' => 0,
                    'quantity' => 20,
                ],
            ];

            foreach ($products as $productData) {
                Product::updateOrCreate(
                    ['slug' => $productData['slug']],
                    array_merge($productData, [
                        'category_id' => $category->id,
                        'brand_id' => $brand->id,
                    ])
                );
            }

            echo "Created " . count($products) . " products successfully!\n";
        } else {
            echo "Missing brand or category. Please run CategorySeeder and BrandSeeder first.\n";
        }
    }
}
