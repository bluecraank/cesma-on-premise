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
        Schema::create('unknown_clients', function (Blueprint $table) {
            $table->id();
            $table->string('mac_address');
            $table->foreignId('device_id')->constrained('devices');
            $table->string('port_id');
            $table->string('vlan_id');
            $table->string('hostname');
            $table->string('ip_address')->nullable();
            $table->string('type');
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
        Schema::dropIfExists('unknown_clients');
    }
};
