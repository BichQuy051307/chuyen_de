<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    // search product
    public function search(Request $request){
        $parameter = trim($request->parameter);
        $products = Product::where('name', 'like', "%$parameter%")->where('status', 1)->where('quantity', '>', 0)->orderBy('name', 'asc')->paginate(15);
        return view('search', ['parameter' => $parameter, 'products' => $products]);
    }

    // product detail
    public function productDetail($slug)
    {
        $product_key = 'product' . $slug;
        $current_time = time();
        if (Session::has($product_key)) {
            if ($current_time - Session::get($product_key) > 1800) {
                Product::where('slug', $slug)->firstOrFail()->increment('view_count');
                Session::put(
                    [
                        $product_key => $current_time,
                    ]
                );
            }
        } else {
            Product::where('slug', $slug)->firstOrFail()->increment('view_count');
            Session::put(
                [
                    $product_key => $current_time,
                ]
            );
        }
        $product = Product::where('slug', $slug)->first();
        return view('product_detail', compact('product'));
    }
}
