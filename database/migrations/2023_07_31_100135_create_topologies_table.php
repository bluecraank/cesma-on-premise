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
        Schema::create('topologies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('local_device');
            $table->unsignedBigInteger('remote_device');
            $table->string('local_port');
            $table->string('remote_port');
            $table->string('remote_mac');
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
        Schema::dropIfExists('topologies');
    }
};
