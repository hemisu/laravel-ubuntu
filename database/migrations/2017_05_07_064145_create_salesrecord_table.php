<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesrecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('stock_id');//商品id
            $table->text('remarks');//订单备注
            $table->double('price', 15, 2);//售价
            $table->boolean('ispay');//是否支付
            $table->string('motor_serial_number');//电机号
            $table->string('frame_number');//车架号
            $table->string('bettery_type');//电池型号
            $table->integer('client_id')->nullable();//客户id
            $table->integer('staff_id')->nullable();//经手人
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
        Schema::dropIfExists('sale_record');
    }
}
