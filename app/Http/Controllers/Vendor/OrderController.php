<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::whereHas('product', function($query) {
                $query->where('shop_id', auth()->user()->shop->id);
            })
            ->with(['product', 'buyer'])
            ->latest()
            ->paginate(15);
            
        return view('vendor.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);
        
        $order->load(['product', 'buyer']);
        
        return view('vendor.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);
        
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        $order->update([
            'status' => $request->status
        ]);

        return redirect()->route('vendor.orders.show', $order)
            ->with('success', 'Order status updated successfully!');
    }

    public function markShipped(Order $order): RedirectResponse
    {
        $this->authorize('update', $order);
        
        $order->update([
            'status' => 'shipped',
            'shipped_at' => now()
        ]);

        return redirect()->route('vendor.orders.index')
            ->with('success', 'Order marked as shipped!');
    }

    public function markDelivered(Order $order): RedirectResponse
    {
        $this->authorize('update', $order);
        
        $order->update([
            'status' => 'delivered',
            'delivered_at' => now()
        ]);

        return redirect()->route('vendor.orders.index')
            ->with('success', 'Order marked as delivered!');
    }
} 