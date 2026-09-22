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
        Schema::create('ppdb_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id')->index();
            $table->foreign('account_id')->references('id')->on('ppdb_accounts')->cascadeOnDelete();

            // Status Pembayaran & Berkas
            $table->string('payment_status', 30)->default('unpaid')->index(); // unpaid, pending_verification, paid, rejected
            $table->decimal('payment_amount', 12, 2)->default(0);
            $table->string('payment_proof_path', 255)->nullable();
            $table->timestamp('payment_verified_at')->nullable();
            $table->string('payment_verified_by', 150)->nullable();

            // Status Seleksi & Data Formulir Lengkap
            $table->string('registration_status', 30)->default('pending')->index(); // pending, accepted, rejected
            $table->longText('form_data')->nullable(); // Nested JSON formulir pendaftaran
            $table->string('student_id', 100)->nullable(); // ID Siswa jika sudah diterima di SIAKAD

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
        Schema::dropIfExists('ppdb_registrations');
    }
};
