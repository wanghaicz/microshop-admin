<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Order;
use App\Member;
use App\Item;
use App\Factory;
use App\MemberAddr;
use Validator;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function create(Request $req)
    {
        $member = Auth::guard('api')->user();

        $sTime = strtotime(date('Y-m-d').' 5:00:00');
        $eTime = strtotime(date('Y-m-d').' 19:00:00');
        $cTime = time();
        if($cTime < $sTime || $cTime > $eTime)
        {
            return response()->json([
                'error'=> true,
                'msg' => '每天有效下单时间：5:00-17:00'
            ]);
        }

        $input = $req->input();

        $validator = Validator::make($input, [
            'item_id' => 'required',
            'quality' => 'required',
            'quantity' => 'required',
            'weight' => 'required',
            'volume' => 'required',
            // 'truck_length' => 'required',
            'addr_id' => 'required',
            'factory_id' => 'required',
            'pickup_date' => 'required',
            'pickup_time' => 'required',
        ], [
            'item_id.required' => '参数错误！请重新进入页面后再提交！',
            'quality.required' => '请选择品质！',
            'quantity.required' => '请输入数量！',
            'weight.required' => '请输入总重量！',
            'volume.required' => '请输入总体积！',
            // 'truck_length.required' => '请选择场地最大可进车型！',
            'addr_id.required' => '请选择地址！',
            'factory_id.required' => '请选择电厂！',
            'pickup_date.required' => '请选预计上门提货日期！',
            'pickup_time.required' => '请选提货时间！',
        ]);

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

        $item = Item::find($input['item_id'])->toArray();
        if(!$item)
        {
            return response()->json([
                'error'=> true,
                'msg' => '燃料不存在！'
            ]);
        }

        $quality = ['excellent'=>1, 'good'=>2, 'unknown'=>3];
        if(!array_key_exists($input['quality'], $quality))
        {
            return response()->json([
                'error'=> true,
                'msg' => '请选择品质！'
            ]);
        }

        if(!(($weight = round(floatval($input['weight']), 2)) > 0))
        {
            return response()->json([
                'error'=> true,
                'msg' => '请输入总重量！'
            ]);
        }

        if(!(($quantity = round(floatval($input['quantity']), 1)) > 0))
        {
            return response()->json([
                'error'=> true,
                'msg' => '请输入数量！'
            ]);
        }

        if(!(($volume = round(floatval($input['volume']), 1)) > 0))
        {
            return response()->json([
                'error'=> true,
                'msg' => '请输入总体积！'
            ]);
        }

        $addr = MemberAddr::find($input['addr_id'])->toArray();
        if(!$addr)
        {
            return response()->json([
                'error'=> true,
                'msg' => '提货地址不存在！'
            ]);
        }

        $factory = Factory::find($input['factory_id'])->toArray();
        if(!$factory)
        {
            return response()->json([
                'error'=> true,
                'msg' => '电厂不存在！'
            ]);
        }

        if(time() > strtotime($input['pickup_date'].' '.$input['pickup_time']))
        {
            return response()->json([
                'error'=> true,
                'msg' => '预计上门提货时间不能早于当前！'
            ]);
        }

        try
        {
            $data = [
                'erp_number' => date('Ymd').rand(1000, 9999).$member->id,
                'member_id' => $member->id,
                'ship_from_mobile' => $member->mobile,
                'ship_from_name' => $member->name,
                'ship_from_postcode' => $addr['postcode'],
                'ship_from_town' => $addr['town'],
                'ship_from_address' => $addr['address'],
                'ship_to_id' => $factory['ship_to_id'],
                'pickup_date' => strtotime($input['pickup_date'].' '.$input['pickup_time']),
                'product_code' => $item['product_code'],
                'product_name' => $item['product_name'],
                'unit_type' => $item['unit_type'],
                'quantity' => $quantity,
                'weight' => $weight,
                'volume' => $volume,
                'quality' => $quality[$input['quality']],
                // 'truck_length' => $input['truck_length']
            ];

            Order::create($data);

            $this->toOtms($data['erp_number']);
            
            return response()->json([
                'succ'=> true,
                'msg' => '提交成功！'
            ]);
        }
        catch(\Exception $e)
        {
            return response()->json([
                'error'=> true,
                'msg' => '订单保存失败！'
            ]);
        }

    }

    public function getList(Request $req)
    {
        $member = Auth::guard('api')->user();

        $input = $req->input();

        if($input['status'])
        {
            switch ($input['status']) {
                case 'todo':
                    $status = [0, 1];
                    break;
                case 'doing':
                    $status = [2];
                    break;
                case 'done':
                    $status = [3];
                    break;
                case 'cancel':
                    $status = [4];
                    break;
                default:
                    $status = [-1];
            }

            $orderList = Order::where('member_id', '=', $member->id)->whereIn('status', $status)->orderBy('created_at', 'DESC')->get();
        }
        else
        {
            $orderList = Order::where('member_id', '=', $member->id)->orderBy('created_at', 'DESC')->get();
        }

        $unitTypeArr = [
            '4' => '件'
        ];

        $data = $orderList->toArray();
        foreach($data as $key=>$val)
        {
            $data[$key]['unit'] = $unitTypeArr[$val['unit_type']];
        }

        return response()->json([
            'succ'=> true,
            'msg' => '获取成功！',
            'data' => $data
        ]);
    }

    public function getDetail(Request $req)
    {
        $member = Auth::guard('api')->user();

        $input = $req->input();

        $validator = Validator::make($input, [
            'order_bn' => 'required'
        ], [
            'order_bn.required' => '参数错误！'
        ]);

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

        $dataSet = Order::where('member_id', '=', $member->id)->where('erp_number', '=', $input['order_bn']);
        $order = $dataSet->get()->toArray();
        if(!$order)
        {
            return response()->json([
                'error'=> true,
                'msg' => '参数错误！'
            ]);
        }

        $unitTypeArr = [
            '4' => '件'
        ];
        $order[0]['unit'] = $unitTypeArr[$order[0]['unit_type']];
        $order[0]['pickup_date'] = date('Y-m-d H:i', $order[0]['pickup_date']);

        $factory = Factory::where('ship_to_id', '=', $order[0]['ship_to_id'])->get()->toArray();
        if($factory)
        {
            $order[0]['ship_to_company_name'] = $factory[0]['company_name'];
        }
        else
        {
            $order[0]['ship_to_company_name'] = $order[0]['ship_to_id'];
        }

        $order[0]['dockAppointment'] = $order[0]['dockAppointment'] ? unserialize($order[0]['dockAppointment']) : [];
        $order[0]['orderEvents'] = $order[0]['orderEvents'] ? unserialize($order[0]['orderEvents']) : [];

        if(time() - $order[0]['otms_updated_at'] > 15*60)
        {
            $ret = $this->getOrderInfoFromOtms($order[0]['erp_number']);
            if($ret)
            {
                $orderStatus = array('new', 'released', 'dispatched', 'picked', 'delivered');
                if(in_array($ret['orderStatus'], $orderStatus))
                {
                    if($ret['orderStatus'] == 'delivered')
                    {
                        $data['status'] = 3;
                    }
                    else
                    {
                        if(isset($ret['partnerCode']))
                        {
                            if($ret['partnerCode'] == 'DXWL')
                            {
                                if(isset($ret['dockAppointment']['truckPlate']) && $ret['dockAppointment']['truckPlate'])
                                {
                                    $data['status'] = 2;
                                }
                            }
                            else
                            {
                                $data['status'] = 2;
                            }
                        }
                    }
                }

                $order[0]['dockAppointment'] = $ret['dockAppointment'];
                $order[0]['orderEvents'] = $ret['orderEvents'];

                $data['dockAppointment'] = serialize($ret['dockAppointment']);
                $data['orderEvents'] = serialize($ret['orderEvents']);
                $data['otms_updated_at'] = time();
                $dataSet->update($data);
            }
        }

        $orderEvents = array(
            '订单创建' => 1,
            '订单释放' => 2,
            '订单分配' => 3,
            '订单提货' => 4,
            '订单送达' => 5
        );

        usort($order[0]['orderEvents'], function($a, $b) use ($orderEvents){
            if($orderEvents[$a['name']] == $orderEvents[$b['name']]) return 0;
            else return $orderEvents[$a['name']] > $orderEvents[$b['name']] ? 1 : -1;
        });

        return response()->json([
            'succ'=> true,
            'msg' => '获取成功！',
            'data' => $order[0]
        ]);
    }

    public function cancel(Request $req)
    {
        $member = Auth::guard('api')->user();

        $input = $req->input();

        $validator = Validator::make($input, [
            'order_bn' => 'required',
            'cancel_reason' => 'required'
        ], [
            'order_bn.required' => '参数错误！',
            'cancel_reason.required' => '订单取消原因必选！'
        ]);

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

        $dataSet = Order::where('member_id', '=', $member->id)->where('erp_number', '=', $input['order_bn']);
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
                'cancel_reason' => $input['cancel_reason']
            ]);

            return response()->json([
                'succ'=> true,
                'msg' => '取消成功！'
            ]);
        }
        else
        {
            $status = $this->cancelOrderInOtms($order[0]['erp_number']);
            if($status == 'SUCCESS')
            {
                $dataSet->update([
                    'status' => 4,
                    'cancel_reason' => $input['cancel_reason']
                ]);

                return response()->json([
                    'succ'=> true,
                    'msg' => '取消成功！'
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

    private function getOrderInfoFromOtms($erp_number)
    {
        $data = <<<EOF
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<orderOutboundRequest login="hXPy8zsJ" password="MyZWt8TrnksGosSu" version="0.1">
    <queries>
        <query>
            <attribute>erpNumber</attribute>
                <eq>{$erp_number}</eq>
            </query>
        </queries>
    <includeOrderInfo>true</includeOrderInfo>
</orderOutboundRequest>
EOF;

        $url = 'https://login.otms.cn/ws/orderOutbound';
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

        if($res['total'] > 0)
        {
            $ret['dockAppointment'] = $res['orders']['order']['orderInfo']['dockAppointment'];
            if(isset($ret['dockAppointment']['expectedArrivalTime']) && $ret['dockAppointment']['expectedArrivalTime'])
            {
                $ret['dockAppointment']['expectedArrivalTime'] = date('Y-m-d H:i:s', strtotime($ret['dockAppointment']['expectedArrivalTime']));
            }

            $orderEvents = array(
                'new' => '订单创建',
                'released' => '订单释放',
                'dispatched' => '订单分配',
                'picked' => '订单提货',
                'delivered' => '订单送达'
            );

            if(is_array(current($res['orders']['order']['orderEvents']['event'])))
            {
                foreach($res['orders']['order']['orderEvents']['event'] as $event)
                {
                    if(isset($orderEvents[$event['name']])){
                        $event['name'] = $orderEvents[$event['name']];
                        $event['time'] = date('Y-m-d H:i:s', strtotime($event['time']));
                        $ret['orderEvents'][] = $event;
                    }
                }
            }
            else
            {
                $event = $res['orders']['order']['orderEvents']['event'];
                if(isset($orderEvents[$event['name']])){
                    $event['name'] = $orderEvents[$event['name']];
                    $event['time'] = date('Y-m-d H:i:s', strtotime($event['time']));
                    $ret['orderEvents'][] = $event;
                }
            }

            $ret['orderStatus'] = $res['orders']['order']['orderStatus'];
            if(isset($res['orders']['order']['vendorDetail']['partnerCode']))
            {
                $ret['partnerCode'] = $res['orders']['order']['vendorDetail']['partnerCode'];
            }

            return $ret;
        }

        return false;
    }

    private function toOtms($erp_number)
    {
        try
        {
            $dataSet = Order::where('erp_number', '=', $erp_number);

            $order = $dataSet->get()->toArray();

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

                return true;
            }
            else
            {
                return false;
            }
        }
        catch(\Exception $e)
        {
            return false;
        }
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
