<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Cart;


class OrderController extends Controller
{
     // Checkout画面表示
    public function checkout()
    {
        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $items = $cart->items;
        $total = $items->sum(fn($item) => $item->price * $item->quantity);

        return view('checkout.index', compact('items', 'total'));
    }

    // 注文確定
    public function placeOrder(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
        ]);

        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $total = $cart->items->sum(fn($item) => $item->price * $item->quantity);

        $order = Order::create([
            'user_id' => Auth::id(),
            'total_amount' => $total,
            'status' => 'pending',
            'payment_method' => 'cod',
            'shipping_address' => $request->shipping_address,
            'phone_number' => $request->phone_number,
        ]);

        foreach ($cart->items as $item) {
            $order->items()->create([
                'product_id' => $item->product_id,
                'product_name' => 'Product #' . $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ]);
        }

        // カートを空にする
        $cart->items()->delete();

        return redirect()->route('order.confirmation', $order->id)->with('success', 'Order placed successfully!');
    }

    // 注文完了画面
    public function confirmation(Order $order)
    {
        return view('checkout.confirmation', compact('order'));
    }
    public function order()
    {
        $all_orders = Order::withTrashed()->latest()->get();

        return view('admin.orders.index', compact('all_orders'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,ordered,progress,delivering,delivered,cancelled',
        ]);

        $order->update($validated);

        return redirect()->route('admin.orders.show')->with('success', 'Order status updated.');
    }
}
