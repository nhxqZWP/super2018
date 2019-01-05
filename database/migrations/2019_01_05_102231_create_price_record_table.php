<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePriceRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_record', function (Blueprint $table) {
            $table->increments('id');
            $table->double('XBTUSD');
            $table->double('XBTH19');
            $table->double('XBTM19');
            $table->double('XBTUSD_XBTH19');
            $table->double('XBTH19_XBTM19');
            $table->double('XBTUSD_XBTM19');
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
        Schema::dropIfExists('price_record');
    }
}
