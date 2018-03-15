<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('erp_number', 50)->unique();
            $table->string('order_remarks', 250)->nullable();
            $table->integer('member_id');
            $table->string('ship_from_mobile', 11);
            $table->string('ship_from_name', 100);
            $table->string('ship_from_postcode', 6);
            $table->string('ship_from_town', 30);
            $table->string('ship_from_address', 255);
            $table->string('ship_to_id', 30);
            $table->integer('pickup_date')->nullable();
            $table->integer('delivery_date')->nullable();
            $table->string('product_code', 50);
            $table->string('product_name', 120);
            $table->tinyInteger('unit_type');
            $table->float('quantity');
            $table->float('weight');
            $table->float('volume');
            $table->tinyInteger('quality');
            $table->tinyInteger('status')->default(0);
            $table->integer('otms_updated_at')->nullable();
            $table->text('dockAppointment')->nullable();
            $table->text('orderEvents')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
