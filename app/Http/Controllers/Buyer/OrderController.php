<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display a listing of the buyer's orders
     */
    public function index(): View
    {
        $orders = auth()->user()->orders()
            ->with(['product.shop'])
            ->latest()
            ->paginate(10);

        return view('buyer.orders.index', compact('orders'));
    }

    /**
     * Display the specified order
     */
    public function show(Order $order): View
    {
        // Ensure the order belongs to the authenticated buyer
        if ($order->buyer_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        $order->load(['product.shop']);

        return view('buyer.orders.show', compact('order'));
    }
} 