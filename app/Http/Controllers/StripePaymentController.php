<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Inertia\Inertia;
use Stripe\StripeClient;

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

    public function success(Request $request)
    {
        // Use the Secret from config for security
        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

        try {
            // 1. Retrieve the intent
            $intent = $stripe->paymentIntents->retrieve($request->payment_intent);

            if ($intent->status === 'succeeded') {
                // 2. Find the order
                $order = Order::where('stripe_payment_intent_id', $intent->id)->first();

                if (!$order) {
                    return redirect()->route('dashboard')->with('error', 'Order not found.');
                }

                // 3. Update status if not already paid
                if ($order->status !== 'paid') {
                    $order->update(['status' => 'paid']);
                }

                // 4. REDIRECT to the Order History or specific Order Show page
                // This triggers your new Toast notification!
                return redirect()->route('orders.index')->with('message', 'Payment successful! Your order #' . $order->id . ' is confirmed.');
            }
            
            return redirect()->route('dashboard')->with('error', 'Payment was not successful.');

        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error("Stripe Success Error: " . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Payment verification failed.');
        }
    }

    public function checkout(Request $request)
    {
        $cartItems = $request->input('items'); // Data sent from your Vue frontend
        
        // Convert your cart items into Stripe "Line Items"
        $lineItems = array_map(function($item) {
            return [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $item['name'],
                    ],
                    'unit_amount' => $item['price'], // Price in cents
                ],
                'quantity' => $item['quantity'],
            ];
        }, $cartItems);

        // We return this to Vue to initialize the Stripe Checkout
        return Inertia::render('Stripe/Checkout', [
            'lineItems' => $lineItems,
            'total' => $request->input('total')
        ]);
    }
}
