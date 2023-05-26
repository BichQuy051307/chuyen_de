@extends('layouts.admin') 

@section('content') 
@if(isset($user))
<div class="row">
    @csrf
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Cập nhật người dùng</h4>
                <form class="forms-sample" onsubmit="submitForm(event)">
                    @csrf
                    <input
                        name="id"
                        type="hidden"
                        class="form-control"
                        id="userId"
                        value="{{$user->id}}"
                    />
                    <div class="form-group">
                        <label for="name">Tên người dùng</label>
                        <input
                            type="text"
                            class="form-control"
                            id="name"
                            value="{{$user->name}}"
                        />
                    </div>
                    <div class="form-group">
                        <label for="name">Avatar</label>
                        <input
                            type="text"
                            class="form-control"
                            id="avatar"
                            value="{{$user->avatar}}"
                        />
                    </div>
                    <div class="form-group">
                        <label for="name">Địa chỉ</label>
                        <input
                            type="text"
                            class="form-control"
                            id="address"
                            value="{{$user->address}}"
                        />
                    </div>
                    <button
                        type="submit"
                        class="btn btn-primary me-2 text-white btn-save"
                    >
                        Cập nhật
                    </button>
                    <a href="/admin/user" class="btn btn-danger text-white"
                        >Hủy</a
                    >
                </form>
            </div>
        </div>
    </div>
</div>
@endif 
@endsection 

@section('js')
<script type="text/javascript">
    function submitForm(event) {
        event.preventDefault();
    }
    $(".btn-save").click(function () {
        var form_data = new FormData();
        var id = $("#userId").val();
        var name = $("#name").val();
        var address = $("#address").val();
        var avatar = $("#avatar").val();
        if (!name) {
            swal({
                title: "Lỗi!",
                text: "Tên người dùng là bắt buộc",
                icon: "warning",
                buttons: true,
                buttons: ["Ok"],
                timer: 3000,
            });
            return;
        }
        form_data.append("_token", "{{csrf_token()}}");
        form_data.append("id", id);
        form_data.append("name", name);
        form_data.append("address", address);
        form_data.append("avatar", avatar);
        $.ajax({
            type: "post",
            url: "/admin/update-user",
            data: form_data,
            dataType: "json",
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.is === "success") {
                    setTimeout(function () {
                        window.location.href = "/admin/user";
                    }, 1600);
                    swal({
                        title: "Hoàn thành!",
                        text: "Cập nhật người dùng thành công",
                        icon: "success",
                        buttons: true,
                        buttons: ["Ok"],
                        timer: 1500,
                    });
                }
                if (response.is === "unsuccess") {
                    swal({
                        title: "Lỗi!",
                        text: "Không cập nhật được người dùng",
                        icon: "warning",
                        buttons: true,
                        buttons: ["Ok"],
                        timer: 3000,
                    });
                    return;
                }
            },
        });
    });
</script>
@endsection('js')
