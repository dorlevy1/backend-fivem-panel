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
        Schema::connection('second_db')->create('redeem_codes_history', function (Blueprint $table) {
            $table->id();
            $table->string('request_by')->nullable();
            $table->string('discord_id')->nullable();
            $table->string('citizenid')->nullable();
            $table->text('vehicles')->nullable();
            $table->text('weapons')->nullable();
            $table->text('items')->nullable();
            $table->string('cash')->nullable();
            $table->boolean('used')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('second_db')->dropIfExists('redeem_codes_history');
    }
};
