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
        Schema::create('vlans', function (Blueprint $table) {
            $table->id();
            $table->integer('vid')->unique();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('location_id')->references('id')->on('locations');
            $table->string('ip_range')->nullable();
            $table->boolean('is_client_vlan')->default(true);
            $table->boolean('is_scanned')->default(false);
            $table->boolean('is_synced')->default(true);
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
        Schema::dropIfExists('vlans');
    }
};
