<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductRating;
use Illuminate\Http\Request;

class ProductRatingController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
            'guest_name' => 'nullable|string|max:255',
            'guest_email' => 'nullable|email|max:255',
        ]);

        if ($request->user()) {
            $user = $request->user();
            // Prevent duplicate rating by same user
            $existing = ProductRating::where('user_id', $user->id)->where('product_id', $product->id)->first();
            if ($existing) {
                return back()->with('info', 'You have already rated this product.');
            }
            ProductRating::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'rating' => $request->rating,
                'review' => $request->review,
            ]);
        } else {
            // Prevent duplicate rating by guest email
            if ($request->guest_email) {
                $existing = ProductRating::where('guest_email', $request->guest_email)
                    ->where('product_id', $product->id)->first();
                if ($existing) {
                    return back()->with('info', 'You have already rated this product.');
                }
            }
            ProductRating::create([
                'user_id' => null,
                'product_id' => $product->id,
                'rating' => $request->rating,
                'review' => $request->review,
                'guest_name' => $request->guest_name,
                'guest_email' => $request->guest_email,
            ]);
        }
        return back()->with('success', 'Thank you for your rating!');
    }
} 