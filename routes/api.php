<?php

use App\Http\Controllers\StreamController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\StreamParticipantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Stream routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/streams', [StreamController::class, 'index']);
    Route::post('/streams', [StreamController::class, 'create']);
    Route::get('/streams/{stream}', [StreamController::class, 'show']);
    Route::post('/streams/{stream}/start', [StreamController::class, 'start']);
    Route::post('/streams/{stream}/stop', [StreamController::class, 'stop']);
    Route::post('/streams/{stream}/join', [StreamController::class, 'join']);
    Route::post('/streams/{stream}/leave', [StreamController::class, 'leave']);
    Route::get('/streams/{stream}/webrtc-config', [StreamController::class, 'getWebRTCConfig']);
});

// Video routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/videos/upload', [VideoController::class, 'upload']);
    Route::get('/videos', [VideoController::class, 'index']);
    Route::get('/videos/{video}', [VideoController::class, 'show']);
    Route::delete('/videos/{video}', [VideoController::class, 'destroy']);
});

// Stream participant routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/streams/{stream}/invite', [StreamParticipantController::class, 'invite']);
    Route::post('/streams/{stream}/participants/{participant}/accept', [StreamParticipantController::class, 'accept']);
    Route::post('/streams/{stream}/participants/{participant}/decline', [StreamParticipantController::class, 'decline']);
});
