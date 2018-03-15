<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('admin');
});

Auth::routes();

// Route::get('/home', 'HomeController@index')->name('home');

Admin::routes();
Route::get('/profile', function(){
	return 'profile';
})->middleware('auth')->name('admin-profile');;
Route::get('/admin-site-menus', function(){
	return [];
})->middleware('auth')->name('admin-site-menus');

Route::any('admin/member-list', 'Admin\\MembersController@index');
Route::post('admin/member-savepwd', 'Admin\\MembersController@savePwd');
Route::any('admin/member-import','Admin\\MembersController@import');
Route::get('admin/member-export','Admin\\MembersController@doExport');
Route::get('admin/member-add','Admin\\MembersController@add');
Route::post('admin/member-save','Admin\\MembersController@save');
Route::get('admin/member-update-{id}','Admin\\MembersController@update');
Route::get('admin/member-delete-{id}','Admin\\MembersController@delete');
Route::get('admin/member-logout-{id}','Admin\\MembersController@logout');
Route::get('admin/member-addradd-{member_id}','Admin\\MembersController@addAddr');
Route::post('admin/member-addrsave','Admin\\MembersController@saveAddr');
Route::get('admin/member-addredit-{id}','Admin\\MembersController@editAddr');
Route::get('admin/member-addrdelete-{id}','Admin\\MembersController@deleteAddr');

Route::any('admin/item-list', 'Admin\\ItemsController@index');
Route::any('admin/item-import','Admin\\ItemsController@import');
Route::get('admin/item-export','Admin\\ItemsController@doExport');
Route::get('admin/item-add','Admin\\ItemsController@add');
Route::post('admin/item-save','Admin\\ItemsController@save');
Route::get('admin/item-update-{id}','Admin\\ItemsController@update');
Route::get('admin/item-delete-{id}','Admin\\ItemsController@delete');

Route::any('admin/factory-list', 'Admin\\FactoriesController@index');
Route::any('admin/factory-import','Admin\\FactoriesController@import');
Route::get('admin/factory-export','Admin\\FactoriesController@doExport');
Route::get('admin/factory-add','Admin\\FactoriesController@add');
Route::post('admin/factory-save','Admin\\FactoriesController@save');
Route::get('admin/factory-update-{id}','Admin\\FactoriesController@update');
Route::get('admin/factory-delete-{id}','Admin\\FactoriesController@delete');

Route::get('admin/order-list', 'Admin\\OrdersController@index');
// Route::any('admin/order-export', 'Admin\\OrdersController@export');
Route::any('admin/order-export-{sDate}-{eDate}', 'Admin\\OrdersController@doExport');
// Route::get('admin/order-beforeotms-{erp_number}', 'Admin\\OrdersController@beforeOtms');
Route::post('admin/order-tootms', 'Admin\\OrdersController@toOtms');
Route::get('admin/order-delete-{id}', 'Admin\\OrdersController@delete');
Route::get('admin/order-cancel-{erp_number}', 'Admin\\OrdersController@showCancelReason');
Route::post('admin/order-cancel', 'Admin\\OrdersController@cancel');

Route::get('admin/user-list', 'Admin\\UsersController@index');
Route::get('admin/user-add', 'Admin\\UsersController@showAddUserForm');
Route::post('admin/user-add', 'Admin\\UsersController@addUser');
Route::get('admin/user-delete-{id}', 'Admin\\UsersController@delete');
Route::get('admin/user-resetpwd-{id}', 'Admin\\UsersController@showResetPwdForm');
Route::post('admin/user-resetpwd', 'Admin\\UsersController@resetPwd');
Route::get('admin/user-setpermission-{id}', 'Admin\\UsersController@showSetPermissionForm');
Route::post('admin/user-setpermission', 'Admin\\UsersController@setPermission');

Route::post('api/login', 'Api\\PassportController@login');
Route::post('api/getopenid', 'Api\\PassportController@getopenid');
Route::post('api/item-detail', 'Api\\ItemController@detail');
Route::post('api/item-list', 'Api\\ItemController@getList');
// Route::post('api/item-recommend', 'Api\\ItemController@getRecommend');
Route::group([
    'prefix'     => 'api',
    'middleware' => 'auth:api'
], function () {
    Route::post('autologin', 'Api\\PassportController@autologin');
    Route::post('logout', 'Api\\PassportController@logout');

    Route::post('addr-list', 'Api\\MemberController@getAddr');
    Route::post('addr-save', 'Api\\MemberController@saveAddr');
    Route::post('addr-del', 'Api\\MemberController@delAddr');
    Route::post('passport-resetpwd', 'Api\\MemberController@pwdReset');

    Route::post('factory-list', 'Api\\FactoryController@getList');

    Route::post('order-create', 'Api\\OrderController@create');
    Route::post('order-list', 'Api\\OrderController@getList');
    Route::post('order-detail', 'Api\\OrderController@getDetail');
    Route::post('order-cancel', 'Api\\OrderController@cancel');
});