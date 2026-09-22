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
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('syncable_type', 100)->index(); // 'account' atau 'registration'
            $table->string('syncable_id', 50)->index();
            $table->string('action', 50); // create, update, verify_payment, accept, delete
            $table->string('api_endpoint', 255);
            $table->string('http_method', 10);
            $table->longText('request_payload')->nullable();
            $table->integer('response_status')->nullable();
            $table->longText('response_body')->nullable();
            $table->string('status', 20)->default('success')->index(); // success, failed, skipped
            $table->text('error_message')->nullable();
            $table->unsignedInteger('attempt')->default(1);
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
