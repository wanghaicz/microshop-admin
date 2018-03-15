<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MemberFactory extends Model
{

    protected $table = 'member_factory';
    
    protected $fillable = ['member_id', 'factory_id'];
}
