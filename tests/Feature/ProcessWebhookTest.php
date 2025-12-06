<?php

use App\Jobs\ProcessWebhook;
use App\Models\Product;
use App\Models\User;
use App\Models\WebhookLog;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->shop = User::factory()->create([
        'name' => 'test-shop.myshopify.com',
    ]);
});

describe('Webhook Controller', function () {
    it('creates a webhook log and dispatches job', function () {
        Queue::fake();

        // Use URL encoding for slashes in the type parameter
        $response = $this->postJson('/webhook/products-create', [
            'id' => 123456789,
            'title' => 'Test Product',
        ], [
            'X-Shopify-Shop-Domain' => 'test-shop.myshopify.com',
        ]);

        $response->assertSuccessful()
            ->assertJsonPath('message', 'Webhook received');

        $this->assertDatabaseHas('webhook_logs', [
            'shop_id' => $this->shop->id,
            'topic' => 'products-create',
            'status' => 'pending',
        ]);

        Queue::assertPushed(ProcessWebhook::class);
    });

    it('creates webhook log without shop when domain not found', function () {
        Queue::fake();

        $response = $this->postJson('/webhook/products-create', [
            'id' => 123456789,
        ], [
            'X-Shopify-Shop-Domain' => 'unknown-shop.myshopify.com',
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('webhook_logs', [
            'shop_id' => null,
            'topic' => 'products-create',
        ]);
    });
});

describe('Process Products Create Webhook', function () {
    it('creates a product from webhook payload', function () {
        $log = WebhookLog::create([
            'shop_id' => $this->shop->id,
            'topic' => 'products/create',
            'payload' => [
                'id' => 123456789,
                'title' => 'Webhook Product',
                'body_html' => '<p>Product description</p>',
                'vendor' => 'Test Vendor',
                'product_type' => 'Apparel',
                'tags' => 'new, featured',
                'status' => 'active',
                'variants' => [
                    ['price' => '29.99'],
                ],
            ],
            'status' => 'pending',
        ]);

        (new ProcessWebhook($log))->handle();

        $this->assertDatabaseHas('products', [
            'shop_id' => $this->shop->id,
            'shopify_product_id' => '123456789',
            'title' => 'Webhook Product',
            'description' => '<p>Product description</p>',
            'vendor' => 'Test Vendor',
            'product_type' => 'Apparel',
            'status' => 'active',
        ]);

        $product = Product::first();
        expect($product->tags)->toBe(['new', 'featured']);
        expect($product->price)->toBe('29.99');

        $log->refresh();
        expect($log->status)->toBe('processed');
        expect($log->processed_at)->not->toBeNull();
    });

    it('does not create product without shop association', function () {
        $log = WebhookLog::create([
            'shop_id' => null,
            'topic' => 'products/create',
            'payload' => ['id' => 123456789, 'title' => 'Test'],
            'status' => 'pending',
        ]);

        (new ProcessWebhook($log))->handle();

        $this->assertDatabaseCount('products', 0);
        expect($log->fresh()->status)->toBe('processed');
    });
});

describe('Process Products Update Webhook', function () {
    it('updates an existing product', function () {
        $product = Product::factory()->for($this->shop, 'shop')->create([
            'shopify_product_id' => '123456789',
            'title' => 'Original Title',
        ]);

        $log = WebhookLog::create([
            'shop_id' => $this->shop->id,
            'topic' => 'products/update',
            'payload' => [
                'id' => 123456789,
                'title' => 'Updated Title',
                'body_html' => 'Updated description',
                'status' => 'active',
                'variants' => [['price' => '39.99']],
            ],
            'status' => 'pending',
        ]);

        (new ProcessWebhook($log))->handle();

        $product->refresh();
        expect($product->title)->toBe('Updated Title');
        expect($product->description)->toBe('Updated description');
        expect($product->price)->toBe('39.99');
    });

    it('creates product if it does not exist locally', function () {
        $log = WebhookLog::create([
            'shop_id' => $this->shop->id,
            'topic' => 'products/update',
            'payload' => [
                'id' => 999999999,
                'title' => 'New Product From Update',
                'status' => 'draft',
            ],
            'status' => 'pending',
        ]);

        (new ProcessWebhook($log))->handle();

        $this->assertDatabaseHas('products', [
            'shop_id' => $this->shop->id,
            'shopify_product_id' => '999999999',
            'title' => 'New Product From Update',
        ]);
    });
});

describe('Process Products Delete Webhook', function () {
    it('deletes an existing product', function () {
        $product = Product::factory()->for($this->shop, 'shop')->create([
            'shopify_product_id' => '123456789',
        ]);

        $log = WebhookLog::create([
            'shop_id' => $this->shop->id,
            'topic' => 'products/delete',
            'payload' => ['id' => 123456789],
            'status' => 'pending',
        ]);

        (new ProcessWebhook($log))->handle();

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);

        expect($log->fresh()->status)->toBe('processed');
    });

    it('handles deletion of non-existent product gracefully', function () {
        $log = WebhookLog::create([
            'shop_id' => $this->shop->id,
            'topic' => 'products/delete',
            'payload' => ['id' => 999999999],
            'status' => 'pending',
        ]);

        (new ProcessWebhook($log))->handle();

        expect($log->fresh()->status)->toBe('processed');
    });
});

describe('Process App Uninstalled Webhook', function () {
    it('cleans up shop data on app uninstall', function () {
        Product::factory()->count(5)->for($this->shop, 'shop')->create();

        // Update the shop without hashed password to avoid hashing issues
        $this->shop->forceFill([
            'shopify_token' => 'shpat_test_token',
        ])->save();

        $log = WebhookLog::create([
            'shop_id' => $this->shop->id,
            'topic' => 'app/uninstalled',
            'payload' => [],
            'status' => 'pending',
        ]);

        (new ProcessWebhook($log))->handle();

        $this->assertDatabaseCount('products', 0);

        $this->shop->refresh();
        expect($this->shop->shopify_token)->toBeNull();
        expect($log->fresh()->status)->toBe('processed');
    });

    it('handles uninstall without shop association', function () {
        $log = WebhookLog::create([
            'shop_id' => null,
            'topic' => 'app/uninstalled',
            'payload' => [],
            'status' => 'pending',
        ]);

        (new ProcessWebhook($log))->handle();

        expect($log->fresh()->status)->toBe('processed');
    });
});

describe('Webhook Error Handling', function () {
    it('marks webhook as failed on exception', function () {
        $log = WebhookLog::create([
            'shop_id' => $this->shop->id,
            'topic' => 'products/create',
            'payload' => [], // Empty payload will cause an error when accessing payload['id']
            'status' => 'pending',
        ]);

        (new ProcessWebhook($log))->handle();

        $log->refresh();
        expect($log->status)->toBe('failed');
        expect($log->error)->not->toBeNull();
        expect($log->processed_at)->not->toBeNull();
    });
});
