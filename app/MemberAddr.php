<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MemberAddr extends Model
{

    protected $table = 'member_addr';
    
    protected $fillable = ['member_id', 'postcode', 'town', 'address'];
}
