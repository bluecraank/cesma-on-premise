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
            $table->integer('vid');
            $table->string('name');
            $table->string('description')->nullable();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->string('ip_range')->nullable();
            $table->boolean('is_client_vlan')->default(true);
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
