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
            $table->string('mac_address')->unique()->nullable();
            $table->string('username')->default('admin');
            $table->string('password');
            $table->string('model')->nullable();
            $table->string('serial')->nullable();
            $table->string('firmware')->nullable();
            $table->string('hardware')->nullable();
            $table->string('named')->nullable();
            $table->foreignId('location_id')->constrained();
            $table->foreignId('building_id')->constrained();
            $table->string('location_desc')->nullable();
            $table->integer('location_number')->nullable();
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
        Schema::dropIfExists('devices');
    }
};
