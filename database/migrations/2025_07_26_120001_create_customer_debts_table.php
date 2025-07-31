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
        Schema::create('customer_debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Usuario que registró la deuda
            $table->decimal('original_amount', 12, 2); // Monto original de la deuda
            $table->decimal('remaining_amount', 12, 2); // Monto pendiente por pagar
            $table->decimal('paid_amount', 12, 2)->default(0); // Monto ya pagado
            $table->date('debt_date'); // Fecha en que se generó la deuda
            $table->date('due_date')->nullable(); // Fecha límite de pago
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Índices para optimización
            $table->index(['customer_id', 'status']);
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_debts');
    }
};
