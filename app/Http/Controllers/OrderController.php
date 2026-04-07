<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\Product;

class OrderController extends Controller
{
    public function index()
    {
        return Inertia::render('Orders/Index', [
            'orders' => auth()->user()->orders()
                ->with(['items.product'])
                ->latest()
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'total_amount' => $order->total_amount,
                        'status' => $order->status,
                        'created_at' => $order->created_at->format('M d, Y'),
                        'items' => $order->items
                    ];
                })
        ]);
    }

    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Orders/Show', [
            'order' => $order->load('items.product')
        ]);
    }

    public function syncCart(Request $request)
    {
        $request->validate(['items' => 'required|array']);
        
        $request->user()->update([
            'cart_data' => json_encode($request->items)
        ]);

        return response()->json(['status' => 'synced']);
    }

    public function validateStock(Request $request)
    {
        $cartItems = $request->input('items');
        $errors = [];

        foreach ($cartItems as $item) {
            $product = Product::find($item['id']);
            if (!$product || $product->stock < $item['quantity']) {
                $errors[] = "Only {$product->stock} units of {$product->name} are available.";
            }
        }

        return response()->json(['errors' => $errors]);
    }
}
