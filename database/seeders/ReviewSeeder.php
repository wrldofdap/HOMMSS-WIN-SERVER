<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $users = User::where('utype', 'USR')->get();

        if ($products->count() > 0 && $users->count() > 0) {
            $reviews = [
                [
                    'rating' => 5,
                    'title' => 'Excellent Quality!',
                    'comment' => 'These tiles are amazing! Great quality and perfect for my kitchen renovation.',
                    'approved' => true,
                ],
                [
                    'rating' => 4,
                    'title' => 'Good value for money',
                    'comment' => 'Nice product, delivery was fast and installation was easy.',
                    'approved' => true,
                ],
                [
                    'rating' => 5,
                    'title' => 'Highly recommended',
                    'comment' => 'Perfect finish and very durable. Will definitely buy again.',
                    'approved' => true,
                ],
                [
                    'rating' => 4,
                    'title' => 'Great product',
                    'comment' => 'Good quality materials and excellent customer service.',
                    'approved' => true,
                ],
                [
                    'rating' => 5,
                    'title' => 'Love it!',
                    'comment' => 'Exactly what I was looking for. Beautiful design and great quality.',
                    'approved' => true,
                ],
            ];

            foreach ($products as $product) {
                // Add 2-3 reviews per product
                $reviewCount = rand(2, 3);
                for ($i = 0; $i < $reviewCount; $i++) {
                    $reviewData = $reviews[array_rand($reviews)];
                    
                    Review::create([
                        'product_id' => $product->id,
                        'user_id' => $users->random()->id,
                        'rating' => $reviewData['rating'],
                        'title' => $reviewData['title'],
                        'comment' => $reviewData['comment'],
                        'approved' => $reviewData['approved'],
                    ]);
                }
            }

            echo "Created reviews for all products!\n";
        } else {
            echo "No products or users found. Please run ProductSeeder and UserSeeder first.\n";
        }
    }
}
