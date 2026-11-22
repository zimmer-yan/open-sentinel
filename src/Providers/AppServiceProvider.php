<?php

namespace OpenSentinel\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();
        RateLimiter::for('ingestion', function (Request $request) {
            return Limit::perMinute(120)
                ->by($request->ip() ?? 'unknown');
        });
        DB::listen(function (QueryExecuted $query) {
            if ($query->time >= 100) {
                Log::warning('Slow database query', [
                    'sql' => $query->sql,
                    'time_ms' => $query->time,
                ]);
            }
        });
    }
}
