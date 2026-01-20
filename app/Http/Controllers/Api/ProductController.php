<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CapacityService; // Will need to mock/create this if not exists
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', true)
            ->with('category')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->base_price,
                    'currency' => 'AED',
                    'category' => $product->category->name ?? 'General',
                    'has_capacity' => $product->has_capacity,
                ];
            });

        return response()->json(['data' => $products]);
    }

    public function show($id)
    {
        $product = Product::where('is_active', true)->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->base_price,
                'currency' => 'AED',
                'image' => $product->image_url, // Assuming attribute exists or null
                'category' => $product->category->name ?? 'General',
            ]
        ]);
    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'date' => 'required|date|after_or_equal:today',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Simple mock capacity check for now until CapacityService is full verified
        $available = true; 
        if ($product->has_capacity) {
             // Logic to check capacity would go here
             // For now, assume always available unless sold out logic implemented
             // $available = CapacityService::check($product, $request->date, $request->quantity);
        }

        return response()->json([
            'available' => $available,
            'message' => $available ? 'Tickets available' : 'Sold out for this date',
        ]);
    }
}
