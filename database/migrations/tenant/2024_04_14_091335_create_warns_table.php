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
        Schema::create('warns', function (Blueprint $table) {
            $table->id();
            $table->string('discord')->unique();
            $table->string('license');
            $table->string('name');
            $table->string('reason');
            $table->string('warned_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warns');
    }
};
