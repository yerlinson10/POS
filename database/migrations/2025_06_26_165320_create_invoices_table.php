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
            $table->foreignId('pos_session_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->string('discount_type')->nullable(); // 'percentage' or 'fixed'
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->string('payment_method', 20)->default('cash');
            $table->string('status', 20)->default('paid')->comment('quotation, paid, canceled');
            $table->softDeletes();
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
