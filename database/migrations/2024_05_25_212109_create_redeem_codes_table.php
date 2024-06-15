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
        Schema::connection('second_db')->create('redeem_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('redeem_request')->unique();
            $table->string('code');
            $table->foreign('redeem_request')->references('id')->on('redeem_code_requests');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('second_db')->dropIfExists('redeem_codes');
    }
};
