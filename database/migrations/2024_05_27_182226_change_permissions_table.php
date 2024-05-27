<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign(['discord_id']);
        });

        Schema::rename('permissions', 'admin_permissions');

        Schema::table('admin_permissions', function (Blueprint $table) {
            $table->foreign('discord_id')->references('discord_id')->on('admins');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_permissions', function (Blueprint $table) {
            $table->dropForeign(['discord_id']);
        });

        Schema::rename('admin_permissions', 'permissions');

        Schema::table('permissions', function (Blueprint $table) {
            $table->foreign('discord_id')->references('discord_id')->on('admins');
        });
    }
};
