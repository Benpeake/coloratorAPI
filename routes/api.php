<?php

use App\Http\Controllers\PaletteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/palettes', [PaletteController::class, 'addPalette']);
Route::get('/palettes', [PaletteController::class, 'getAllPalletes']);
Route::get('/palettes/{user_id}', [PaletteController::class, ' getAllUsersPalettes']);
Route::put('/palettes/vote/{palette_id}', [PaletteController::class, 'addVoteToPalette']);
Route::put('/palettes/delete/{palette_id}', [PaletteController::class, 'softDelete']);
