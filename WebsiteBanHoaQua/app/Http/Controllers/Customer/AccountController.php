<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    public function myAccount($user_id)
    {
        if (Auth::check()) {
            if ($user_id == Auth::user()->id) {
                $user = User::where('id', $user_id)->first();
                return view('my_account', compact('user'));
            }
        }
        return view('404');
    }

    public function updateMyAccount(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'address' => 'required'
            ],
            [
                'name.required' => 'Bạn chưa nhập họ tên!!',
                'address.required' => 'Bạn chưa nhập địa chỉ!!',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['is' => 'failed', 'error' => $validator->errors()->all()]);
        }
        $data['user_id'] = $request->user_id;
        $data['name'] = $request->name;
        $data['address'] = $request->address;
        if (Auth::check()) {
            if (Auth::user()->id == $data['user_id']) {
                $user = User::where('id', $data['user_id'])
                    ->update(['name' => $data['name'], 'address' => $data['address']]);
                if ($user) {
                    return response()->json(['is' => 'success', 'complete' => 'Thông tin tài khoản đã được cập nhật']);
                }
                return response()->json(['is' => 'unsuccess', 'uncomplete' => 'Việc cập nhật thông tin tài khoản đã gặp sự cố!']);
            }
        }
        return view('404');
    }
}
