<div class="panel-body">
    <form class="form-horizontal ajax-form" method="POST" action="{{ url('admin/member-savepwd') }}">
        {{ csrf_field() }}
        <input type="hidden" name="member_id" value="{{$member_id}}">
        <div class="form-group">
            <label for="password" class="col-md-2 control-label">新密码</label>

            <div class="col-md-3">
                <input id="password" type="text" class="form-control" name="password" required>
            </div>
        </div>

        <div class="form-group">
            <div class="col-md-5 col-md-offset-2">
                <button type="submit" class="btn btn-primary ajax-btn">提交</button>
            </div>
        </div>
    </form>
</div>