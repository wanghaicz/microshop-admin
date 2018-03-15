<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\DesktopController;
use App\Permission;
use App\Role;
use Illuminate\Http\Request;
use Session;
use App\User;
use App\UserPermission;

class AdminController extends DesktopController
{

    public function index(Request $req)
    {
        $user_id = $req->user()->id;

        if($user_id != 1){
            $allPermissions = User::allPermissions();

            foreach($allPermissions as $module=>$val){
                $data = UserPermission::where('user_id', '=', $user_id)->where('permission', 'like', $module.'.%')->get()->toArray();
                if($data){
                    return redirect('admin/'.$module.'-list');
                }
            }

            return response()->json([
                'error'=> true,
                'msg' => 'You are not authorized to access this resource.'
            ]);
        }
        else
        {
            return redirect('admin/order-list');
        }
    }

    /**
     * Display given permissions to role.
     *
     * @return void
     */
    public function getGiveRolePermissions()
    {
        $roles = Role::select('id', 'name', 'label')->get();
        $permissions = Permission::select('id', 'name', 'label')->get();

        return view('admin::permissions.role-give-permissions', compact('roles', 'permissions'));
    }

    /**
     * Store given permissions to role.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return void
     */
    public function postGiveRolePermissions(Request $request)
    {
        $this->validate($request, ['role' => 'required', 'permissions' => 'required']);

        $role = Role::with('permissions')->whereName($request->role)->first();
        $role->permissions()->detach();

        foreach ($request->permissions as $permission_name) {
            $permission = Permission::whereName($permission_name)->first();
            $role->givePermissionTo($permission);
        }

        Session::flash('flash_message', 'Permission granted!');

        return redirect('admin/roles');
    }
}
