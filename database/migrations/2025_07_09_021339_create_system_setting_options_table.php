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
        Schema::create('system_setting_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('system_setting_id')->constrained('system_settings')->onDelete('cascade');
            $table->string('value');
            $table->boolean('default')->default(false);
            $table->string('label')->nullable();
            $table->string('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_setting_options');
    }
};
