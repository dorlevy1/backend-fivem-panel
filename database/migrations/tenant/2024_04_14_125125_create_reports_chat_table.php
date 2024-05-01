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
        Schema::create('reports_chat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id');
            $table->longText('messages')->nullable();
            $table->timestamps();
            $table->foreign('report_id')->references('id')->on('reports');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports_chat');
    }
};
