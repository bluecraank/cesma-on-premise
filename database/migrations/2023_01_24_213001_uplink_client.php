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
        Schema::create('uplink_clients', function (Blueprint $table) {
            $table->id();
            $table->string('hostname');
            $table->string('ip_address');
            $table->string('mac_address');
            $table->string('port_id');
            $table->string('vlan_id');
            $table->string('switch_id');
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
