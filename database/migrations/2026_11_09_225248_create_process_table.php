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
        Schema::create('processes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('public_id')->unique();
            $table->uuid('user_id');
            $table->uuid('coordinator_id');
            $table->string('status')->default('active')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('concluded_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('coordinator_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('process_id');
            $table->uuid('discipline_id');
            $table->uuid('professor_id');
            $table->string('title');
            $table->text('description');
            $table->string('url_activity')->nullable();
            $table->string('activity_type', 10)->default('pdf');
            $table->text('activity_text')->nullable();
            $table->string('url_answer')->nullable();
            $table->string('answer_type', 10)->nullable();
            $table->text('answer_text')->nullable();
            $table->string('status')->default('pending')->index();
            $table->decimal('grade', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->date('deadline');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('corrected_at')->nullable();
            $table->timestamp('concluded_at')->nullable();
            $table->timestamps();

            $table->foreign('process_id')->references('id')->on('processes')->onDelete('cascade');
            $table->foreign('discipline_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('professor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
        Schema::dropIfExists('processes');
    }
};
