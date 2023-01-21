<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('port_stats', function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('device_id');
            $table->unsignedBigInteger('port_id');
            $table->unsignedBigInteger('port_speed');
            $table->unsignedDouble('port_rx_bps');
            $table->unsignedDouble('port_tx_bps');
            $table->unsignedDouble('port_rx_pps');
            $table->unsignedDouble('port_tx_pps');
            $table->unsignedBigInteger('port_rx_bytes');
            $table->unsignedBigInteger('port_tx_bytes');
            $table->unsignedBigInteger('port_rx_packets');
            $table->unsignedBigInteger('port_tx_packets');
            $table->unsignedBigInteger('port_rx_errors');
            $table->unsignedBigInteger('port_tx_errors');
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
        //
    }
};
