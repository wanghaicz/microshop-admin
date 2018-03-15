<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Member;
use App\MemberAddr;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    public function getAddr(Request $req)
    {
    	$member = Auth::guard('api')->user();
        $addr = Member::find($member->id)->addr->toArray();

        return response()->json([
            'succ'=> true,
            'msg' => '获取成功！',
            'data' => $addr
        ]);
    }

    public function saveAddr(Request $req)
    {
        $member = Auth::guard('api')->user();

        $input = $req->input();
        $validator = Validator::make($input, [
            'postcode' => 'required',
            'town' => 'required',
            'address' => 'required'
        ], [
            'postcode.required' => '邮政编码必填！',
            'town.required' => '省市必填！',
            'address.required' => '地址必填！'
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

        try
        {
            $data['postcode'] = $input['postcode'];
            $data['town'] = $input['town'];
            $data['address'] = $input['address'];
            $data['member_id'] = $member->id;
            MemberAddr::create($data);

            return response()->json([
                'succ'=> true,
                'msg' => '保存成功！'
            ]);
        }
        catch(\Exception $e)
        {
            return response()->json([
                'error'=> true,
                'msg' => '保存失败！'
            ]);
        }
    }

    public function delAddr(Request $req)
    {
        $member = Auth::guard('api')->user();

        $input = $req->input();
        $validator = Validator::make($input, [
            'addr_id' => 'required'
        ], [
            'addr_id.required' => '参数错误！'
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

        try
        {
            MemberAddr::find($input['addr_id'])->delete();

            return response()->json([
                'succ'=> true,
                'msg' => '删除成功！'
            ]);
        }
        catch(\Exception $e)
        {
            return response()->json([
                'error'=> true,
                'msg' => '删除失败！'
            ]);
        }
    }

    public function pwdReset(Request $req)
    {
        $member = Auth::guard('api')->user();

        $input = $req->input();
        $validator = Validator::make($input, [
            'password' => 'required',
            'new_password' => 'required|min:6|confirmed'
        ], [
            'password.required' => '旧密码必填！',
            'new_password.required' => '新密码必填且长度不能少于6位！',
            'new_password.min' => '新密码必填且长度不能少于6位！',
            'new_password.confirmed' => '新密码两次输入不一致！'
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

        if(!Hash::check($input['password'], $member->password))
        {
            return response()->json([
                'error'=> true,
                'msg' => '旧密码不正确！'
            ]);
        }

        try
        {
            Member::find($member->id)->update([
                'password'=>bcrypt($input['new_password']),
                'api_token'=>''
            ]);
            
            return response()->json([
                'succ'=> true,
                'msg' => '设置成功，请重新登录！'
            ]);
        }
        catch(\Exception $e)
        {
            return response()->json([
                'error'=> true,
                'msg' => '设置失败！'
            ]);
        }


    }
}
