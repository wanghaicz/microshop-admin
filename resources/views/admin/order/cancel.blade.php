<div class="panel-body">
    <form class="form-horizontal ajax-form" method="POST" action="{{ url('admin/order-cancel') }}">
        {{ csrf_field() }}
        <input type="hidden" name="erp_number" value="{{$erp_number}}">
        <div class="form-group">
            <ul>
                <li>
                    <label>
                        <input type="radio" name="cancel_reason" value="1">
                        <span>订单信息错误</span>
                    </label>
                </li>
                <li>
                    <label>
                        <input type="radio" name="cancel_reason" value="2">
                        <span>停电</span>
                    </label>
                </li>
                <li>
                    <label>
                        <input type="radio" name="cancel_reason" value="3">
                        <span>设备故障</span>
                    </label>
                </li>
                <li>
                    <label>
                        <input type="radio" name="cancel_reason" value="4">
                        <span>天气原因</span>
                    </label>
                </li>
                <li>
                    <label>
                        <input type="radio" name="cancel_reason" value="5">
                        <span>改送其他地方</span>
                    </label>
                </li>
                <li>
                    <label>
                        <input type="radio" name="cancel_reason" value="6">
                        <span>其他</span>
                        <input type="text" name="other_reason">
                    </label>
                </li>
            </ul>
        </div>

        <div class="form-group">
            <div class="col-md-5 col-md-offset-1">
                <button type="submit" class="btn btn-primary ajax-btn">提交</button>
            </div>
        </div>
    </form>
</div>