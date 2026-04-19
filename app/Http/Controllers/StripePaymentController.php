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
            'coupon_code' => 'nullable|string', // Add this
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $order = Order::create([
                'user_id' => auth()->id(),
                'total_amount' => 0,
                'status' => 'pending',
            ]);

            $subtotal = 0;

            foreach ($request->items as $item) {
                $product = Product::find($item['id']);
                $itemTotal = $product->price * $item['quantity'];
                $subtotal += $itemTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price_at_purchase' => $product->price,
                ]);
            }

            // --- NEW COUPON CALCULATION ---
            $finalTotal = $subtotal;
            if ($request->coupon_code) {
                $coupon = \App\Models\Coupon::where('code', $request->coupon_code)
                            ->where('is_active', true)
                            ->first();

                if ($coupon && (!$coupon->expires_at || $coupon->expires_at->isFuture()) && $coupon->used_count < $coupon->max_uses) {
                    $discountAmount = ($subtotal * ($coupon->discount_percent / 100));
                    $finalTotal = $subtotal - $discountAmount;
                    
                    // Track that the coupon was used
                    $coupon->increment('used_count');
                    $order->update(['coupon_id' => $coupon->id]); // Assuming you add coupon_id to orders
                }
            }
            // ------------------------------

            $order->update(['total_amount' => $finalTotal]);

            $intent = PaymentIntent::create([
                'amount' => (int) $finalTotal, // Stripe needs integers (cents)
                'currency' => 'usd',
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'order_id' => $order->id,
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

    public function applyCoupon(Request $request)
    {
        $coupon = Coupon::where('code', $request->code)
                    ->where('is_active', true)
                    ->first();

        if (!$coupon) {
            return response()->json(['message' => 'Invalid coupon.'], 422);
        }

        // 1. Check Expiry
        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return response()->json(['message' => 'This coupon has expired.'], 422);
        }

        // 2. Check Usage Limit
        if ($coupon->used_count >= $coupon->max_uses) {
            return response()->json(['message' => 'This coupon has reached its usage limit.'], 422);
        }

        return response()->json([
            'code' => $coupon->code,
            'discount_percent' => $coupon->discount_percent
        ]);
    }
}
