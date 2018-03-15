@extends('admin::layout')

@section('title', '编辑地址')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <form class="form-horizontal ajax-form" action="{{ url('admin/member-addrsave') }}" method="POST">
                            {{csrf_field()}}
                            <input type="hidden" name="addr_id" value="{{$data->id}}">
                            <input type="hidden" name="member_id" value="{{$data->member_id}}">

                            <div class="form-group">
                                <label class="control-label col-sm-2">邮政编码：</label>
                                <div class="col-md-3">
                                    <input type="text" name="postcode" class="form-control" value="{{$data->postcode}}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-sm-2">省市：</label>
                                <div class="col-md-3">
                                    <input type="text" name="town" class="form-control" value="{{$data->town}}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-sm-2">地址：</label>
                                <div class="col-md-3">
                                    <input type="text" name="address" class="form-control" value="{{$data->address}}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-5 col-md-offset-2">
                                    <button type="submit" class="btn btn-primary ajax-btn">保存</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection