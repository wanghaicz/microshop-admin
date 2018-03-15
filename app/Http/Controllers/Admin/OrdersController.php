<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\DesktopController;
use App\Order;
use Illuminate\Http\Request;
use Session;
use Shopex\LubanAdmin\Finder;
use Validator;
use App\Item;
use App\Factory;
use Excel;
use App\UserFactory;
use Illuminate\Support\Facades\Auth;

class OrdersController extends DesktopController
{
    public function index(Request $req)
    {
        $input = $req->input();
        if(isset($input['sDate']) && $input['sDate'])
        {
            $sTime = strtotime($input['sDate']);
            $dataSet = Order::where('created_at', '>=', date('Y-m-d H:i:s', $sTime));
            $query[] = 'sDate='.$input['sDate'];
        }
        else
        {
            $sTime = 0;
            $dataSet = Order::where('created_at', '>=', date('Y-m-d H:i:s', $sTime));
        }

        if(isset($input['eDate']) && $input['eDate'])
        {
            $eTime = strtotime($input['eDate']) + 86399;
            $dataSet = $dataSet->where('created_at', '<=', date('Y-m-d H:i:s', $eTime));
            $query[] = 'eDate='.$input['eDate'];
        }
        else
        {
            $eTime = time();
            $dataSet = $dataSet->where('created_at', '<=', date('Y-m-d H:i:s', $eTime));
        }

        $user = Auth::guard()->user();
        if($user->id != 1)
        {
            $userFactory = UserFactory::where('user_id', '=', $user->id)->get()->toArray();
            if($userFactory)
            {
                $factoryId = array_column($userFactory, 'factory_id');
                $factory = Factory::whereIn('id', $factoryId)->get()->toArray();
                if($factory)
                {
                    $shipToId = array_column($factory, 'ship_to_id');
                    $dataSet = $dataSet->whereIn('ship_to_id', $shipToId);
                }
                else
                {
                    $dataSet = $dataSet->whereIn('ship_to_id', [-1]);
                }
            }
            else
            {
                $dataSet = $dataSet->whereIn('ship_to_id', [-1]);
            }
        }

        $finder = Finder::create($dataSet, '订单列表', isset($query)?implode('&', $query):null)
                    ->setId('id')
                    ->addSort('按下单时间倒序', 'created_at', 'desc')
                    ->addSort('按下单时间正序', 'created_at')
                    ->addAction('导出订单数据', '/admin/order-export-'.$sTime.'-'.$eTime)
                    ->addColumn('订单号', 'erp_number')
                    ->addColumn('操作', 'id')->modifier(function($id){
                        $order = Order::find($id);
                        if($order->status == 0)
                        {
                            return '<form class="ajax-form" style="display:inline-block;" method="POST" action="'.url('/admin/order-tootms').'">'.csrf_field().'<input type="hidden" name="erp_number" value="'.$order->erp_number.'"><button type="submit" class="btn btn-primary btn-xs ajax-btn">导入OTMS</button></div></form>
                                    <a target="#modal" data-modal-title="选择订单取消原因" href="'.url('/admin/order-cancel-'.$order->erp_number).'" title="取消订单"><button class="btn btn-default btn-xs">取消订单</button></a>
                                    <a href="'.url('/admin/order-delete-'.$id).'" title="删除" onclick="if(!confirm(\'确定要删除吗？\')){return false;};"><button class="btn btn-default btn-xs">删除</button></a>';
                        }
                        elseif(in_array($order->status, [1, 2]))
                        {
                            return '<a target="#modal" data-modal-title="选择订单取消原因" href="'.url('/admin/order-cancel-'.$order->erp_number).'" title="取消订单"><button class="btn btn-default btn-xs">取消订单</button></a>';
                        }
                    })->html(true)
                    ->addColumn('订单状态', 'status')->modifier(function($status){
                        $arr = [
                            0 => '未处理',
                            1 => '未处理',
                            2 => '处理中',
                            3 => '已完成',
                            4 => '已取消'
                        ];
                        return $arr[$status];
                    })->size(1)
                    ->addColumn('燃料名称', 'product_name')
                    ->addColumn('数量', 'id')->modifier(function($id){
                        $order = Order::find($id);
                        $unitTypeArr = [
                            '4' => '件'
                        ];

                        return $order['quantity'].$unitTypeArr[$order['unit_type']];
                    })->size(1)
                    ->addColumn('重量', 'weight')->modifier(function($weight){
                        return $weight.'吨';
                    })->size(1)
                    ->addColumn('体积', 'volume')->modifier(function($volume){
                        return $volume.'立方米';
                    })->size(1)
                    ->addColumn('收货人ID', 'ship_to_id')->size(1)
                    ->addInfoPanel('订单详情', [$this, 'detail'])
                    ->addTab('全部', [])
                    ->addTab('未处理', [['status', '<=', 1]])
                    ->addTab('处理中', [['status', '=', 2]])
                    ->addTab('已完成', [['status', '=', 3]])
                    ->addTab('已取消', [['status', '=', 4]]);

        $search = '<form class="finder-search-bar"><div class="form-inline"><div class="form-group">下单日期</div> <div class="form-group"><input type="text" class="form-control" name="sDate" placeholder="YYYY-MM-DD" /></div> ~ <div class="form-group"><input type="text" class="form-control" name="eDate" placeholder="YYYY-MM-DD" /></div> <div class="form-group"><button type="submit" class="btn btn-primary">搜索</button></div></div></form>';
        return $finder->view('admin::finder', ['search'=>$search]);
    }

    public function delete($id, Request $req)
    {
        Order::find($id)->delete();
        return redirect('/admin/order-list');
    }

    // public function export(Request $req)
    // {
    //     if($req->isMethod('POST'))
    //     {
    //         $input = $req->input();

    //         $rule = [
    //             'date_start' => 'required|date_format:Y-m-d',
    //             'date_end' => 'required|date_format:Y-m-d'
    //         ];

    //         $msg = [
    //             'required' => '日期必填！',
    //             'date_format' => '日期格式有误！'
    //         ];

    //         $validator = Validator::make($input, $rule, $msg);

    //         if($validator->fails())
    //         {
    //             $messages = $validator->errors();
    //             foreach($messages->all() as $error)
    //             {
    //                 return response()->json([
    //                     'error'=> true,
    //                     'msg' => $error
    //                 ]);
    //             }
    //         }

    //         if(strtotime($input['date_start']) >= strtotime($input['date_end']))
    //         {
    //             return response()->json([
    //                 'error'=> true,
    //                 'msg' => '起始时间不能晚于结束时间！'
    //             ]);
    //         }

    //         return response()->json([
    //             'succ'=> true,
    //             'msg' => '验证成功！',
    //             'redirect' => url('/admin/order-doexport-'.strtotime($input['date_start']).'-'.strtotime($input['date_end']))
    //         ]);
    //     }

    //     return view('admin/order/export'); 
    // }

    public function doExport($sTime, $eTime, Request $req)
    {
        $cellData[] = ['帐户名称', 'ERP号码', '提货日期', '产品名称[1]', '发货人ID', '总重量', '产品数量[1]', '单位[1]', '总体积'];
        $unitTypeArr = [
            '4' => '件'
        ];
        $data = Order::where('created_at', '>=', date('Y-m-d H:i:s', $sTime))->where('created_at', '<=', date('Y-m-d H:i:s', $eTime))->get()->toArray();
        foreach($data as $key=>$val)
        {
            $factory = Factory::where('ship_to_id', '=', $val['ship_to_id'])->get()->toArray();
            $company_name = $factory ? $factory[0]['company_name'] : $val['ship_to_id'];
            $cellData[] = [
                $company_name,
                $val['erp_number'],
                date('Y-m-d H:i:s', $val['pickup_date']),
                $val['product_name'],
                $val['ship_from_name'],
                $val['weight'],
                $val['quantity'],
                $unitTypeArr[$val['unit_type']],
                $val['volume']
            ];
        }

        return Excel::create('订单'.date('Y-m-d', $sTime).'_'.date('Y-m-d', $sTime),function($excel) use ($cellData){
            $excel->sheet('Sheet1', function($sheet) use ($cellData){
                $sheet->rows($cellData);
            });
        })->export('xls');
    }

    public function showCancelReason($erp_number, Request $req)
    {
        return view('admin/order/cancel', ['erp_number'=>$erp_number]);
    }

    public function cancel(Request $req)
    {
        $input = $req->input();

        $rule = [
            'erp_number' => 'required',
            'cancel_reason' => 'required'
        ];

        $msg = [
            'erp_number.required' => '参数有误！',
            'cancel_reason.required' => '订单取消原因必选！'
        ];

        $validator = Validator::make($input, $rule, $msg);

        if($validator->fails())
        {
            $messages = $validator->errors();
            foreach($messages->all() as $error)
            {
                return response()->json([
                    'error'=> true,
                    'msg' => $error
                ]);
            }
        }

        $arr = [
            1 => '订单信息错误',
            2 => '停电',
            3 => '设备故障',
            4 => '天气原因',
            5 => '改送其他地方',
            6 => '其他'
        ];
        if(!$arr[$input['cancel_reason']])
        {
            return response()->json([
                'error'=> true,
                'msg' => '订单取消原因必选！'
            ]);
        }

        $reason = $arr[$input['cancel_reason']];

        if($input['cancel_reason'] == 6)
        {
            if(!$input['other_reason'])
            {
                return response()->json([
                    'error'=> true,
                    'msg' => '订单取消原因必填！'
                ]);
            }

            $reason = $input['other_reason'];
        }

        try
        {
            $dataSet = Order::where('erp_number', '=', $input['erp_number']);

            $order = $dataSet->get()->toArray();
            if(!$order)
            {
                return response()->json([
                    'error'=> true,
                    'msg' => '参数错误！'
                ]);
            }
        
            if($order[0]['status'] == 0)
            {
                $dataSet->update([
                    'status' => 4,
                    'cancel_reason' => $reason
                ]);

                return response()->json([
                    'succ'=> true,
                    'msg' => '取消成功！',
                    'redirect' => url('/admin/order-list')
                ]);
            }
            else
            {
                $status = $this->cancelOrderInOtms($order[0]['erp_number']);
                if($status == 'SUCCESS')
                {
                    $dataSet->update([
                        'status' => 4,
                        'cancel_reason' => $reason
                    ]);

                    return response()->json([
                        'succ'=> true,
                        'msg' => '取消成功！',
                        'redirect' => url('/admin/order-list')
                    ]);
                }
                elseif($status == 'FAILED')
                {
                    return response()->json([
                        'error'=> true,
                        'msg' => '取消失败！'
                    ]);
                }
                elseif($status == 'NOT_RECALLED')
                {
                    return response()->json([
                        'error'=> true,
                        'msg' => '不符合取消条件！'
                    ]);
                }
            }
        }
        catch(\Exception $e)
        {
            return response()->json([
                'error'=> true,
                'msg' => '取消失败！'
            ]);
        }
    }

    private function cancelOrderInOtms($erp_number)
    {
        $data = <<<EOF
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<orderRecallRequest login="hXPy8zsJ" password="MyZWt8TrnksGosSu" version="0.1">
    <orders>
        <order sequence="1">
            <erpNumber>{$erp_number}</erpNumber>
        </order>
    </orders>
</orderRecallRequest>
EOF;

        $url = 'https://login.otms.cn/ws/orderRecall';
        $header[] = "Content-type:application/xml";
        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
        $res = curl_exec($ch);
        curl_close($ch);

        error_log($data."\n\n", 3, __FILE__.'.log');
        error_log($res."\n\n\n", 3, __FILE__.'.log');

        $res = json_decode(json_encode(simplexml_load_string($res)), true);

        return $res['orders']['order']['recallStatus'];
    }

    // public function beforeOtms($erp_number, Request $req)
    // {
    //     return view('admin/order/beforeotms', ['erp_number'=>$erp_number]);
    // }

