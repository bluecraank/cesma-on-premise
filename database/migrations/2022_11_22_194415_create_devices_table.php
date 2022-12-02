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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('hostname')->unique();
            $table->string('password');
            $table->json('vlan_data');
            $table->json('port_data');
            $table->json('port_statistic_data');
            $table->json('vlan_port_data');
            $table->json('system_data');
            $table->integer('building');
            $table->integer('location');
            $table->string('details');
            $table->integer('number');
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
        Schema::dropIfExists('devices');
    }
};
