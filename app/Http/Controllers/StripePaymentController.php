<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StripePaymentController extends Controller
{
    public function show($id)
    {
        $product = Product::findOrFail($id);

        return Inertia::render('Stripe/Checkout', [
            'product' => $product,
            'stripeKey' => config('services.stripe.key'),
        ]);
    }
}
