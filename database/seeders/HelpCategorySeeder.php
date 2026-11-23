<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HelpCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('help_categories')->upsert([
            [
                'name' => 'Light Work',
                'slug' => 'light_work',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Food & Beverage',
                'slug' => 'food_beverage',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Nursing & Caregiving',
                'slug' => 'nursing_caregiving',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Retail & Sales',
                'slug' => 'retail_sales',
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Office Work',
                'slug' => 'office_work',
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Event',
                'slug' => 'event',
                'sort_order' => 6,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Professional Occupations',
                'slug' => 'professional_occupations',
                'sort_order' => 7,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Customer Service',
                'slug' => 'customer_service',
                'sort_order' => 8,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Entertainment',
                'slug' => 'entertainment',
                'sort_order' => 9,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Delivery & Driving',
                'slug' => 'delivery_driving',
                'sort_order' => 10,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Other',
                'slug' => 'other',
                'sort_order' => 11,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['slug'], [
            'name',
            'sort_order',
            'is_active',
            'updated_at',
        ]);
    }
}
