<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('admin.category.categories-list', ['categories' => $categories]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|max:255|regex:/^[a-zA-Z0-9_ÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠàáâãèéêìíòóôõùúăđĩũơƯĂẠẢẤẦẨẪẬẮẰẲẴẶẸẺẼỀỀỂưăạảấầẩẫậắằẳẵặẹẻẽếềềểỄỆỈỊỌỎỐỒỔỖỘỚỜỞỠỢỤỦỨỪễệỉịọỏốồổỗộớờởỡợụủứừỬỮỰỲỴÝỶỸửữựỳỵỷỹ_(\s)_(\.)_(\,)_(\-)_(\_)]+$/',
            ],
            [
                'name.required' => 'Loại tài khoản không được để trống',
                'name.max' => 'Loại tài khoản không được nhiều hơn :max kí tự',
                'name.regex' => 'Loại tài khoản không được chứa kí tự đặc biệt',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['is' => 'failed', 'error' => $validator->errors()->all()]);
        }

        $data = $request->all();
        unset($data['_token']);
        $data['slug'] = Str::slug($data['name']);
        $category = Category::create($data);
        if (isset($category)) {
            return response()->json(['is' => 'success', 'complete' => 'Danh mục được thêm thành công']);
        }
        return response()->json(['is' => 'unsuccess', 'uncomplete' => 'Danh mục chưa được thêm']);
    }

    public function show($id)
    {
        $category = Category::find($id);
        return view('admin.category.edit-category', ['category' => $category]);
    }

    public function edit(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|max:255|regex:/^[a-zA-Z0-9_ÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠàáâãèéêìíòóôõùúăđĩũơƯĂẠẢẤẦẨẪẬẮẰẲẴẶẸẺẼỀỀỂưăạảấầẩẫậắằẳẵặẹẻẽếềềểỄỆỈỊỌỎỐỒỔỖỘỚỜỞỠỢỤỦỨỪễệỉịọỏốồổỗộớờởỡợụủứừỬỮỰỲỴÝỶỸửữựỳỵỷỹ_(\s)_(\.)_(\,)_(\-)_(\_)]+$/',
            ],
            [
                'name.required' => 'Loại tài khoản không được để trống',
                'name.max' => 'Loại tài khoản không được nhiều hơn :max kí tự',
                'name.regex' => 'Loại tài khoản không được chứa kí tự đặc biệt',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['is' => 'failed', 'error' => $validator->errors()->all()]);
        }
        $data = $request->all();
        $category = Category::find($data['id']);
        unset($data['_token']);
        unset($data['id']);
        $data['slug'] = Str::slug($data['name']);
        $flag = $category->update($data);
        if ($flag) {
            return response()->json(['is' => 'success', 'complete' => 'Danh mục đã được cập nhật']);
        }
        return response()->json(['is' => 'unsuccess', 'uncomplete' => 'Danh mục chưa được cập nhật']);
    }

    public function destroy($id)
    {
        return Category::findOrFail($id)->delete();
    }
}
