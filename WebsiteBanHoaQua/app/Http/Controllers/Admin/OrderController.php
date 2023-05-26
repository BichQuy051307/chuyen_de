<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::all();
        return view('admin.order.order_lists', ['orders' => $orders]);
    }

    public function pending()
    {
        $orders = Order::where('status', 0)->get();
        return view('admin.order.order_lists_pending', ['orders' => $orders]);
    }

    public function shipped()
    {
        $orders = Order::where('status', 1)->get();
        return view('admin.order.order_lists_shipped', ['orders' => $orders]);
    }

    public function delivered()
    {
        $orders = Order::where('status', 2)->get();
        return view('admin.order.order_lists_delivered', ['orders' => $orders]);
    }

    public function cancel()
    {
        $orders = Order::where('status', 3)->get();
        return view('admin.order.order_lists_cancel', ['orders' => $orders]);
    }

    public function show($order_id)
    {
        $order = Order::where('order_id', $order_id)->first();
        $orders_detail = OrderDetail::where('order_id', $order_id)->get();
        return view('admin.order.order_detail', ['order' => $order, 'orders_detail' => $orders_detail]);
    }

    public function update(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            $data = $request->all();
            $order = Order::find($data['id']);
            if ($order->status == 0) {
                $order->status = 1;
            } else if ($order->status == 1) {
                $order->status = 2;
            } else {
                $order->status = 3;
            }

            if ($order->status == 2) {
                $products_order_detail = DB::table('orders_detail')
                    ->where('order_id', $order->order_id)
                    ->get();

                foreach ($products_order_detail as $item) {
                    $product = DB::table('products')
                        ->select('bought')
                        ->where('id', $item->product_id)
                        ->first();

                    $bought = $product->bought + $item->quantity;

                    DB::table('products')->where('id', $item->product_id)
                        ->update([
                            'bought' => $bought
                        ]);
                }
            }

            $flag = $order->save();
            if ($flag) {
                return response()->json(['is' => 'success', 'complete' => 'Đã cập nhật đơn hàng!']);
            }
        }
        return response()->json(['is' => 'unsuccess', 'uncomplete' => 'Đã xảy ra lỗi!']);
    }

    public function cancelOrder(Request $request)
    {
        $data = $request->all();
        $order = Order::find($data['id']);

        $products_order_detail = DB::table('orders_detail')
            ->where('order_id', $order->order_id)
            ->get();

        foreach ($products_order_detail as $item) {
            $product = DB::table('products')
                ->select('quantity')
                ->where('id', $item->product_id)
                ->first();

            $quantity = $product->quantity + $item->quantity;

            DB::table('products')->where('id', $item->product_id)
                ->update([
                    'quantity' => $quantity
                ]);
        }

        DB::table('orders_detail')->where('order_id', $order->order_id)
            ->update([
                'status' => 0
            ]);

        if (Auth::guard('admin')->check()) {
            $order->status = 3;
            $order->save();
            return response()->json(['is' => 'success', 'complete' => 'Đơn hàng vừa bị hủy!']);
        }

        return response()->json(['is' => 'unsuccess', 'uncomplete' => 'Đơn hàng chưa bị hủy!']);
    }

    public function destroy($id)
    {
        Order::findOrFail($id)->delete();
    }
}
