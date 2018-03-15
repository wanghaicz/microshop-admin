<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{

    protected $table = 'user_permission';
    
    protected $fillable = ['user_id', 'permission'];
}
