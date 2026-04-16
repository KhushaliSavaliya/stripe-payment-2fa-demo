<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CouponController extends Controller
{
    // List all coupons
    public function index()
    {
        return Inertia::render('Admin/Coupons', [
            'coupons' => Coupon::latest()->get()
        ]);
    }

    // Delete a coupon
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return back()->with('message', 'Coupon deleted successfully');
    }

    public function toggle(Coupon $coupon)
    {
        $coupon->update([
            'is_active' => !$coupon->is_active
        ]);

        return back()->with('message', 'Coupon status updated.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code|max:20',
            'discount_percent' => 'required|integer|min:1|max:100',
        ]);

        Coupon::create($validated + ['is_active' => true]);

        return back()->with('message', 'Coupon created successfully!');
    }
}