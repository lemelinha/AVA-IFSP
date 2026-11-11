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
        Schema::create('process_discipline', function (Blueprint $table) {
            $table->uuid('process_id');
            $table->uuid('discipline_id');
            $table->uuid('professor_id');
            $table->timestamps();

            $table->foreign('process_id')->references('id')->on('processes')->cascadeOnDelete();
            $table->foreign('discipline_id')->references('id')->on('classes')->cascadeOnDelete();
            $table->foreign('professor_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process_discipline');
    }
};
