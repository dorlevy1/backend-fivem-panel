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
        Schema::connection('second_db')->create('gangs_data', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable()->unique();
            $table->boolean('available')->nullable();
            $table->string('color_hex')->nullable();
            $table->string('color_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('second_db')->dropIfExists('gangs_data');
    }
};
