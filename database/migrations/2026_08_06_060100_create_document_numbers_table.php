<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_numbers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('prefix')->index();
            $table->string('period', 7)->index(); // YYYYMM
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['prefix', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_numbers');
    }
};
