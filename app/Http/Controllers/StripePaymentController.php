<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
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

    public function createIntent(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        Stripe::setApiKey(config('services.stripe.secret'));

        $product = Product::find($request->product_id);

        try {
            $intent = PaymentIntent::create([
                'amount' => $product->price,
                'currency' => 'usd',
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                ],
            ]);

            return response()->json([
                'clientSecret' => $intent->client_secret
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
