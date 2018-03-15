<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Admin;
class DesktopServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->share('app_name', '凯迪物流有限公司下单程序后台管理');
        
        view()->share('app_menus', [
                // ['label'=>'首页', 'link'=>'/admin', 'user-only'=>true],
        		['label'=> '订单管理', 'link'=>'/admin/order-list','user-only'=>true],
                ['label'=> '会员管理', 'link'=>'/admin/member-list', 'user-only'=>true],
                ['label'=> '燃料管理', 'link'=>'/admin/item-list', 'user-only'=>true],
                ['label'=> '电厂管理', 'link'=>'/admin/factory-list', 'user-only'=>true],
                ['label'=> '用户管理', 'link'=>'/admin/user-list', 'user-only'=>true],
        	]);

        // view()->share('searchbar', [
        //         ['label'=>'搜索邮箱', 'action'=>'/member-list?filters=[[1,"{{value}}","begin"]]', 'regexp'=>'^[a-z0-9\.\_\+\-]+@[a-z0-9\.\_\-]+$'],
        //         ['label'=>'搜索用户', 'action'=>'/member-list?filters=[[1,"{{value}}","begin"]]', 'regexp'=>'[a-z0-9\.\_\+\-]'],                
        //     ]); 
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

}
