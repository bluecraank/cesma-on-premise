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
        Schema::create('device_port_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_port_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('port_speed')->nullable();
            $table->unsignedDouble('port_rx_bps')->nullable();
            $table->unsignedDouble('port_tx_bps')->nullable();
            $table->unsignedDouble('port_rx_pps')->nullable();
            $table->unsignedDouble('port_tx_pps')->nullable();
            $table->unsignedBigInteger('port_rx_bytes')->nullable();
            $table->unsignedBigInteger('port_tx_bytes')->nullable();
            $table->unsignedBigInteger('port_rx_packets')->nullable();
            $table->unsignedBigInteger('port_tx_packets')->nullable();
            $table->unsignedBigInteger('port_rx_errors')->nullable();
            $table->unsignedBigInteger('port_tx_errors')->nullable();
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
        Schema::dropIfExists('device_port_stats');
    }
};
