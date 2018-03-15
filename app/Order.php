<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    protected $table = 'orders';

    protected $fillable = [
    	'erp_number',
        'order_remarks',
        'member_id',
        'ship_from_mobile',
        'ship_from_name',
        'ship_from_postcode',
        'ship_from_town',
        'ship_from_address',
        'ship_to_id',
        'pickup_date',
        'delivery_date',
        'product_code',
        'product_name',
        'unit_type',
        'quantity',
        'weight',
        'volume',
        'quality',
        'status',
        'otms_updated_at',
        'dockAppointment',
        'orderEvents',
        'cancel_reason'
    ];
}
