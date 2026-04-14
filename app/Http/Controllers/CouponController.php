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
}