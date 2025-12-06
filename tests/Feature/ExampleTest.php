<?php

use App\Models\User;
use Osiset\ShopifyApp\Http\Middleware\AuthShop;

test('the application returns a successful response', function () {
    $this->withoutMiddleware(AuthShop::class);

    $shop = User::factory()->create();

    $response = $this->actingAs($shop)->get('/');

    $response->assertStatus(200);
});
