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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->string('type', 2);
            $table->string('name');
            $table->string('trade_name')->nullable();
            $table->string('document', 14)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 11)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Alvo das chaves estrangeiras compostas das tabelas filhas.
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'document']);
            $table->index(['tenant_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
