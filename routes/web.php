<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

use function Sentry\captureException;

use OpenSentinel\Http\Controllers\OrganizationController;
use OpenSentinel\Http\Controllers\ProjectController;
use OpenSentinel\Http\Controllers\ProjectConnectionController;
use OpenSentinel\Http\Controllers\IssueController;
use OpenSentinel\Http\Controllers\LogController;
use OpenSentinel\Http\Controllers\Auth\AuthenticatedSessionController;
use OpenSentinel\Http\Controllers\Auth\RegisteredUserController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::view('/', 'open-sentinel::landing')->name('home');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'guest'])->group(function () {

    Route::controller(AuthenticatedSessionController::class)->group(function () {
        Route::get('/login', 'create')->name('login');
        Route::post('/login', 'store')->name('login.store');
    });

    Route::controller(RegisteredUserController::class)->group(function () {
        Route::get('/register', 'create')->name('register');
        Route::post('/register', 'store')->name('register.store');
    });

});

Route::post('/logout', [
    AuthenticatedSessionController::class,
    'destroy',
])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', function () {
        return redirect()->route('organizations.index');
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Organizations
    |--------------------------------------------------------------------------
    */

    Route::prefix('organizations')
        ->name('organizations.')
        ->controller(OrganizationController::class)
        ->group(function () {

            Route::get('/', 'index')->name('index');

            Route::post('/', 'store')->name('store');

            Route::get('/{organization}', 'show')
                ->name('overview');

        });

    /*
    |--------------------------------------------------------------------------
    | Organization Projects
    |--------------------------------------------------------------------------
    */

    Route::prefix('organizations/{organization}/projects')
        ->name('organizations.projects.')
        ->group(function () {

            Route::controller(ProjectController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
            });

            Route::get('/{project}/settings/connection', [
                ProjectConnectionController::class,
                'show',
            ])->name('connection');

            Route::post('/{project}/keys', [
                ProjectConnectionController::class,
                'rotate',
            ])->name('keys.rotate');

            Route::delete('/{project}/keys/{key}', [
                ProjectConnectionController::class,
                'revoke',
            ])->name('keys.revoke');

        });

    /*
    |--------------------------------------------------------------------------
    | Organization Issues
    |--------------------------------------------------------------------------
    */

    Route::prefix('organizations/{organization}/issues')
        ->name('organizations.issues.')
        ->controller(IssueController::class)
        ->group(function () {

            Route::get('/', 'index')->name('index');

            Route::get('/{issue}', 'show')->name('show');

            Route::patch('/{issue}/resolve', 'resolve')
                ->name('resolve');

            Route::patch('/{issue}/reopen', 'reopen')
                ->name('reopen');

        });

    /*
    |--------------------------------------------------------------------------
    | Organization Logs
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/organizations/{organization}/logs',
        [LogController::class, 'index']
    )->name('organizations.logs.index');

});

/*
|--------------------------------------------------------------------------
| Sentry Diagnostics
|--------------------------------------------------------------------------
|
| Keep these endpoints out of production.
|
*/

if (app()->environment(['local', 'testing'])) {

    Route::prefix('_debug/sentry')->middleware('auth')->group(function () {

        Route::get('/exception', function () {
            try {
                throw new RuntimeException('My first test event');
            } catch (RuntimeException $exception) {
                captureException($exception);
            }

            return response()->json([
                'success' => true,
                'message' => 'Test exception sent to Sentry.',
            ]);
        })->name('debug.sentry.exception');

        Route::get('/logs', function () {

            Log::info('Sentry log ingestion test', [
                'component' => 'test-route',
                'test_id' => 'log-test-001',
            ]);

            Log::warning('Sentry warning ingestion test', [
                'component' => 'test-route',
            ]);

            Log::error('Sentry error ingestion test', [
                'component' => 'test-route',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test logs sent.',
            ]);

        })->name('debug.sentry.logs');

    });

}
