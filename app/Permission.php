<?php

namespace App;

trait Permission
{
    public static function allPermissions()
    {
        return [
            'order' => [
                'label' => '订单管理',
                'items' => [
                    'list' => [
                        'label' => '订单查看',
                        'items' => [
                            'Admin\\OrdersController@index',
                        ],
                    ],
                    'export' => [
                        'label' => '订单导出',
                        'items' => [
                            'Admin\\OrdersController@index',
                            // 'Admin\\OrdersController@export',
                            'Admin\\OrdersController@doExport',
                        ],
                    ],
                    'otms' => [
                        'label' => '导入OTMS',
                        'items' => [
                            'Admin\\OrdersController@index',
                            // 'Admin\\OrdersController@beforeOtms',
                            'Admin\\OrdersController@toOtms',
                        ],
                    ],
                    'cancel' => [
                        'label' => '订单取消',
                        'items' => [
                            'Admin\\OrdersController@index',
                            'Admin\\OrdersController@showCancelReason',
                            'Admin\\OrdersController@cancel',
                        ],
                    ],
                    'delete' => [
                        'label' => '订单删除',
                        'items' => [
                            'Admin\\OrdersController@index',
                            'Admin\\OrdersController@delete',
                        ],
                    ],
                ],
            ],
            'member' => [
                'label' => '会员管理',
                'items' => [
                    'list' => [
                        'label' => '会员查看',
                        'items' => [
                            'Admin\\MembersController@index',
                        ],
                    ],
                    'importexport' => [
                        'label' => '会员导入/导出',
                        'items' => [
                            'Admin\\MembersController@index',
                            'Admin\\MembersController@import',
                            'Admin\\MembersController@doExport',
                        ],
                    ],
                    'addedit' => [
                        'label' => '会员添加/修改',
                        'items' => [
                            'Admin\\MembersController@index',
                            'Admin\\MembersController@add',
                            'Admin\\MembersController@update',
                            'Admin\\MembersController@save',
                        ],
                    ],
                    'resetpwd' => [
                        'label' => '会员密码修改',
                        'items' => [
                            'Admin\\MembersController@index',
                            'Admin\\MembersController@savePwd',
                        ],
                    ],
                    'delete' => [
                        'label' => '会员删除',
                        'items' => [
                            'Admin\\MembersController@index',
                            'Admin\\MembersController@delete',
                        ],
                    ],
                    'logout' => [
                        'label' => '会员登录信息清除',
                        'items' => [
                            'Admin\\MembersController@index',
                            'Admin\\MembersController@logout',
                        ],
                    ],
                    'addr' => [
                        'label' => '会员地址添加/修改/删除',
                        'items' => [
                            'Admin\\MembersController@index',
                            'Admin\\MembersController@addAddr',
                            'Admin\\MembersController@editAddr',
                            'Admin\\MembersController@saveAddr',
                            'Admin\\MembersController@deleteAddr',
                        ],
                    ],
                ],
            ],
            'item' => [
                'label' => '燃料管理',
                'items' => [
                    'list' => [
                        'label' => '燃料查看',
                        'items' => [
                            'Admin\\ItemsController@index',
                        ],
                    ],
                    'importexport' => [
                        'label' => '燃料导入/导出',
                        'items' => [
                            'Admin\\ItemsController@index',
                            'Admin\\ItemsController@import',
                            'Admin\\ItemsController@doExport'
                        ],
                    ],
                    'addedit' => [
                        'label' => '燃料添加/修改',
                        'items' => [
                            'Admin\\ItemsController@index',
                            'Admin\\ItemsController@add',
                            'Admin\\ItemsController@save',
                            'Admin\\ItemsController@update',
                        ],
                    ],
                    'delete' => [
                        'label' => '燃料删除',
                        'items' => [
                            'Admin\\ItemsController@index',
                            'Admin\\ItemsController@delete',
                        ],
                    ],
                ],
            ],
            'factory' => [
                'label' => '电厂管理',
                'items' => [
                    'list' => [
                        'label' => '电厂查看',
                        'items' => [
                            'Admin\\FactoriesController@index',
                        ],
                    ],
                    'importexport' => [
                        'label' => '电厂导入/导出',
                        'items' => [
                            'Admin\\FactoriesController@index',
                            'Admin\\FactoriesController@import',
                            'Admin\\FactoriesController@doExport',
                        ],
                    ],
                    'addedit' => [
                        'label' => '电厂添加/修改',
                        'items' => [
                            'Admin\\FactoriesController@index',
                            'Admin\\FactoriesController@add',
                            'Admin\\FactoriesController@update',
                            'Admin\\FactoriesController@save',
                        ],
                    ],
                    'delete' => [
                        'label' => '电厂删除',
                        'items' => [
                            'Admin\\FactoriesController@index',
                            'Admin\\FactoriesController@delete',
                        ],
                    ],
                ],
            ],
            'user' => [
                'label' => '用户管理',
                'items' => [
                    'list' => [
                        'label' => '用户查看',
                        'items' => [
                            'Admin\\UsersController@index',
                        ],
                    ],
                    'add' => [
                        'label' => '用户添加',
                        'items' => [
                            'Admin\\UsersController@index',
                            'Admin\\UsersController@showAddUserForm',
                            'Admin\\UsersController@addUser',
                        ],
                    ],
                    'resetpwd' => [
                        'label' => '重置用户密码',
                        'items' => [
                            'Admin\\UsersController@index',
                            'Admin\\UsersController@showResetPwdForm',
                            'Admin\\UsersController@resetPwd',
                        ],
                    ],
                    'setpermission' => [
                        'label' => '设置用户权限',
                        'items' => [
                            'Admin\\UsersController@index',
                            'Admin\\UsersController@showSetPermissionForm',
                            'Admin\\UsersController@setPermission',
                        ],
                    ],
                    'delete' => [
                        'label' => '删除用户',
                        'items' => [
                            'Admin\\UsersController@index',
                            'Admin\\UsersController@delete',
                        ],
                    ],
                ],
            ],
        ];
    }
}