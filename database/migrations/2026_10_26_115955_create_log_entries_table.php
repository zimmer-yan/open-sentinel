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
        Schema::create('log_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('batch_id', 64)->nullable();
            $table->timestampTz('occurred_at');
            $table->timestampTz('received_at');

            $table->string('level', 16);
            $table->text('message');
            $table->string('logger')->nullable();
            $table->string('environment')->nullable();
            $table->string('release')->nullable();
            $table->jsonb('attributes')->default('{}');

            $table->timestamps();

            $table->index(['project_id', 'occurred_at']);
            $table->index(['project_id', 'level', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_entries');
    }
};
