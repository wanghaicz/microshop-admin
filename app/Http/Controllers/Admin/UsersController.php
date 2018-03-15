<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\DesktopController;
use App\User;
use Illuminate\Http\Request;
use Session;
use Shopex\LubanAdmin\Finder;
use Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use App\UserPermission;
use App\Factory;
use App\UserFactory;

class UsersController extends DesktopController
{
    public function index(Request $req)
    {
        $user = Auth::guard()->user();
        if($user->id != 1)
        {
            $dataSet = User::where('pid', '=', $user->id)->orWhere('id', '=', $user->id);
        }
        else
        {
            $dataSet = User::class;
        }

        $finder = Finder::create($dataSet, '用户列表')
                    ->setId('id')
                    ->addAction('添加用户', '/admin/user-add')
                    ->addColumn('用户名', 'name')->size(1)
                    ->addColumn('操作', 'id')->modifier(function ($id) use ($user) {
                        $html = '';

                        if($user->id == 1 || $user->id == $id)
                        {
                            $html = '<a href="'.url("/admin/user-resetpwd-$id").'" title="重置密码"><button class="btn btn-primary btn-xs">重置密码</button></a>';
                        }

                        if($id != 1 && $user->id != $id)
                        {
                            $html .= ' <a href="'.url("/admin/user-setpermission-$id").'" title="权限管理"><button class="btn btn-primary btn-xs">设置权限</button></a>';

                            $html .= ' <a href="'.url("/admin/user-delete-$id").'" title="删除" onclick="if(!confirm(\'确定要删除吗？\')){return false;};"><button class="btn btn-default btn-xs">删除</button></a>';
                        }

                        return $html;
                    })->html(true)
                    ->addColumn('邮箱', 'email')
                    ->addColumn('创建时间', 'created_at')->modifier(function($createdAt){
                        $s = time() - strtotime($createdAt);
                        if ($s < 60 )
                        {
                            return  $s.'秒钟前';
                        }
                        elseif($s >= 60 && $s < 3600)
                        {
                            return floor($s / 60).'分钟前';
                        }
                        elseif($s >= 3600 && $s < 86400)
                        {
                            return floor($s / 3600).'小时前';
                        }
                        else
                        {
                            return date('Y-m-d H:i:s', strtotime($createdAt));
                        }
                    });

        return $finder->view();
    }

    public function showSetPermissionForm($id, Request $req)
    {
        $user = Auth::guard()->user();
        $data = User::where('id', '=', $id)->get()->toArray();

        if($user->id != 1 && $data[0]['pid'] != $user->id)
        {
            return response()->json([
                'error'=> true,
                'msg' => 'You are not authorized to access this resource.'
            ]);
        }

        $permissions = User::allPermissions();
        $factories = Factory::get()->toArray();
        if($user->id != 1)
        {
            $tmpPermissions = [];
            $userPermission = UserPermission::where('user_id', '=', $user->id)->get()->toArray();
            foreach($userPermission as $val)
            {
                list($module, $action) = explode('.', $val['permission']);
                if(!isset($tmpPermissions[$module]))
                {
                    $tmpPermissions[$module]['label'] = $permissions[$module]['label'];
                }
                $tmpPermissions[$module]['items'][$action] = $permissions[$module]['items'][$action];
            }
            $permissions = $tmpPermissions;

            $tmpFactories = [];
            $userFactory = UserFactory::where('user_id', '=', $user->id)->get()->toArray();
            $userFactory = array_column($userFactory, 'factory_id');
            foreach($factories as $val)
            {
                if(in_array($val['id'], $userFactory))
                {
                    $tmpFactories[] = $val;
                }
            }
            $factories = $tmpFactories;
        }

        $curPermission = UserPermission::where('user_id', '=', $id)->get()->toArray();
        if($curPermission)
        {
            $curPermission = array_column($curPermission, 'permission');
        }

        $curFactory = UserFactory::where('user_id', '=', $id)->get()->toArray();
        if($curFactory)
        {
            $curFactory = array_column($curFactory, 'factory_id');
        }

        return view('admin/user/setpermission', ['user_id'=>$id, 'permissions'=>$permissions, 'curPermission'=>$curPermission, 'factories'=>$factories, 'curFactory'=>$curFactory]);
    }

