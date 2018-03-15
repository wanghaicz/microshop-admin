<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Member;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PassportController extends Controller
{


    public function login(Request $req)
    {
    	$mobile = $req->input('mobile');
    	$password = $req->input('password');
        $openid = $req->input('openid');
    	$dataSet = Member::where('mobile', '=', $mobile);
    	$data = $dataSet->select('name', 'mobile', 'password', 'api_token')->first();
    	if(!$data || !Hash::check($password, $data->password))
    	{
    		return response()->json([
                'error'=> true,
                'msg' => '手机号或密码不正确！'
            ]);
    	}

        if($data->api_token){
            return response()->json([
                'error'=> true,
                'msg' => '该账号已被其他人使用！'
            ]);
        }

    	try
    	{
    		$data->api_token = $openid ? $openid : bcrypt(rand());
    		$dataSet->update(['api_token'=>$data->api_token]);
    		return response()->json([
                'succ'=> true,
                'msg' => '登录成功！',
                'data' => $data->toArray()
            ]);
    	}
    	catch(\Exception $e)
        {
            return response()->json([
                'error'=> true,
                'msg' => '登录失败！'
            ]);
        }
    }

    public function autologin(Request $req)
    {
        $member = Auth::guard('api')->user();

        $data['mobile'] = $member->mobile;
        $data['name'] = $member->name;
        $data['api_token'] = $member->api_token;

        return response()->json([
            'succ'=> true,
            'msg' => '获取成功！',
            'data' => $data
        ]);
    }

    public function getopenid(Request $req)
    {
        $appId = 'wxf13fe416ef08e4b6';
        $appSecret = 'b6ddf3195e690baa251d2682bcefaf5e';
        $code = $req->input('code');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/sns/jscode2session");
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS, "appid={$appId}&secret={$appSecret}&js_code={$code}&grant_type=authorization_code");
        $res = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($res, true);
        if(isset($data['openid']))
        {
            return response()->json([
                'succ'=> true,
                'msg' => '获取成功!',
                'openid' => $data['openid']
            ]);
        }
        else
        {
            return response()->json([
                'error'=> true,
                'msg' => '获取失败！'
            ]);
        }
    }

    public function logout(Request $req)
    {
        $member = Auth::guard('api')->user();
        $dataSet = Member::where('id', '=', $member->id);
        $dataSet->update(['api_token'=>'']);

        return response()->json([
            'succ'=> true,
            'msg' => '操作成功！'
        ]);
    }
}
