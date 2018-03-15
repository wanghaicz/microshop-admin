<div class="panel-body">
    <form class="form-horizontal ajax-form" method="POST" action="{{ url('admin/order-tootms') }}">
        {{ csrf_field() }}
        <input type="hidden" name="erp_number" value="{{$erp_number}}">
        <div class="form-group">
            <label for="pickup_date" class="col-md-3 control-label">预计提货日期</label>

            <div class="col-md-5">
                <input id="pickup_date" type="text" class="form-control" name="pickup_date" required placeholder="YYYY-MM-DD">
            </div>
        </div>

        <div class="form-group">
            <div class="col-md-5 col-md-offset-3">
                <button type="submit" class="btn btn-primary ajax-btn">提交</button>
            </div>
        </div>
    </form>
</div>