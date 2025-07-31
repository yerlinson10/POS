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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['income', 'expense']); // ingreso o egreso
            $table->string('category'); // categoría del pago: debt_payment, supplier_payment, other_income, other_expense
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 20)->default('cash'); // cash, card, bank_transfer, etc.
            $table->date('payment_date');
            $table->string('reference_number', 50)->nullable(); // Número de referencia o comprobante
            $table->text('description');
            
            // Relaciones polimórficas para asociar con diferentes entidades
            $table->nullableMorphs('payable'); // Puede ser customer_debt, supplier, etc.
            
            // Relaciones específicas
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_debt_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Usuario que registró el pago
            $table->foreignId('pos_session_id')->nullable()->constrained()->nullOnDelete();
            
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Para información adicional
            $table->softDeletes();
            $table->timestamps();

            // Índices para optimización
            $table->index(['type', 'category']);
            $table->index('payment_date');
            $table->index(['customer_id', 'type']);
            $table->index(['supplier_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
