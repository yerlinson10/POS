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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->decimal('subtotal', 10, 2)->default(0)->after('total_amount');
            $table->string('discount_type')->nullable()->after('subtotal'); // 'percentage' or 'fixed'
            $table->decimal('discount_value', 10, 2)->nullable()->after('discount_type');
            $table->decimal('total_amount', 12, 2);
            $table->string('status', 20)->default('paid')->comment('pending, paid, canceled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
