@extends('admin::layout')

@section('title', '添加账号')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <form class="form-horizontal ajax-form" method="POST" action="{{ url('admin/user-add') }}">
                        {{ csrf_field() }}

                        <div class="form-group">
                            <label class="control-label col-sm-2">上级：</label>
                            <div class="col-md-3">
                                <select name="pid" class="form-control">
                                    @if ($user_id == 1)
                                    <option value="1">---无---</option>
                                    @endif
                                    @foreach ($users as $row)
                                    <option value="{{$row['id']}}">{{$row['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-sm-2">用户名：</label>
                            <div class="col-md-3">
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-sm-2">邮箱：</label>
                            <div class="col-md-3">
                                <input type="text" name="email" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-sm-2">密码：</label>
                            <div class="col-md-3">
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-sm-2">确认密码：</label>
                            <div class="col-md-3">
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-5 col-md-offset-2">
                                <button type="submit" class="btn btn-primary ajax-btn">提交</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
