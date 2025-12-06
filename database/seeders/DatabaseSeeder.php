<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $shop = User::factory()->create([
            'name' => 'Test Shop',
            'email' => 'test@example.com',
            'shopify_domain' => 'test-shop.myshopify.com',
        ]);

        \App\Models\Product::factory()
            ->count(20)
            ->for($shop, 'shop')
            ->create();

        \App\Models\Product::factory()
            ->count(5)
            ->for($shop, 'shop')
            ->active()
            ->create();
    }
}
