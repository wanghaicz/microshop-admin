<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserFactory extends Model
{

    protected $table = 'user_factory';
    
    protected $fillable = ['user_id', 'factory_id'];
}
