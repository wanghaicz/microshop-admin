<div class="panel-body">
    <table class="table table-striped">
        <col width="60" />
        <col width="100" />
        <col width="100" />
        <col width="400" />
        <col />
        <tr>
            <td>序号</td>
            <td>邮政编码</td>
            <td>省市</td>
            <td>地址</td>
            <td>操作</td>
        </tr>
        @foreach($list as $key=>$val)
        <tr>
            <td>{{$key+1}}</td>
            <td>{{$val->postcode}}</td>
            <td>{{$val->town}}</td>
            <td>{{$val->address}}</td>
            <td>
                <a href="{{url('/admin/member-addredit-'.$val->id)}}" title="编辑">
                    <button class="btn btn-primary btn-xs">编辑</button>
                </a>
                <a href="{{url('/admin/member-addrdelete-'.$val->id)}}" title="删除" onclick="if(!confirm('确定要删除吗？')){return false;};">
                    <button class="btn btn-default btn-xs">删除</button>
                </a>
            </td>
        </tr>
        @endforeach
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>
                <a href="{{url('/admin/member-addradd-'.$member_id)}}" title="新增地址">
                    <button class="btn btn-primary btn-xs">新增地址</button>
                </a>
            </td>
        </tr>
    </table>
</div>