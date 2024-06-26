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
        Schema::create('pending_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('discord_id')->unique();
            $table->foreignId('permission_type');
            $table->timestamps();
            $table->foreign('permission_type')->references('id')->on('permission_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_permissions');
    }
};
