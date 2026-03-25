<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
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
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $order = Order::create([
                'user_id' => auth()->id(),
                'total_amount' => 0,
                'status' => 'pending',
            ]);

            $total = 0;

            foreach ($request->items as $item) {
                $product = Product::find($item['id']);
                $itemTotal = $product->price * $item['quantity'];
                $total += $itemTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price_at_purchase' => $product->price,
                ]);
            }

            $order->update(['total_amount' => $total]);

            $intent = PaymentIntent::create([
                'amount' => $total,
                'currency' => 'usd',
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'order_id' => $order->id,
                    'user_id' => auth()->id(),
                ],
            ]);

            $order->update(['stripe_payment_intent_id' => $intent->id]);

            return response()->json([
                'clientSecret' => $intent->client_secret,
                'order_id' => $order->id
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
