<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{

    protected $table = 'members';

    protected $fillable = ['name', 'mobile', 'password', 'api_token'];

    public function addr()
    {
        return $this->hasMany('App\MemberAddr', 'member_id');
    }

    public function order()
    {
        return $this->hasMany('App\Order', 'member_id');
    }
}
