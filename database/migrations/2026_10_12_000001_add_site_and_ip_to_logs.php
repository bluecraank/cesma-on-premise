<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // The table had no primary key, entries with the same timestamp had no stable order.
        // SQLite can not add a primary key to an existing table.
        if (Schema::getConnection()->getDriverName() != 'sqlite') {
            Schema::table('logs', function (Blueprint $table) {
                $table->id()->first();
            });
        }

        Schema::table('logs', function (Blueprint $table) {
            $table->integer('site_id')->nullable()->after('device_name');
            $table->string('ip', 45)->nullable()->after('user_id');

            $table->index('created_at');
            $table->index('site_id');
            $table->index('device_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['site_id']);
            $table->dropIndex(['device_id']);
            $table->dropColumn(['site_id', 'ip']);
        });

        if (Schema::hasColumn('logs', 'id')) {
            Schema::table('logs', function (Blueprint $table) {
                $table->dropColumn('id');
            });
        }
    }
};
