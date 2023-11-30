<?php

use App\Http\Controllers\PaletteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/palettes', [PaletteController::class, 'addPalette']);
Route::get('/palettes', [PaletteController::class, 'getAllPalletes']);
Route::put('/palettes/edit/{id}', [PaletteController::class, 'editPalleteById']);
Route::put('/palettes/delete/{id}', [PaletteController::class, 'softDelete']);