    public function setPermission(Request $req)
    {
        $data = $req->all();
        $validator = Validator::make($data, [
            'user_id' => 'required',
        ], [
            'user_id.required' => '参数有误！',
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

        if($data['user_id'] == 1)
        {
            return response()->json([
                'error'=> true,
                'msg' => '参数有误！'
            ]);
        }

        $user = Auth::guard()->user();
        $userData = User::where('id', '=', $data['user_id'])->get()->toArray();

        if($user->id != 1 && $userData[0]['pid'] != $user->id)
        {
            return response()->json([
                'error'=> true,
                'msg' => 'You are not authorized to access this resource.'
            ]);
        }

        $all = false;
        $permissions = [];
        $factories = [];
        if($user->id == 1)
        {
            if($userData[0]['pid'] == 1)
            {
                $all = true;
            }
            else
            {
                $userPermission = UserPermission::where('user_id', '=', $userData[0]['pid'])->get()->toArray();
                $permissions = array_column($userPermission, 'permission');

                $userFactory = UserFactory::where('user_id', '=', $userData[0]['pid'])->get()->toArray();
                $factories = array_column($userFactory, 'factory_id');
            }
        }
        else
        {
            $userPermission = UserPermission::where('user_id', '=', $user->id)->get()->toArray();
            $permissions = array_column($userPermission, 'permission');

            $userFactory = UserFactory::where('user_id', '=', $user->id)->get()->toArray();
            $factories = array_column($userFactory, 'factory_id');
        }

        UserPermission::where('user_id', '=', $data['user_id'])->delete();
        if(isset($data['permission'])){
            $allPermissions = User::allPermissions();

            foreach($data['permission'] as $val){
                if(!$all && !in_array($val, $permissions)){
                    list($module, $action) = explode('.', $val);
                    if($user->id == 1){
                        $msg = '该用户的上级没有'.$allPermissions[$module]['label'].'=>'.$allPermissions[$module]['items'][$action]['label'].'的权限，不能给该用户设置该权限！';
                    }else{
                        $msg = '您没有'.$allPermissions[$module]['label'].'=>'.$allPermissions[$module]['items'][$action]['label'].'的权限，不能给该用户设置该权限！';
                    }

                    return response()->json([
                        'error'=> true,
                        'msg' => $msg
                    ]);
                }
            }

            foreach($data['permission'] as $val){
                UserPermission::insert([
                    'user_id' => $data['user_id'],
                    'permission' => $val
                ]);
            }
        }

        UserFactory::where('user_id', '=', $data['user_id'])->delete();
        if(isset($data['factory'])){
            foreach($data['factory'] as $val){
                if(!$all && !in_array($val, $factories)){
                    $factory = Factory::find($val);
                    
                    if($user->id == 1){
                        $msg = '该用户的上级没有'.$factory->company_name.'项目的权限，不能给该用户设置该权限！';
                    }else{
                        $msg = '您没有'.$factory->company_name.'项目的权限，不能给该用户设置该权限！';
                    }                    

                    return response()->json([
                        'error'=> true,
                        'msg' => $msg
                    ]);
                }
            }

            foreach($data['factory'] as $val){
                UserFactory::insert([
                    'user_id' => $data['user_id'],
                    'factory_id' => $val
                ]);
            }
        }

        return response()->json([
            'succ'=> true,
            'msg' => '保存成功！',
            'redirect' => url('/admin/user-list')
        ]);
    }

    public function showResetPwdForm($id, Request $req)
    {
        return view('admin/user/resetpwd', ['user_id'=>$id]);
    }

    public function resetPwd(Request $req)
    {
        $data = $req->all();
        $validator = Validator::make($data, [
            'user_id' => 'required',
            'password' => 'required|min:6|confirmed',
        ], [
            'user_id.required' => '参数有误！',
            'password.required' => '密码必填！',
            'password.min' => '密码不能少于6个字符！',
            'password.confirmed' => '两次密码输入不一样！'
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

        $user = Auth::guard()->user();
        if($user->id != 1 && $data['user_id'] != $user->id)
        {
            return response()->json([
                'error'=> true,
                'msg' => '参数有误！'
            ]);
        }

        User::find($data['user_id'])->update([
            'password'=>bcrypt($data['password'])
        ]);

        return response()->json([
            'succ'=> true,
            'msg' => '保存成功！',
            'redirect' => url('/admin/user-list')
        ]);
    }

    public function delete($id, Request $req)
    {
        $user = Auth::guard()->user();
        $userData = User::where('id', '=', $id)->get()->toArray();

        if($user->id != 1 && $userData[0]['pid'] != $user->id)
        {
            return response()->json([
                'error'=> true,
                'msg' => 'You are not authorized to access this resource.'
            ]);
        }

        User::find($id)->delete();
        User::where('pid', '=', $id)->delete();
        return redirect('/admin/user-list');
    }

    public function showAddUserForm(Request $req)
    {
        $user = Auth::guard()->user();
        if($user->id == 1)
        {
            $users = User::where('pid', '=', 1)->get()->toArray();
        }
        else
        {
            $users = User::where('id', '=', $user->id)->get()->toArray();
            $pid = $users[0]['pid'];
            while($pid != 1)
            {
                $users = User::where('id', '=', $pid)->get()->toArray();
                $pid = $users[0]['pid'];
            }
        }

        return view('admin/user/add', ['users'=>$users, 'user_id'=>$user->id]);
    }

    public function addUser(Request $req)
    {
        $data = $req->all();
        $validator = Validator::make($data, [
            'pid' => 'required',
            'name' => 'required|max:30',
            'email' => 'required|email|max:100|unique:users',
            'password' => 'required|min:6|confirmed',
        ], [
            'pid.required' => '请选择上级',
            'name.required' => '用户名必填！',
            'name.max' => '用户名长度超出限制！',
            'email.required' => '邮箱必填！',
            'email.email' => '邮箱格式有误！',
            'email.max' => '邮箱长度超出限制！',
            'email.unique' => '邮箱已存在！',
            'password.required' => '密码必填！',
            'password.min' => '密码不能少于6个字符！',
            'password.confirmed' => '两次密码输入不一样！'
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

        $user = Auth::guard()->user();
        if($user->id == 1)
        {
            if($data['pid'] != 1)
            {
                $user = User::where('id', '=', $data['pid'])->get()->toArray();
                if(!$user || $user[0]['pid'] != 1)
                {
                    return response()->json([
                        'error'=> true,
                        'msg' => '请重新选择上级！'
                    ]);
                }
            }
        }
        else
        {
            $user = User::where('id', '=', $user->id)->get()->toArray();
            $pid = $user[0]['pid'];
            while($pid != 1)
            {
                $user = User::where('id', '=', $pid)->get()->toArray();
                $pid = $user[0]['pid'];
            }

            if($user[0]['id'] != $data['pid'])
            {
                return response()->json([
                    'error'=> true,
                    'msg' => '请重新选择上级！'
                ]);
            }
        }

        event(new Registered($user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'pid' => $data['pid']
        ])));

        return response()->json([
            'succ'=> true,
            'msg' => '保存成功！',
            'redirect' => url('/admin/user-list')
        ]);
    }
}
