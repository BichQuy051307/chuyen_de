<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required',
                'name' => 'required',
                'password' => 'required|min:8|max:16',
            ],
            [
                'required' => 'Lỗi: Vui lòng nhập các trường bắt buộc (*).',
                'password.min' => 'Mật khẩu tối thiểu 8 kí tự',
                'password.max' => 'Mật khẩu tối đa 16 kí tự',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['is' => 'failed', 'error' => $validator->errors()->all()]);
        }

        $flag = User::where('email', $request->email)->first();
        if ($flag) {
            return response()->json(['is' => 'unsuccess', 'uncomplete' => 'Email đã đăng kí.']);
        }

        $data['email'] = $request->email;
        $data['name'] = $request->name;
        $data['role'] = 'customer';
        $data['password'] = Hash::make($request->password);
        $data['address'] = $request->address;

        $user = User::create($data);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $remember = true)) {
            return response()->json(['is' => 'login-success']);
        }

        return response()->json(['is' => 'success']);
    }
}
