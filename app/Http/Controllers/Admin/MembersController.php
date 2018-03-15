<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\DesktopController;
use App\Member;
use App\MemberAddr;
use Illuminate\Http\Request;
use Session;
use Shopex\LubanAdmin\Finder;
use Excel;
use Validator;
use App\Factory;
use App\MemberFactory;
use App\UserFactory;
use Illuminate\Support\Facades\Auth;

class MembersController extends DesktopController
{
    public function index(Request $req)
    {
        $user = Auth::guard()->user();
        if($user->id != 1)
        {
            $userFactory = UserFactory::where('user_id', '=', $user->id)->get()->toArray();
            if($userFactory)
            {
                $factoryId = array_column($userFactory, 'factory_id');
                $memberFactory = MemberFactory::whereIn('factory_id', $factoryId)->get()->toArray();
                if($memberFactory)
                {
                    $memberId = array_column($memberFactory, 'member_id');
                    $dataSet = Member::whereIn('id', $memberId);
                }
                else
                {
                    $dataSet = Member::whereIn('id', [-1]);
                }
            }
            else
            {
                $dataSet = Member::whereIn('id', [-1]);
            }
        }

        $input = $req->input();
        if(isset($input['mobile']) && $input['mobile'])
        {
            if(isset($dataSet))
            {
                $dataSet = $dataSet->where('mobile', '=', $input['mobile']);
            }
            else
            {
                $dataSet = Member::where('mobile', '=', $input['mobile']);
            }
        }

        if(isset($input['name']) && $input['name'])
        {
            if(isset($dataSet))
            {
                $dataSet = $dataSet->where('name', '=', $input['name']);
            }
            else
            {
                $dataSet = Member::where('name', '=', $input['name']);
            }
        }        

        $dataSet = isset($dataSet) ? $dataSet : Member::class;

        $finder = Finder::create($dataSet, '会员列表')
                    ->setId('id')
                    ->addSort('按创建时间倒序', 'created_at', 'desc')
                    ->addSort('按创建时间正序', 'created_at')
                    ->addAction('导入会员数据', '/admin/member-import')->modal()
                    ->addAction('导出会员数据', '/admin/member-export')
                    ->addAction('添加会员', '/admin/member-add')
                    ->addColumn('联系人姓名', 'name')->size(1)
                    ->addColumn('操作', 'id')->modifier(function ($id) {
                        $html = '<a href="'.url("/admin/member-update-$id").'" title="编辑"><button class="btn btn-primary btn-xs">编辑</button></a>
                                <a href="'.url("/admin/member-delete-$id").'" title="删除" onclick="if(!confirm(\'确定要删除吗？\')){return false;};"><button class="btn btn-default btn-xs">删除</button></a>';

                        $member = Member::find($id);
                        if($member->api_token)
                        {
                            $html .= ' <a href="'.url("/admin/member-logout-$id").'" title="清除登录信息" onclick="if(!confirm(\'确定要清除登录信息吗？\')){return false;};"><button class="btn btn-default btn-xs">清除登录信息</button></a>';
                        }

                        return $html;
                    })->html(true)
                    ->addColumn('手机号', 'mobile')
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
                    })
                    ->addInfoPanel('地址', [$this, 'addr'])
                    ->addInfoPanel('修改密码', [$this, 'resetpwd']);
                    // ->addBatchAction('删除', [$this, 'delete']);

        $search = '<form class="finder-search-bar"><div class="form-inline"><div class="form-group">手机号</div> <div class="form-group"><input type="text" class="form-control" name="mobile" /></div> <div class="form-group">姓名</div> <div class="form-group"><input type="text" class="form-control" name="name" /></div> <div class="form-group"><button type="submit" class="btn btn-primary">搜索</button></div></div></form>';
        return $finder->view('admin::finder', ['search'=>$search]);
    }

    public function logout($id, Request $req)
    {
        Member::find($id)->update(['api_token'=>'']);
        return redirect('/admin/member-list');
    }

    public function delete($id, Request $req)
    {
        Member::find($id)->delete();
        MemberAddr::where('member_id', '=', $id)->delete();
        MemberFactory::where('member_id', '=', $id)->delete();
        return redirect('/admin/member-list');
    }

    public function addr($memberId)
    {
        $addr = Member::find($memberId)->addr;
        return view('admin/member/addr', ['list'=>$addr, 'member_id'=>$memberId]);
    }

    public function resetpwd($memberId)
    {
        return view('admin/member/resetpwd', ['member_id'=>$memberId]);
    }

    public function savePwd(Request $req)
    {
        $data = $req->input();

        if(!$data['member_id'])
        {
            return response()->json([
                'error'=> true,
                'msg' => '参数有误！'
            ]);
        }

        if(!$data['password'] || strlen($data['password']) < 6)
        {
            return response()->json([
                'error'=> true,
                'msg' => '密码必填且长度不能少于6位！'
            ]);
        }

        $isSucc = Member::find($data['member_id'])->update([
            'password' => bcrypt($data['password']),
        ]);
        if(!$isSucc)
        {
            return response()->json([
                'error'=> true,
                'msg' => '保存失败！'
            ]);
        }

        return response()->json([
                'succ'=> true,
                'msg' => '修改成功！'
            ]);
    }

    public function import(Request $req)
    {
        if($req->hasFile('excel'))
        {
            //上传文件
            try
            {
                $file = $req->file('excel');
                $originalName = $file->getClientOriginalName();
                $newName = md5($originalName.time()).'.'.$file->getClientOriginalExtension();
                $destinationPath = storage_path().'/import/';
                $filePath = $file->move($destinationPath, $newName);
            }
            catch(\Exception $e)
            {
                return response()->json([
                    'error'=> true,
                    'msg' => '文件上传失败！'
                ]);
            }

            //读取文件内容
            try
            {
                $reader = Excel::load($filePath, function($reader) {})->get();
                $heading = $reader->getHeading();
                $tpl = ['联系人姓名', '手机号', '邮政编码', '省市', '地址', '项目电厂'];
                if(array_diff($tpl, $heading) || array_diff($heading, $tpl))
                {
                    return response()->json([
                        'error'=> true,
                        'msg' => '模版错误！'
                    ]);
                }

                $data = $reader->toArray();
            }
            catch(\Exception $e)
            {
                return response()->json([
                    'error'=> true,
                    'msg' => '文件内容读取失败！'
                ]);
            }

            //保存数据
            if($data)
            {
                try
                {
                
                    foreach($data as $row)
                    {
                        $validator = Validator::make($row, [
                            '联系人姓名' => 'required',
                            '手机号' => 'required|regex:/^1[34578][0-9]{9}$/',
                            '邮政编码' => 'required',
                            '省市' => 'required',
                            '地址' => 'required',
                            '项目电厂' => 'required'
                        ], [
                            'required' => '必填！',
                            'regex' => '格式有误！'
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

                        $member = array(
                            'name' => $row['联系人姓名'],
                            'mobile' => $row['手机号'],
                            'password' => bcrypt($row['手机号'])
                        );

                        $dataSet = Member::where('mobile', '=', $member['mobile']);
                        if($dataSet->get()->toArray())
                        {
                            $dataSet->update($member);
                        }
                        else
                        {
                            Member::create($member);
                        }

                        $member = Member::where('mobile', '=', $member['mobile'])->get()->toArray();
                        if($member)
                        {
                            $curFactory = MemberFactory::where('member_id', '=', $member[0]['id'])->get()->toArray();
                            $curFactory = array_column($curFactory, 'factory_id');

                            $factory = Factory::where('company_name', '=', $row['项目电厂'])->get()->toArray();
                            if($factory && !in_array($factory[0]['id'], $curFactory))
                            {
                                MemberFactory::insert([
                                    'member_id' => $member[0]['id'],
                                    'factory_id' => $factory[0]['id']
                                ]);
                            }

                            $addr = MemberAddr::where('member_id', '=', $member[0]['id'])->where('postcode', '=', $row['邮政编码'])->where('town', '=', $row['省市'])->where('address', '=', $row['地址'])->get()->toArray();
                            if(!$addr)
                            {
                                MemberAddr::insert([
                                    'postcode' => $row['邮政编码'],
                                    'town' => $row['省市'],
                                    'address' => $row['地址'],
                                    'member_id' => $member[0]['id']
                                ]);
                            }
                        }
                    }
                }
                catch(\Exception $e)
                {
                    return response()->json([
                        'error'=> true,
                        'msg' => '手机号【'.$row['手机号'].'】用户数据保存失败！'.$e->getMessage()
                    ]);
                }
            }
            else
            {
                return response()->json([
                    'error'=> true,
                    'msg' => '数据不能为空！'
                ]);
            }

            return response()->json([
                'succ'=> true,
                'msg' => '导入成功！'
            ]);
        }

        return view('admin/member/import');
    }

    public function add(Request $req)
    {
        $factories = Factory::get()->toArray();
        return view('admin/member/add', ['factories'=>$factories]);
    }

    public function save(Request $req)
    {
        $input = $req->input();

        $rule = [
            'name' => 'required',
            'mobile' => 'required|regex:/^1[34578][0-9]{9}$/',
            'factory_id' => 'required',
        ];

        $msg = [
            'name.required' => '联系人姓名必填！',
            'mobile.required' => '手机号必填！',
            'mobile.regex' => '手机号格式有误！',
            'factory_id.required' => '项目电厂必选！',
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
            $member = Member::where('mobile', '=', $input['mobile'])->get()->toArray();
            if($member)
            {
                if(isset($input['member_id']) && $input['member_id'])
                {
                    if($input['member_id'] != $member[0]['id']){
                        return response()->json([
                            'error'=> true,
                            'msg' => '该手机号已存在！'
                        ]);
                    }
                }
                else
                {
                    return response()->json([
                        'error'=> true,
                        'msg' => '该手机号已存在！'
                    ]);
                }
            }

            $data = [
                'name' => $input['name'],
                'mobile' => $input['mobile']
            ];

            if(isset($input['member_id']) && $input['member_id'])
            {
                $member_id = $input['member_id'];
                Member::find($member_id)->update($data);
                MemberFactory::where('member_id', '=', $member_id)->delete();

            }
            else
            {
                $data['password'] = bcrypt($input['mobile']);
                $mData = Member::create($data);
                $mData = json_decode($mData, true);
                $member_id = $mData['id'];
            }

            foreach($input['factory_id'] as $val)
            {
                MemberFactory::insert([
                    'member_id' => $member_id,
                    'factory_id' => $val
                ]);
            }

            return response()->json([
                'succ'=> true,
                'msg' => '保存成功！',
                'redirect' => url('/admin/member-list')
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

    public function update($id, Request $req)
    {
        $member = Member::find($id);

        $factories = Factory::get()->toArray();
        $curFactory = MemberFactory::where('member_id', '=', $id)->get()->toArray();
        $curFactory = array_column($curFactory, 'factory_id');

        return view('admin/member/update', ['data'=>$member, 'factories'=>$factories, 'curFactory'=>$curFactory]);
    }

    public function addAddr($memberId, Request $req)
    {
        return view('admin/member/addr/add', ['member_id'=>$memberId]);
    }

    public function editAddr($addrId, Request $req)
    {
        $addr = MemberAddr::find($addrId);
        return view('admin/member/addr/edit', ['data'=>$addr]);
    }

    public function saveAddr(Request $req)
    {
        $input = $req->input();

        $rule = [
            'member_id' => 'required',
            'postcode' => 'required',
            'town' => 'required',
            'address' => 'required'
        ];

        $msg = [
            'member_id.required' => '参数有误！',
            'postcode.required' => '邮政编码必填！',
            'town.required' => '省市必填！',
            'address.required' => '地址必填！'
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
            $data = [
                'member_id' => $input['member_id'],
                'postcode' => $input['postcode'],
                'town' => $input['town'],
                'address' => $input['address']
            ];

            if(isset($input['addr_id']) && $input['addr_id'])
            {
                MemberAddr::find($input['addr_id'])->update($data);
            }
            else
            {
                MemberAddr::create($data);
            }

            return response()->json([
                'succ'=> true,
                'msg' => '保存成功！',
                'redirect' => url('/admin/member-list')
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

    public function doExport(Request $req)
    {
        $cellData[] = ['联系人姓名', '手机号', '邮政编码', '省市', '地址', '项目电厂'];

        $user = Auth::guard()->user();
        if($user->id != 1)
        {
            $userFactory = UserFactory::where('user_id', '=', $user->id)->get()->toArray();
            if($userFactory)
            {
                $factoryId = array_column($userFactory, 'factory_id');
                $memberFactory = MemberFactory::whereIn('factory_id', $factoryId)->get()->toArray();
                if($memberFactory)
                {
                    $memberId = array_column($memberFactory, 'member_id');
                    $data = Member::whereIn('id', $memberId)->get()->toArray();
                }
                else
                {
                    $data = Member::whereIn('id', [-1])->get()->toArray();
                }
            }
            else
            {
                $data = Member::whereIn('id', [-1])->get()->toArray();
            }
        }
        else
        {
            $data = Member::get()->toArray();
        }

        foreach($data as $value)
        {
            $factory = MemberFactory::where('member_id', '=', $value['id'])->get()->toArray();
            $addr = MemberAddr::where('member_id', '=', $value['id'])->get()->toArray();

            if($factory)
            {
                foreach ($factory as $val)
                {
                    if($user->id != 1 && !in_array($val['factory_id'], $factoryId))
                    {
                        continue;
                    }

                    $fData = Factory::find($val['factory_id']);

                    if($addr)
                    {
                        foreach ($addr as $v) {
                            $cellData[] = [
                                $value['name'],
                                $value['mobile'],
                                $v['postcode'],
                                $v['town'],
                                $v['address'],
                                $fData ? $fData->company_name : $val['factory_id']
                            ];
                        }
                    }
                    else
                    {
                        $cellData[] = [
                            $value['name'],
                            $value['mobile'],
                            '',
                            '',
                            '',
                            $fData ? $fData->company_name : $val['factory_id']
                        ];
                    }
                }
            }
            else
            {
                if($addr)
                {
                    foreach ($addr as $v) {
                        $cellData[] = [
                            $value['name'],
                            $value['mobile'],
                            $v['postcode'],
                            $v['town'],
                            $v['address'],
                            ''
                        ];
                    }
                }
                else
                {
                    $cellData[] = [
                        $value['name'],
                        $value['mobile'],
                        '',
                        '',
                        '',
                        ''
                    ];
                }
            }
        }

        return Excel::create('会员列表', function($excel) use ($cellData){
            $excel->sheet('Sheet1', function($sheet) use ($cellData){
                $sheet->rows($cellData);
            });
        })->export('xls');
    }

    public function deleteAddr($id, Request $req)
    {
        MemberAddr::find($id)->delete();
        return redirect('/admin/member-list');
    }
}
