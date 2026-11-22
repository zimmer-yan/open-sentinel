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
        Schema::create('issues', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->char('fingerprint_hash', 64);
            $table->string('title');
            $table->string('type')->default('error');
            $table->string('status')->default('unresolved');

            $table->timestampTz('first_seen_at');
            $table->timestampTz('last_seen_at');
            $table->unsignedBigInteger('event_count')->default(0);

            $table->timestamps();

            $table->unique(['project_id', 'fingerprint_hash']);
            $table->index(['project_id', 'status', 'last_seen_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
