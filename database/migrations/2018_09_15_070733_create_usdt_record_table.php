<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsdtRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usdt_record', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key', '10');
            $table->string('coin', '10')->default('eos');
            $table->string('price', '10');
            $table->string('usdt_left', '10');
            $table->integer('timestamp');
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
        Schema::dropIfExists('usdt_record');
    }
}
