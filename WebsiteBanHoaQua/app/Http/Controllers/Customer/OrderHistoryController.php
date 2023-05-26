<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderDetail;

class OrderHistoryController extends Controller
{
    public function myOrder($user_id)
    {
        if (Auth::check()) {
            if ($user_id == Auth::user()->id) {
                $orders = Order::where('user_id', $user_id)->orderBy('created_at', 'desc')->paginate(5);
                return view('my_orders', compact('orders'));
            }
        }
        return view('404');
    }

    public function myOrderDetail($order_id)
    {
        if (Auth::check()) {
            $user_id = Auth::user()->id;
            $order = Order::where('order_id', $order_id)->where('user_id', $user_id)->first();
            if ($order) {
                $order_detail = OrderDetail::where('order_id', $order_id)->where('status', 1)->paginate(5);
                return view('my_orders_detail', compact('order_detail'));
            }
        }
        return view('404');
    }
}
