<?php

use App\Models\Product;
use App\Models\User;
use Osiset\ShopifyApp\Http\Middleware\AuthShop;

beforeEach(function () {
    $this->shop = User::factory()->create();

    // Bypass the Shopify auth middleware for testing
    $this->withoutMiddleware(AuthShop::class);
});

describe('Product Index', function () {
    it('returns products for authenticated shop', function () {
        Product::factory()->count(3)->for($this->shop, 'shop')->create();

        $response = $this->actingAs($this->shop)
            ->getJson('/api/v1/products');

        $response->assertSuccessful()
            ->assertJsonCount(3, 'data');
    });

    it('returns only products belonging to the authenticated shop', function () {
        $otherShop = User::factory()->create();

        Product::factory()->count(3)->for($this->shop, 'shop')->create();
        Product::factory()->count(2)->for($otherShop, 'shop')->create();

        $response = $this->actingAs($this->shop)
            ->getJson('/api/v1/products');

        $response->assertSuccessful()
            ->assertJsonCount(3, 'data');
    });

    it('returns products sorted by latest first', function () {
        $oldProduct = Product::factory()->for($this->shop, 'shop')->create([
            'title' => 'Old Product',
            'created_at' => now()->subDays(5),
        ]);
        $newProduct = Product::factory()->for($this->shop, 'shop')->create([
            'title' => 'New Product',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->shop)
            ->getJson('/api/v1/products');

        $response->assertSuccessful();
        expect($response->json('data.0.title'))->toBe('New Product');
        expect($response->json('data.1.title'))->toBe('Old Product');
    });

    it('paginates products', function () {
        Product::factory()->count(20)->for($this->shop, 'shop')->create();

        $response = $this->actingAs($this->shop)
            ->getJson('/api/v1/products');

        $response->assertSuccessful()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.total', 20);
    });
});

describe('Product Store', function () {
    it('creates a product with valid data', function () {
        $response = $this->actingAs($this->shop)
            ->postJson('/api/v1/products', [
                'title' => 'Test Product',
                'description' => 'A test product description',
                'price' => 29.99,
                'vendor' => 'Test Vendor',
                'status' => 'draft',
            ]);

        $response->assertSuccessful()
            ->assertJsonPath('data.title', 'Test Product')
            ->assertJsonPath('data.price', '29.99')
            ->assertJsonPath('data.vendor', 'Test Vendor');

        $this->assertDatabaseHas('products', [
            'shop_id' => $this->shop->id,
            'title' => 'Test Product',
        ]);
    });

    it('validates required fields', function () {
        $response = $this->actingAs($this->shop)
            ->postJson('/api/v1/products', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    });

    it('validates price is numeric and non-negative', function () {
        $response = $this->actingAs($this->shop)
            ->postJson('/api/v1/products', [
                'title' => 'Test Product',
                'price' => -10,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['price']);
    });

    it('validates status is valid', function () {
        $response = $this->actingAs($this->shop)
            ->postJson('/api/v1/products', [
                'title' => 'Test Product',
                'status' => 'invalid',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    });

    it('stores tags as array', function () {
        $response = $this->actingAs($this->shop)
            ->postJson('/api/v1/products', [
                'title' => 'Test Product',
                'tags' => ['sale', 'featured'],
            ]);

        $response->assertSuccessful();

        $product = Product::first();
        expect($product->tags)->toBe(['sale', 'featured']);
    });
});

describe('Product Show', function () {
    it('returns a product owned by the shop', function () {
        $product = Product::factory()->for($this->shop, 'shop')->create();

        $response = $this->actingAs($this->shop)
            ->getJson("/api/v1/products/{$product->id}");

        $response->assertSuccessful()
            ->assertJsonPath('data.id', $product->id);
    });

    it('returns 403 for product owned by another shop', function () {
        $otherShop = User::factory()->create();
        $product = Product::factory()->for($otherShop, 'shop')->create();

        $response = $this->actingAs($this->shop)
            ->getJson("/api/v1/products/{$product->id}");

        $response->assertForbidden();
    });
});

describe('Product Update', function () {
    it('updates a product owned by the shop', function () {
        $product = Product::factory()->for($this->shop, 'shop')->create([
            'title' => 'Original Title',
        ]);

        $response = $this->actingAs($this->shop)
            ->putJson("/api/v1/products/{$product->id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertSuccessful()
            ->assertJsonPath('data.title', 'Updated Title');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'title' => 'Updated Title',
        ]);
    });

    it('returns 403 for product owned by another shop', function () {
        $otherShop = User::factory()->create();
        $product = Product::factory()->for($otherShop, 'shop')->create();

        $response = $this->actingAs($this->shop)
            ->putJson("/api/v1/products/{$product->id}", [
                'title' => 'Hacked Title',
            ]);

        $response->assertForbidden();
    });

    it('partially updates a product', function () {
        $product = Product::factory()->for($this->shop, 'shop')->create([
            'title' => 'Original Title',
            'description' => 'Original Description',
        ]);

        $response = $this->actingAs($this->shop)
            ->putJson("/api/v1/products/{$product->id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertSuccessful();

        $product->refresh();
        expect($product->title)->toBe('Updated Title');
        expect($product->description)->toBe('Original Description');
    });
});

describe('Product Delete', function () {
    it('deletes a product owned by the shop', function () {
        $product = Product::factory()->for($this->shop, 'shop')->create();

        $response = $this->actingAs($this->shop)
            ->deleteJson("/api/v1/products/{$product->id}");

        $response->assertSuccessful()
            ->assertJsonPath('message', 'Product deleted successfully');

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    });

    it('returns 403 for product owned by another shop', function () {
        $otherShop = User::factory()->create();
        $product = Product::factory()->for($otherShop, 'shop')->create();

        $response = $this->actingAs($this->shop)
            ->deleteJson("/api/v1/products/{$product->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
        ]);
    });
});
