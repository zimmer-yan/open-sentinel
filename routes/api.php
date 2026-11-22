<?php

use OpenSentinel\Http\Controllers\Api\EnvelopeController;
use Illuminate\Support\Facades\Route;

Route::post('/{projectId}/envelope/', EnvelopeController::class)
    ->whereNumber('projectId')
    ->middleware('throttle:120,1');