    public function toOtms(Request $req)
    {
        $input = $req->input();

        $rule = [
            'erp_number' => 'required'
        ];

        $msg = [
            'erp_number.required' => '参数有误！'
        ];

        $validator = Validator::make($input, $rule, $msg);

        if($validator->fails())
        {
            $messages = $validator->errors();
            foreach($messages->all() as $error)
            {
                return response()->json([
                    'error'=> true,
                    'msg' => $error
                ]);
            }
        }

        try
        {
            $dataSet = Order::where('erp_number', '=', $input['erp_number']);

            $order = $dataSet->get()->toArray();
            if(!$order)
            {
                return response()->json([
                    'error'=> true,
                    'msg' => '参数错误！'
                ]);
            }

            $pickup_date = date('Y-m-d', $order[0]['pickup_date']);
            $pickup_time_from = date('H:i:s', $order[0]['pickup_date']);
            $pickup_time_to = date('H:i:s', $order[0]['pickup_date']);
            $order[0]['weight'] *= 1000;

            $factory = Factory::where('ship_to_id', '=', $order[0]['ship_to_id'])->get()->toArray();

            $data = <<<EOF
<?xml version="1.0" encoding="utf-8"?>
<orderImportRequest login="hXPy8zsJ" password="MyZWt8TrnksGosSu" version="0.1">
    <orders>
        <order sequence="1">
            <branch>{$factory[0]['company_name']}</branch>
            <erpNumber>{$order[0]['erp_number']}</erpNumber>
            <allowUpdate>true</allowUpdate>
            <shipFrom>
                <companyName>{$order[0]['ship_from_name']}</companyName>
                <postcode>{$order[0]['ship_from_postcode']}</postcode>
                <town>{$order[0]['ship_from_town']}</town>
                <address>{$order[0]['ship_from_address']}</address>
                <loading>false</loading>
                <contact>
                    <name>{$order[0]['ship_from_name']}</name>
                    <mobile>{$order[0]['ship_from_mobile']}</mobile>
                </contact>
            </shipFrom>
            <shipToExternalId>{$order[0]['ship_to_id']}</shipToExternalId>
            <timeSchedule>
                <pickupDate>{$pickup_date}</pickupDate>
                <pickupTimeFrom>{$pickup_time_from}</pickupTimeFrom>
                <pickupTimeTo>{$pickup_time_to}</pickupTimeTo>
            </timeSchedule>
            <orderLines>
                <orderLine>
                    <cargoDescription>
                        <productCode>{$order[0]['product_code']}</productCode>
                        <productName>{$order[0]['product_name']}</productName>
                        <unitType>{$order[0]['unit_type']}</unitType>
                    </cargoDescription>
                    <quantity>{$order[0]['quantity']}</quantity>
                    <weight>{$order[0]['weight']}</weight>
                    <volume>{$order[0]['volume']}</volume>
                </orderLine>
            </orderLines>
            <autoProcessMode>3</autoProcessMode>
            <cargoDetails>
                <cargoType>1</cargoType>
                <packageType>A</packageType>
            </cargoDetails>
            <transportMode>
                <transportType>LTL</transportType>
                <truckType>2</truckType>
            </transportMode>
        </order>
    </orders>
</orderImportRequest>
EOF;

            $res = $this->put($data);

            if($res)
            {
                $dataSet->update([
                    'status' => 1
                ]);

                return response()->json([
                    'succ'=> true,
                    'msg' => '提交成功！',
                    'redirect' => url('/admin/order-list')
                ]);
            }
            else
            {
                return response()->json([
                    'error'=> true,
                    'msg' => '提交失败！'
                ]);
            }
        }
        catch(\Exception $e)
        {
            return response()->json([
                'error'=> true,
                'msg' => '提交失败！'
            ]);
        }
    }

    public function detail($order_id)
    {
        $arr = [
            0 => '未处理',
            1 => '未处理',
            2 => '处理中',
            3 => '已完成',
            4 => '已取消'
        ];

        $order = Order::find($order_id);

        $order->status = $arr[$order->status];
        return view('admin/order/detail', ['order'=>$order]);
    }

    private function put($data)
    {
        $ch = curl_init();
        $header[] = "Content-type:application/xml";
        curl_setopt($ch, CURLOPT_URL, "https://login.otms.cn/ws/orderImport");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "put");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $res = curl_exec($ch);
        curl_close($ch);//关闭

        error_log($data."\n\n", 3, __FILE__.'.log');
        error_log($res."\n\n\n", 3, __FILE__.'.log');

        $res = json_decode(json_encode(simplexml_load_string($res)), true);

        return $res['orders']['order']['orderNumber'];
    }
}
