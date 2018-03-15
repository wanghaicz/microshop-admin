@extends('admin::layout')

@section('title', '编辑会员')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <form class="form-horizontal ajax-form" action="{{ url('admin/member-save') }}" method="POST">
                            {{csrf_field()}}
                            <input type="hidden" name="member_id" value="{{$data->id}}">
                            <div class="form-group">
                                <label class="control-label col-sm-2">联系人姓名：</label>
                                <div class="col-md-3">
                                    <input type="text" name="name" value="{{$data->name}}" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-sm-2">手机号：</label>
                                <div class="col-md-3">
                                    <input type="text" name="mobile" value="{{$data->mobile}}" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-sm-2">项目电厂：</label>
                                <div class="col-md-3">
                                    <select name="factory_id[]" class="form-control" multiple>
                                        @foreach($factories as $val)
                                        <option value="{{$val['id']}}" {{!in_array($val['id'], $curFactory)?:'selected="selected"'}}>{{$val['company_name']}}</option>
                                        @endforeach
                                    </select>
                                    <span class="help-block">可按住"Ctrl"键多选</span>
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

    <!-- <script type="text/javascript" src="/js/area.js"></script>
    <script type="text/javascript">_init_area();</script> -->


@endsection