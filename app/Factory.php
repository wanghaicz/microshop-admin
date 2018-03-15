<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Factory extends Model
{

    protected $table = 'factories';

    protected $fillable = ['company_name', 'town', 'address', 'ship_to_id'];
}
