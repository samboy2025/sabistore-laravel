<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            [
                'name' => 'Bronze',
                'slug' => 'bronze',
                'description' => 'New vendor badge - awarded after payment',
                'color' => '#CD7F32',
                'min_products' => 0,
                'min_orders' => 0,
                'min_reviews' => 0,
                'order' => 1,
            ],
            [
                'name' => 'Silver',
                'slug' => 'silver',
                'description' => 'Active vendor with good sales performance',
                'color' => '#C0C0C0',
                'min_products' => 5,
                'min_orders' => 5,
                'min_reviews' => 0,
                'order' => 2,
            ],
            [
                'name' => 'Gold',
                'slug' => 'gold',
                'description' => 'Successful vendor with excellent reputation',
                'color' => '#FFD700',
                'min_products' => 10,
                'min_orders' => 10,
                'min_reviews' => 1,
                'order' => 3,
            ],
            [
                'name' => 'Top Vendor',
                'slug' => 'top-vendor',
                'description' => 'Elite vendor with outstanding performance',
                'color' => '#B10020',
                'min_products' => 20,
                'min_orders' => 25,
                'min_reviews' => 5,
                'order' => 4,
            ],
        ];

        foreach ($badges as $badge) {
            Badge::create($badge);
        }
    }
}
