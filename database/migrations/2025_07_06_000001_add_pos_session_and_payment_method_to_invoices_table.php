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
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('pos_session_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->enum('payment_method', ['cash', 'card', 'transfer', 'other'])->default('cash')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['pos_session_id']);
            $table->dropColumn(['pos_session_id', 'payment_method']);
        });
    }
};
