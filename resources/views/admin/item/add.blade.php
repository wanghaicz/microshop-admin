@extends('admin::layout')

@section('title', '添加燃料')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <form class="form-horizontal ajax-form" action="{{ url('admin/item-save') }}" method="POST">
                            {{csrf_field()}}
                            <div class="form-group">
                                <label class="control-label col-sm-2">燃料名称：</label>
                                <div class="col-md-3">
                                    <input type="text" name="product_name" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-sm-2">燃料代码：</label>
                                <div class="col-md-3">
                                    <input type="text" name="product_code" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-sm-2">排序：</label>
                                <div class="col-md-3">
                                    <input type="text" name="sort" class="form-control">
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