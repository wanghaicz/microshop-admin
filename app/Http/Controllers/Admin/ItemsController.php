<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\DesktopController;
use App\Item;
use Illuminate\Http\Request;
use Session;
use Shopex\LubanAdmin\Finder;
use Excel;
use Validator;

class ItemsController extends DesktopController
{
    public function index(Request $req)
    {
        $dataSet = Item::class;

        $finder = Finder::create($dataSet, '燃料列表')
                    ->setId('id')
                    ->addSort('从小到大', 'sort')
                    ->addSort('从大到小', 'sort', 'desc')
                    ->addAction('导入燃料数据', '/admin/item-import')->modal()
                    ->addAction('导出燃料数据', '/admin/item-export')
                    ->addAction('添加燃料', '/admin/item-add')
                    ->addColumn('燃料代码', 'product_code')
                    ->addColumn('操作', 'id')->modifier(function ($id) {
                        return '<a href="'.url("/admin/item-update-$id").'" title="编辑"><button class="btn btn-primary btn-xs">编辑</button></a>
                                <a href="'.url("/admin/item-delete-$id").'" title="删除" onclick="if(!confirm(\'确定要删除吗？\')){return false;};"><button class="btn btn-default btn-xs">删除</button></a>';
                    })->html(true)
                    ->addColumn('燃料名称', 'product_name')
                    ->addColumn('排序', 'sort')->size(1);
                    // ->addColumn('单位', 'unit_type')->modifier(function($unitType){
                    //     $unitTypeArr = [
                    //         '4' => '件'
                    //     ];

                    //     return $unitTypeArr[$unitType];
                    // })->size(1);
                    // ->addInfoPanel('描述', [$this, 'desc']);
                    // ->addBatchAction('删除', [$this, 'delete']);

        return $finder->view();
    }

    public function delete($id, Request $req)
    {
        Item::find($id)->delete();
        return redirect('/admin/item-list');
    }

    public function desc($itemId)
    {
        $item = Item::find($itemId);
        return view('admin/item/desc', ['desc'=>$item->desc]);
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
                $tpl = ['燃料代码', '燃料名称'];
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
                            '燃料代码' => 'required',
                            '燃料名称' => 'required'
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
                            'product_code' => $row['燃料代码'],
                            'product_name' => $row['燃料名称']
                        );

                        $dataSet = Item::where('product_code', '=', $aData['product_code']);
                        if($dataSet->get()->toArray())
                        {
                            $dataSet->update($aData);
                        }
                        else
                        {
                            Item::create($aData);
                        }
                    }
                }
                catch(\Exception $e)
                {
                    return response()->json([
                        'error'=> true,
                        'msg' => '燃料【'.$row['燃料名称'].'】保存失败！'
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

        return view('admin/item/import');
    }

    public function add(Request $req)
    {
        return view('admin/item/add');
    }

    public function save(Request $req)
    {
        $input = $req->input();

        $rule = [
            'product_code' => 'required',
            'product_name' => 'required'
        ];

        $msg = [
            'product_code.required' => '燃料代码必填！',
            'product_name.required' => '燃料名称必填！'
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
            $item = Item::where('product_code', '=', $input['product_code'])->get()->toArray();
            if($item)
            {
                if(isset($input['item_id']) && $input['item_id'])
                {
                    if($input['item_id'] != $item[0]['id']){
                        return response()->json([
                            'error'=> true,
                            'msg' => '该燃料代码已存在！'
                        ]);
                    }
                }
                else
                {
                    return response()->json([
                        'error'=> true,
                        'msg' => '该燃料代码已存在！'
                    ]);
                }
            }

            $data = [
                'product_code' => $input['product_code'],
                'product_name' => $input['product_name'],
                'sort' => $input['sort'] ? intval($input['sort']) : 0
            ];

            if(isset($input['item_id']) && $input['item_id'])
            {
                Item::find($input['item_id'])->update($data);
            }
            else
            {
                Item::create($data);
            }

            return response()->json([
                'succ'=> true,
                'msg' => '保存成功！',
                'redirect' => url('/admin/item-list')
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
        $item = Item::find($id);
        return view('admin/item/update', ['data'=>$item]);
    }

    public function doExport(Request $req)
    {
        $cellData[] = ['燃料代码', '燃料名称'];
        $data = Item::get()->toArray();
        foreach($data as $key=>$val)
        {
            $cellData[] = [
                $val['product_name'],
                $val['product_code']
            ];
        }

        return Excel::create('燃料列表', function($excel) use ($cellData){
            $excel->sheet('Sheet1', function($sheet) use ($cellData){
                $sheet->rows($cellData);
            });
        })->export('xls');
    }
}
