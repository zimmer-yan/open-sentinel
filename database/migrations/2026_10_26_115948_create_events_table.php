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
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('issue_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->char('event_id', 32);
            $table->timestampTz('occurred_at');
            $table->timestampTz('received_at');

            $table->string('platform')->nullable();
            $table->string('level', 16)->nullable();
            $table->text('message')->nullable();
            $table->string('environment')->nullable();
            $table->string('release')->nullable();

            $table->jsonb('payload');
            $table->timestamps();

            $table->unique(['project_id', 'event_id']);
            $table->index(['project_id', 'occurred_at']);
            $table->index(['issue_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
