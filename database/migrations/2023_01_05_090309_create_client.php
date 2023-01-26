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
        Schema::create('clients', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->string('hostname')->nullable();
            $table->integer('vlan_id');
            $table->string('port_id');
            $table->string('mac_address');
            $table->string('ip_address')->nullable();
            $table->integer('online')->default(0);
            $table->string('type')->default('client');
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
        Schema::dropIfExists('clients');
    }
};
