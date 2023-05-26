<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {
        if (Session::has('cart')) {
            $cart = Session::get('cart');

            foreach ($cart as $id => $product) {
                $item = Product::where('id', $id)->first();
                if ($product['qty'] > $item->quantity) {
                    return view('shopping_cart', ['limit' => 'Sản phẩm ' . $product['name'] . ' không đáp ứng đủ số lượng! Sản phẩm này hiện có số lượng là ' . $item->quantity . '.']);
                }
            }

            $order_id = "ORD" . "" . date('YmdHis') . strtoupper(Str::random(3));
            foreach ($cart as $id => $product) {
                $data = [];
                $data['order_id'] = $order_id;
                $data['product_id'] = $id;
                $data['name'] = $product['name'];
                $data['slug'] = $product['slug'];
                $data['code'] = $product['code'];
                $data['image'] = $product['image'];
                $data['price'] = $product['price'];
                $data['price_sale'] = $product['price_sale'];
                $data['quantity'] = $product['qty'];

                OrderDetail::create($data);
            }

            $orders = OrderDetail::where('status', 0)->where('order_id', $order_id)->get();
            return view('checkout', ['orders' => $orders, 'order_id' => $order_id]);
        }
        return view('checkout');
    }

    public function order(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|max:255',
                'phone' => 'required|max:11',
                'address' => 'required',
            ],
            [
                'name.required' => 'Tên khách hàng không được để trống',
                'name.max' => 'Tên khách hàng không được nhiều hơn 255 kí tự',
                'phone.required' => 'Số điện thoại không được để trống',
                'address.required' => 'Địa chỉ nhận hàng không được để trống',
                'phone.max' => 'Số điện thoại không quá 11 số',
            ]
        );

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        } else {
            $data['order_id'] = $request->order_id;
            $data['amount'] = $request->amount;

            // upload anh 
            $time = time();
            if ($files = $request->file('image_prescriptions')) {
                $destinationPath = 'images/prescriptions/'; // upload path
                $time = time();
                $fileName = $time . "" . date('YmdHis') . "" . $files->hashName();
                $files->move($destinationPath, $fileName);
                $data['image'] = $fileName;
            }

            if ($request->user_id) {
                $data['user_id'] = $request->user_id;
                if ($request->score_awards == 1) {
                    $score = DB::table('users')->select('score_awards')->where('id', $data['user_id'])->first();
                    $data['score_awards'] = $score->score_awards;
                    if ($request->score_awards_payment) {
                        $input_score = (float) $request->score_awards_payment;
                        if ($input_score <= 0 || $input_score > $score->score_awards) {
                            return Redirect::back()->withErrors('Số điểm thanh toán không hợp lệ!');
                        } else {
                            if ($input_score <= $data['amount']) {
                                $data['amount'] = $data['amount'] - $input_score;
                                DB::table('users')->where('id', $data['user_id'])
                                    ->update([
                                        'score_awards' => $data['score_awards'] - $input_score,
                                    ]);
                                $data['score_awards'] = $input_score;
                            } else {
                                DB::table('users')->where('id', $data['user_id'])
                                    ->update([
                                        'score_awards' => $data['score_awards'] - $data['amount'],
                                    ]);
                                $data['score_awards'] = $data['amount'];
                                $data['amount'] = 0;
                            }
                        }
                    } else {
                        return Redirect::back()->withErrors('Số điểm thanh toán không hợp lệ!');
                    }
                }
            }

            $data['name'] = $request->name;
            $data['email'] = $request->email;
            $data['phone'] = $request->phone;
            $data['address'] = $request->address;
            $data['customer_notes'] = $request->note;

            $order = Order::updateOrCreate($data);

            if (Session::has('cart')) {
                Session::forget('cart');
            }

            if ($order) {
                $products_order_detail = DB::table('orders_detail')
                    ->where('order_id', $order->order_id)
                    ->get();

                foreach ($products_order_detail as $item) {
                    $product = DB::table('products')
                        ->select('quantity')
                        ->where('id', $item->product_id)
                        ->first();

                    $quantity = $product->quantity - $item->quantity;
                    if ($quantity < 0) {
                        return Redirect::back()->withErrors('Sản phẩm ' . $product->name . ' không đáp ứng đủ số lượng! Sản phẩm này hiện có số lượng là ' . $product->quantity . '.');
                    }
                    DB::table('products')->where('id', $item->product_id)
                        ->update([
                            'quantity' => $quantity
                        ]);
                }

                $order_detail = DB::table('orders_detail')->where('order_id', $order->order_id)
                    ->update([
                        'status' => 1
                    ]);

                if ($order_detail) {
                    return redirect('/checkout/order-received/' . $order->order_id);
                } else {
                    DB::table('orders')->where('order_id', $order->order_id)
                        ->update([
                            'notes' => 'Lỗi hệ thống đã xảy ra',
                            'status' => 3,
                        ]);
                    return view('500');
                }
            }
            return view('500');
        }
    }

    public function orderReceived($order_id)
    {
        $order = Order::where('order_id', $order_id)->first();
        $order_detail = OrderDetail::where('order_id', $order_id)->where('status', 1)->get();
        if (isset($order) && isset($order_detail)) {
            return view('order_received', [
                'success' => 'Cám ơn bạn. Đơn hàng của bạn đã được tiếp nhận.',
                'order' => $order, 'order_detail' => $order_detail
            ]);
        }
        return view('404');
    }
}
