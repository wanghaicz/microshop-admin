<?php

namespace App\Http\Middleware;

use Closure;
use App\User;
use App\UserPermission;

class CheckPermission
{
    public function handle($req, Closure $next)
    {
        $user = $req->user();
        if($user)
        {
            $user_id = $user->id;
            if($user_id != 1)
            {
                $allPermissions = User::allPermissions();
                $curPermissions = UserPermission::where('user_id', '=', $user_id)->get()->toArray();
                
                $actions = ['Admin\\AdminController@index'];
                foreach($curPermissions as $val)
                {
                    list($module, $action) = explode('.', $val['permission']);

                    $actions = array_merge($actions, $allPermissions[$module]['items'][$action]['items']);
                }

                $action = $req->route()->getAction();
                $action = substr($action['controller'], strlen($action['namespace']) + 1);
                list($app) = explode('\\', $action);
                if($app == 'Admin' && !in_array($action, $actions))
                {
                    return response()->json([
                        'error'=> true,
                        'msg' => 'You are not authorized to access this resource.'
                    ]);
                }
            }
        }

        return $next($req);
    }
}
