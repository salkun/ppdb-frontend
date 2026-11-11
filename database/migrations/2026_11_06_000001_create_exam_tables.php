<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->unique();
            $table->string('title', 150);
            $table->text('instructions')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->boolean('published')->default(false);
            $table->boolean('shuffle_questions')->default(true);
            $table->boolean('shuffle_options')->default(true);
            $table->boolean('results_released')->default(false);
            $table->timestamps();
        });

        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->longText('question');
            $table->json('options');
            $table->char('correct_option', 1);
            $table->timestamps();
            $table->unique(['exam_id', 'position']);
        });

        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->uuid('account_id');
            $table->foreign('account_id')->references('id')->on('ppdb_accounts')->cascadeOnDelete();
            $table->string('status', 20)->default('in_progress')->index();
            $table->timestamp('started_at');
            $table->timestamp('ends_at');
            $table->timestamp('completed_at')->nullable();
            $table->unsignedTinyInteger('violations')->default(0);
            $table->timestamp('last_violation_at')->nullable();
            $table->json('violation_log')->nullable();
            $table->json('question_order');
            $table->json('option_orders');
            $table->json('answers')->nullable();
            $table->decimal('score', 6, 2)->nullable();
            $table->timestamps();
            $table->unique(['exam_id', 'account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('exams');
    }
};
