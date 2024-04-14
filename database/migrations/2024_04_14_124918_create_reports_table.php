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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('discord_id');
            $table->string('citizen_id');
            $table->string('title');
            $table->text('description');
            $table->string('claim_by');
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->foreign('claim_by')->references('discord_id')->on('admins');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
