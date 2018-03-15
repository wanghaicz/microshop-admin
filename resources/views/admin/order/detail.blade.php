<div class="panel-body">
    <table class="table table-striped">
        <col width="60" />
        <col width="200" />
        <col width="60" />
        <col width="200" />
        <tr>
            <th>订单编号</th>
            <td>{{$order->erp_number}}</td>
            <th>收货人ID</th>
            <td>{{$order->ship_to_id}}</td>
        </tr>
        <tr>
            <th>状态</th>
            <td>{{$order->status}}</td>
            <th>发货人姓名</th>
            <td>{{$order->ship_from_name}}</td>
        </tr>
        <tr>
            <th>订单取消原因</th>
            <td>{{$order->cancel_reason}}</td>
            <th>发货人手机号</th>
            <td>{{$order->ship_from_mobile}}</td>
        </tr>
        <tr>
            <th>燃料名称</th>
            <td>{{$order->product_name}}</td>
            <th>发货人邮编</th>
            <td>{{$order->ship_from_postcode}}</td>
        </tr>
        <tr>
            <th>重量</th>
            <td>{{$order->weight}}吨</td>
            <th>发货人省市</th>
            <td>{{$order->ship_from_town}}</td>
        </tr>
        <tr>
            <th>数量</th>
            <td>{{$order->volume}}件</td>
            <th>发货人地址</th>
            <td>{{$order->ship_from_address}}</td>
        </tr>
        <tr>
            <th>体积</th>
            <td>{{$order->volume}}立方米</td>
            <th>预计提货时间</th>
            <td>{{$order->volume}}立方米</td>
        </tr>
    </table>
</div>