<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing 'pending' invoices to 'quotation'
        DB::table('invoices')
            ->where('status', 'pending')
            ->update(['status' => 'quotation']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert 'quotation' invoices back to 'pending'
        DB::table('invoices')
            ->where('status', 'quotation')
            ->update(['status' => 'pending']);
    }
};
