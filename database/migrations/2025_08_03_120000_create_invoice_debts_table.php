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
        Schema::create('invoice_debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_debt_id')->constrained()->cascadeOnDelete();
            $table->decimal('debt_amount', 12, 2)->nullable(); // Monto de la deuda asociada a esta factura (opcional)
            $table->text('notes')->nullable();
            $table->timestamps();

            // Índices
            $table->unique(['invoice_id', 'customer_debt_id']);
            $table->index('invoice_id');
            $table->index('customer_debt_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_debts');
    }
};
