@extends('admin::layout')

@section('title', '新增电厂')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <form class="form-horizontal ajax-form" action="{{ url('admin/factory-save') }}" method="POST">
                            {{csrf_field()}}
                            <div class="form-group">
                                <label class="control-label col-sm-2">名称：</label>
                                <div class="col-md-3">
                                    <input type="text" name="company_name" class="form-control" required>
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
                                <label class="control-label col-sm-2">外部代码：</label>
                                <div class="col-md-3">
                                    <input type="text" name="ship_to_id" class="form-control" required>
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