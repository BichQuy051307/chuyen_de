<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ChangePasswordController extends Controller
{
    public function getFormChangePassword(){
        return view('change_password');
    }

    public function changePassword( Request $request ){
        $validator = Validator::make($request->all(), [
            'old_pass'=>'required',
            'new_pass' => 'required|min:8|max:16',
            're_new_pass' => 'required'
        ],
        [
         'old_pass.required'=>'Bạn chưa nhập mật khẩu cũ',
         'new_pass.required' => 'Bạn chưa nhập mật khẩu mới',
         're_new_pass.required' => 'Bạn cần nhập lại mật khẩu mới',
         'new_pass.min' => 'Mật khẩu tối thiểu 8 kí tự',
         'new_pass.max' => 'Mật khẩu tối đa 16 kí tự',
        ]);

        if ($validator->fails()) {
            return response()->json(['is' => 'failed', 'error'=>$validator->errors()->all()]);
        }
        $old_pass = $request->old_pass;
        $new_pass = trim($request->new_pass);
        $re_new_pass = trim($request->re_new_pass);
        if($new_pass !== $re_new_pass) {
            return response()->json(['is' => 'unsuccess', 'uncomplete'=>'Mật khẩu mới không khớp !!']);
        }
        if($new_pass == $old_pass) {
            return response()->json(['is' => 'unsuccess', 'uncomplete'=>'Mật khẩu mới giống mật khẩu hiện tại !!']);
        }
        $id = Auth::user()->id;
        $password = DB::table('users')->find($id)->password;
        if(Hash::check($old_pass,$password)) {
            DB::table('users')->where('id', $id)->update(['password'=>bcrypt($new_pass)]);
            $ck_user = User::find(Auth::user()->id);
            $ck_user->save();
            Auth::logout();
            return response()->json(['is' => 'success', 'complete'=>'Đổi mật khẩu thành công. Mời quý khách hàng đăng nhập lại!']);
        }
        return response()->json(['is' => 'unsuccess', 'uncomplete'=>'Mật khẩu hiện tại không đúng']);
    }
}
