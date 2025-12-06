<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Osiset\ShopifyApp\Objects\Enums\PlanInterval;
use Osiset\ShopifyApp\Objects\Enums\PlanType;

class PlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tableName = config('shopify-app.table_names.plans', 'plans');

        DB::table($tableName)->insertOrIgnore([
            [
                'name' => 'Basic',
                'type' => PlanType::RECURRING()->toNative(),
                'price' => 9.99,
                'interval' => PlanInterval::EVERY_30_DAYS()->toNative(),
                'trial_days' => 7,
                'test' => config('shopify-app.billing_test', true),
                'on_install' => true,
                'capped_amount' => null,
                'terms' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Professional',
                'type' => PlanType::RECURRING()->toNative(),
                'price' => 29.99,
                'interval' => PlanInterval::EVERY_30_DAYS()->toNative(),
                'trial_days' => 14,
                'test' => config('shopify-app.billing_test', true),
                'on_install' => false,
                'capped_amount' => null,
                'terms' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Enterprise',
                'type' => PlanType::RECURRING()->toNative(),
                'price' => 99.99,
                'interval' => PlanInterval::ANNUAL()->toNative(),
                'trial_days' => 30,
                'test' => config('shopify-app.billing_test', true),
                'on_install' => false,
                'capped_amount' => null,
                'terms' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
