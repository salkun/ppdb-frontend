<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppdb_registrations', function (Blueprint $table) {
            $table->string('sender_bank_name', 100)->nullable()->after('payment_method');
            $table->string('sender_name', 150)->nullable()->after('sender_bank_name');
        });
    }

    public function down(): void
    {
        Schema::table('ppdb_registrations', function (Blueprint $table) {
            $table->dropColumn(['sender_bank_name', 'sender_name']);
        });
    }
};
