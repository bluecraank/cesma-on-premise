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
        Schema::create('ednpoints', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('switch_id');
            $table->integer('vlan_id');
            $table->integer('port_id');
            $table->macAddress('mac_address');
            $table->ipAddress('ip_address');
            $table->string('hostname');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ednpoints');
    }
};
