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
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('widget_type'); // 'chart', 'table', 'stats', 'metric'
            $table->string('title');
            $table->integer('x')->default(0);
            $table->integer('y')->default(0);
            $table->integer('width')->default(4);
            $table->integer('height')->default(4);
            $table->json('config')->nullable(); // Configuración del widget (tipo de gráfico, colores, etc.)
            $table->json('filters')->nullable(); // Filtros aplicados al widget
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_widgets');
    }
};
