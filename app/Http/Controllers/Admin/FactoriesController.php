<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\DesktopController;
use App\Factory;
use Illuminate\Http\Request;
use Session;
use Shopex\LubanAdmin\Finder;
use Excel;
use Validator;
use App\MemberFactory;
use App\UserFactory;
use Illuminate\Support\Facades\Auth;

class FactoriesController extends DesktopController
{
    public function index(Request $req)
    {
        $dataSet = Factory::class;

        $user = Auth::guard()->user();
        if($user->id != 1)
        {
            $userFactory = UserFactory::where('user_id', '=', $user->id)->get()->toArray();
            if($userFactory)
            {
                $factoryId = array_column($userFactory, 'factory_id');
                $dataSet = Factory::whereIn('id', $factoryId);
            }
            else
            {
                $dataSet = Factory::whereIn('id', [-1]);
            }
        }

        $finder = Finder::create($dataSet, '电厂列表')
                    ->setId('id')
                    ->addSort('按创建时间倒序', 'created_at', 'desc')
                    ->addSort('按创建时间正序', 'created_at')
                    ->addAction('导入电厂数据', '/admin/factory-import')->modal()
                    ->addAction('导出电厂数据', '/admin/factory-export')
                    ->addAction('新增电厂', '/admin/factory-add')
                    ->addColumn('名称', 'company_name')
                    ->addColumn('操作', 'id')->modifier(function ($id) {
                        return '<a href="'.url("/admin/factory-update-$id").'" title="编辑"><button class="btn btn-primary btn-xs">编辑</button></a>
                                <a href="'.url("/admin/factory-delete-$id").'" title="删除" onclick="if(!confirm(\'确定要删除吗？\')){return false;};"><button class="btn btn-default btn-xs">删除</button></a>';
                    })->html(true)
                    ->addColumn('外部代码', 'ship_to_id')->size(1)
                    ->addColumn('省市', 'town')->size(1)
                    ->addColumn('地址', 'address')->size(6);
                    // ->addBatchAction('删除', [$this, 'delete']);

        return $finder->view();
    }

    public function delete($id, Request $req)
    {
        Factory::find($id)->delete();
        UserFactory::where('factory_id', '=', $id)->delete();
        MemberFactory::where('factory_id', '=', $id)->delete();
        return redirect('/admin/factory-list');
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
                $tpl = ['名称', '省市', '地址', '外部代码'];
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
                            '名称' => 'required',
                            '省市' => 'required',
                            '地址' => 'required',
                            '外部代码' => 'required'
                        ], [
                            'required' => '必填！'
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

                        $aData = array(
                            'company_name' => $row['名称'],
                            'town' => $row['省市'],
                            'address' => $row['地址'],
                            'ship_to_id' => $row['外部代码']
                        );

                        $dataSet = Factory::where('ship_to_id', '=', $aData['ship_to_id']);
                        if($dataSet->get()->toArray())
                        {
                            $dataSet->update($aData);
                        }
                        else
                        {
                            Factory::create($aData);
                        }
                    }
                }
                catch(\Exception $e)
                {
                    return response()->json([
                        'error'=> true,
                        'msg' => '电厂【'.$row['名称'].'】数据保存失败！'
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

        return view('admin/factory/import');
    }

    public function add(Request $req)
    {
        return view('admin/factory/add');
    }

    public function save(Request $req)
    {
        $input = $req->input();

        $rule = [
            'company_name' => 'required',
            'town' => 'required',
            'address' => 'required',
            'ship_to_id' => 'required'
        ];

        $msg = [
            'company_name.required' => '名称必填！',
            'town.required' => '省市必填！',
            'address.required' => '地址必填！',
            'ship_to_id.required' => '外部代码必填！'
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
            $factory = Factory::where('ship_to_id', '=', $input['ship_to_id'])->get()->toArray();
            if($factory)
            {
                if(isset($input['factory_id']) && $input['factory_id'])
                {
                    if($input['factory_id'] != $factory[0]['id']){
                        return response()->json([
                            'error'=> true,
                            'msg' => '该外部代码已存在！'
                        ]);
                    }
                }
                else
                {
                    return response()->json([
                        'error'=> true,
                        'msg' => '该外部代码已存在！'
                    ]);
                }
            }

            $data = [
                'company_name' => $input['company_name'],
                'town' => $input['town'],
                'address' => $input['address'],
                'ship_to_id' => $input['ship_to_id']
            ];

            if(isset($input['factory_id']) && $input['factory_id'])
            {
                Factory::find($input['factory_id'])->update($data);
            }
            else
            {
                Factory::create($data);
            }

            return response()->json([
                'succ'=> true,
                'msg' => '保存成功！',
                'redirect' => url('/admin/factory-list')
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
        $factory = Factory::find($id);
        return view('admin/factory/update', ['data'=>$factory]);
    }

    public function doExport(Request $req)
    {
        $cellData[] = ['名称', '省市', '地址', '外部代码'];
        $data = Factory::get()->toArray();
        foreach($data as $key=>$val)
        {
            $cellData[] = [
                $val['company_name'],
                $val['town'],
                $val['address'],
                $val['ship_to_id']
            ];
        }

        return Excel::create('电厂列表', function($excel) use ($cellData){
            $excel->sheet('Sheet1', function($sheet) use ($cellData){
                $sheet->rows($cellData);
            });
        })->export('xls');
    }
}
