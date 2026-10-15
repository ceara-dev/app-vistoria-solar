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
        // A maior tabela do sistema: sem timestamps e sem auditoria por linha.
        Schema::create('trip_points', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->uuid('trip_id');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->timestamp('recorded_at', 3);
            $table->char('status', 1);
            $table->double('speed_ms')->default(0);

            $table->index(['trip_id', 'recorded_at']);

            // O banco recusa ponto ligado a rota de outra empresa.
            $table->foreign(['tenant_id', 'trip_id'])
                ->references(['tenant_id', 'id'])
                ->on('trips')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_points');
    }
};
