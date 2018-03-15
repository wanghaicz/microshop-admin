<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Item;
use App\Order;
use Validator;
use Illuminate\Support\Facades\Auth;
use DB;

class ItemController extends Controller
{
    public function getList(Request $req)
    {
        $input = $req->input();

        $member = Auth::guard('api')->user();
        if($member)
        {
            if(isset($input['search']) && trim($input['search']))
            {
                $itemList = DB::select("select i.* from items i left join (select product_code,count(*) as _count from orders where member_id=".$member->id." group by product_code) c on c.product_code=i.product_code where i.product_name like '%".$input['search']."%' order by c._count desc,sort asc");
            }
            else
            {
                $itemList = DB::select("select i.* from items i left join (select product_code,count(*) as _count from orders where member_id=".$member->id." group by product_code) c on c.product_code=i.product_code order by c._count desc,sort asc");
            }

            foreach($itemList as $key=>$val){
                $itemList[$key] = json_decode(json_encode($val), true);
            }
        }
        else
        {
            if(isset($input['search']) && trim($input['search']))
            {
                $itemList = Item::where('product_name', 'like', "%{$input['search']}%")->orderBy('sort', 'ASC')->get()->toArray();
            }
            else
            {
                $itemList = Item::orderBy('sort', 'ASC')->get()->toArray();
            }
        }

        return response()->json([
            'succ'=> true,
            'msg' => '获取成功！',
            'data' => $itemList
        ]);
    }

    // public function getRecommend(Request $req)
    // {   
    //     $member = Auth::guard('api')->user();
    //     if($member)
    //     {
    //         $orderProduct = Order::select(DB::raw('product_code,count(*) as count'))
    //                                 ->where('member_id', '=', $member->id)
    //                                 ->groupBy('product_code')
    //                                 ->orderBy('count', 'DESC')
    //                                 ->limit(10)
    //                                 ->get()->toArray();
    //         if($orderProduct)
    //         {
    //             foreach($orderProduct as $val)
    //             {
    //                 $productCode[] = $val['product_code'];
    //             }
    //             $itemList = Item::whereIn('product_code', $productCode)->get()->toArray();

    //             $total = count($itemList);

    //             if(10 - $total > 0){
    //                 $itemList = array_merge($itemList, Item::whereNotIn('product_code', $productCode)->offset(0)->limit(10 - $total)->get()->toArray());
    //             }
    //         }
    //         else
    //         {
    //             $itemList = Item::offset(0)->limit(10)->get()->toArray();
    //         }
    //     }
    //     else
    //     {
    //         $itemList = Item::offset(0)->limit(10)->get()->toArray();
    //     }

    //     return response()->json([
    //         'succ'=> true,
    //         'msg' => '获取成功！',
    //         'data' => $itemList
    //     ]);
    // }

    public function detail(Request $req)
    {
        $input = $req->input();
        $validator = Validator::make($input, [
            'item_id' => 'required'
        ], [
            'item_id.required' => '参数错误！'
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

        $item = Item::find($input['item_id']);
        if(!$item)
        {
            return response()->json([
                'error'=> true,
                'msg' => '参数错误！'
            ]);
        }

        $unitTypeArr = [
            '4' => '件'
        ];

        $data = array(
            'name' => $item->product_name,
            'unit' => $unitTypeArr[$item->unit_type]
        );

        return response()->json([
            'succ'=> true,
            'msg' => '获取成功！',
            'data' => $data
        ]);
    }
}
