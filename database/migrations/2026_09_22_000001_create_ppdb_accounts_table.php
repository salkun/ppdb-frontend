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
        Schema::create('ppdb_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nik', 20)->nullable()->index();
            $table->string('full_name', 150);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->boolean('is_active')->default(true);

            // Sync metadata ke FastAPI Master Server
            $table->string('remote_id', 100)->nullable()->index();
            $table->string('sync_status', 20)->default('pending')->index(); // pending, synced, failed
            $table->text('sync_message')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppdb_accounts');
    }
};
