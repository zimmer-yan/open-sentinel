<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('exception_type')->nullable();
            $table->text('exception_value')->nullable();
            $table->json('stacktrace')->nullable();

            $table->string('trace_id', 32)->nullable()->index();
            $table->string('span_id', 16)->nullable();

            $table->string('runtime_name')->nullable();
            $table->string('runtime_version')->nullable();
            $table->string('server_name')->nullable();

            $table->string('sdk_name')->nullable();
            $table->string('sdk_version')->nullable();

            $table->json('contexts')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['trace_id']);

            $table->dropColumn([
                'exception_type',
                'exception_value',
                'stacktrace',
                'trace_id',
                'span_id',
                'runtime_name',
                'runtime_version',
                'server_name',
                'sdk_name',
                'sdk_version',
                'contexts',
            ]);
        });
    }
};
