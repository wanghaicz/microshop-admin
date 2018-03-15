@extends('admin::layout')

@section('title', '权限设置')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <form class="form-horizontal ajax-form" method="POST" action="{{ url('admin/user-setpermission') }}">
                        {{ csrf_field() }}

                        <input type="hidden" name="user_id" value="{{$user_id}}">

                        <ul>
                            @foreach($permissions as $m_name=>$module)
                            <li><b>{{$module['label']}}</b></li>
                            @foreach($module['items'] as $a_name=>$action)
                            <ul>
                                <li>
                                    <input type="checkbox" name="permission[]" value="{{$m_name}}.{{$a_name}}" {{in_array($m_name.'.'.$a_name, $curPermission) ? 'checked' : null}}>
                                    {{$action['label']}}  
                                </li>
                            </ul>
                            @endforeach
                            @endforeach

                            <li><b>项目</b></li>
                            <ul>
                                @foreach($factories as $factory)
                                <li>
                                    <input type="checkbox" name="factory[]" value="{{$factory['id']}}" {{in_array($factory['id'], $curFactory) ? 'checked' : null}}>
                                    {{$factory['company_name']}}
                                </li>
                                @endforeach
                            </ul>
                        </ul>

                        <div class="form-group">
                            <div class="col-md-5 col-md-offset-1">
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
