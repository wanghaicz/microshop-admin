@extends('admin::layout')

@section('title', '添加地址')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <form class="form-horizontal ajax-form" action="{{ url('admin/member-addrsave') }}" method="POST">
                            {{csrf_field()}}
                            <input type="hidden" name="member_id" value="{{$member_id}}">
                            <div class="form-group">
                                <label class="control-label col-sm-2">邮政编码：</label>
                                <div class="col-md-3">
                                    <input type="text" name="postcode" class="form-control">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-sm-2">省市：</label>
                                <div class="col-md-3">
                                    <input type="text" name="town" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-sm-2">地址：</label>
                                <div class="col-md-3">
                                    <input type="text" name="address" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-5 col-md-offset-2">
                                    <button type="submit" class="btn btn-primary ajax-btn">添加</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection