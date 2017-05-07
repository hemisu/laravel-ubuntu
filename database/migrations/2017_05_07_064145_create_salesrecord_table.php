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
            $table->integer('user_id')->nullable();//客户id
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
