<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Ghanja API v2',
        'status' => 'running',
    ]);
});