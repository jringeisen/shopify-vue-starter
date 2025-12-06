<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function show(Request $request)
    {
        $shop = $request->user();

        return response()->json([
            'data' => [
                'id' => $shop->id,
                'name' => $shop->name,
                'email' => $shop->email,
                'shopify_domain' => $shop->shopify_domain,
            ],
        ]);
    }
}
