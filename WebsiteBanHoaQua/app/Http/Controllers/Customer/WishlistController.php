<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    public function index()
    {
        $user_id = Auth::user()->id;
        if ($user_id) {
            $wishlists = DB::table('wishlists')
                ->select(
                    'wishlists.id',
                    'products.id as product_id',
                    'products.image',
                    'products.name',
                    'products.slug',
                    'products.code',
                    'products.price',
                    'products.price_sale',
                    'products.quantity'
                )
                ->join('products', 'wishlists.product_id', '=', 'products.id')
                ->where('wishlists.user_id', '=', $user_id)
                ->where('products.status', '=', 1)
                ->orderBy('wishlists.created_at', 'desc')
                ->paginate(5);
            return view('wishlist', ['wishlists' => $wishlists]);
        }
    }

    public function addWishlist(Request $request)
    {
        if (Auth::check()) {
            $data['user_id'] = Auth::user()->id;
            $data['product_id'] = $request->id;
            $check = Wishlist::where('user_id', $data['user_id'])->where('product_id', $data['product_id'])->get()->first();
            if ($check) {
                return response()->json(['is' => 'exist']);
            }
            $product = Product::find($data['product_id']);
            if ($product) {
                $wishlist = Wishlist::create($data);
                if ($wishlist) {
                    return response()->json(['is' => 'success']);
                }
            }
            return response()->json(['is' => 'unsuccess']);
        }
        return response()->json(['is' => 'notlogged']);
    }

    public function delete($id)
    {
        $user_id = Auth::user()->id;
        if ($user_id && $id) {
            Wishlist::where('id', $id)->where('user_id', $user_id)->delete();
        }
    }
}
