<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        // Load brands from JSON file
        $jsonPath = database_path('seeders/data/brands.json');
        if (file_exists($jsonPath)) {
            $brands = json_decode(file_get_contents($jsonPath), true);
        } else {
            // Fallback brands if JSON file doesn't exist
            $brands = [
                [
                    'name' => 'FLOOR TILES',
                    'slug' => 'floor-tiles',
                    'image' => null,
                ],
                [
                    'name' => 'KITCHEN SINK',
                    'slug' => 'kitchen-sink',
                    'image' => null,
                ],
                [
                    'name' => 'SANITARY WARE',
                    'slug' => 'sanitary-ware',
                    'image' => null,
                ],
                [
                    'name' => 'NATURAL GRANITE',
                    'slug' => 'natural-granite',
                    'image' => null,
                ],
                [
                    'name' => 'WALL TILES',
                    'slug' => 'wall-tiles',
                    'image' => null,
                ],
            ];
        }

        foreach ($brands as $brand) {
            Brand::updateOrCreate(
                ['slug' => $brand['slug']],
                [
                    'name' => $brand['name'],
                    'image' => $brand['image'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
