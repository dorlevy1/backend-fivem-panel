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
        if (!Schema::connection('second_db')->hasTable('gang_creation_request')) {

            Schema::connection('second_db')->create('gang_creation_request', function (Blueprint $table) {
                $table->id();
                $table->string('discord_id')->nullable()->unique();
                $table->string('gang_name')->nullable();
                $table->string('boss')->nullable();
                $table->string('co_boss')->nullable();
                $table->string('members')->nullable();
                $table->boolean('ready_for_approve')->default(0);
                $table->string('channel_id')->nullable();
                $table->timestamps();

            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('second_db')->dropIfExists('gang_creation_request');
    }
};
