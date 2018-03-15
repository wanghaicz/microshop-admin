<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Factory;
use App\MemberFactory;
use Illuminate\Support\Facades\Auth;

class FactoryController extends Controller
{
    public function getList(Request $req)
    {
    	$member = Auth::guard('api')->user();

    	$memberFactory = MemberFactory::where('member_id', '=', $member->id)->get()->toArray();
    	if($memberFactory)
    	{
    		$factoryId = array_column($memberFactory, 'factory_id');
    		$factoryList = Factory::whereIn('id', $factoryId)->get()->toArray();
    	}
    	else
    	{
    		$factoryList = [];
    	}

        return response()->json([
            'succ'=> true,
            'msg' => '获取成功！',
            'data' => $factoryList
        ]);
    }
}
