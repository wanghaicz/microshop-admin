<?php
$con = mysql_connect("rm-hp3mq45o6cw2bgdl1.mysql.huhehaote.rds.aliyuncs.com", "kaidi", "OfOWRvuMo59SsXFM");
if (!$con)
{
	die('Could not connect: ' . mysql_error());
}

mysql_select_db("kaidi", $con);

$result = mysql_query("select erp_number,status from orders where status in(1, 2) and (otms_updated_at is null or otms_updated_at<".(time() - 15*60).")");

$erp_number = [];
while($row = mysql_fetch_array($result))
{
	$erp_number[] = $row['erp_number'];
}

foreach($erp_number as $val){
	getOrderInfoFromOtms($val);
}

mysql_close($con);

function getOrderInfoFromOtms($erp_number)
{
    $data_str = <<<EOF
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
    curl_setopt($ch,CURLOPT_POSTFIELDS, $data_str);
    $res = curl_exec($ch);
    curl_close($ch);

    error_log($data_str."\n\n", 3, __FILE__.'.log');
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

        $orderStatus = array('new', 'released', 'dispatched', 'picked', 'delivered');
        if(in_array($ret['orderStatus'], $orderStatus))
        {
            if($ret['orderStatus'] == 'delivered')
            {
                $data[] = "status=3";
            }
            else
            {
                if(isset($ret['partnerCode']))
                {
                    if($ret['partnerCode'] == 'DXWL')
                    {
                        if(isset($ret['dockAppointment']['truckPlate']) && $ret['dockAppointment']['truckPlate'])
                        {
                            $data[] = "status=2";
                        }
                    }
                    else
                    {
                        $data[] = "status=2";
                    }
                }
            }
        }

        $data[] = "dockAppointment='".serialize($ret['dockAppointment'])."'";
        $data[] = "orderEvents='".serialize($ret['orderEvents'])."'";
        $data[] = "otms_updated_at=".time();

        mysql_query("update orders set ".implode(',', $data)." where erp_number=".$erp_number);
    }
}

?>