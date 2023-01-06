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
        //
        Schema::dropIfExists('endpoints');

        Schema::create('endpoints', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('hostname')->nullable();
            $table->integer('switch_id');
            $table->integer('vlan_id');
            $table->string('port_id');
            $table->macAddress('mac_address');
            $table->ipAddress('ip_address')->nullable();
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
