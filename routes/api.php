<?php

use App\Http\Controllers\IngestController;
use Illuminate\Support\Facades\Route;

Route::post('/ingest/{channel}', IngestController::class)
    ->name('ingest')
    ->middleware('signed');
