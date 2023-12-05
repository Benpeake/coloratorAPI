<?php

use App\Http\Controllers\PaletteController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//USER ROUTES
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/users/register', [UserController::class, 'registerUser']);
Route::put('users/update', [UserController::class, 'updateUser'])->middleware('auth:sanctum');
Route::delete('users/delete', [UserController::class, 'softDeleteUser'])->middleware('auth:sanctum');

//PALETTE ROUTES
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/palettes', [PaletteController::class, 'addPalette']);
    Route::put('/palettes/like/{palette_id}', [PaletteController::class, 'addLikeToPalette']);
    Route::get('/palettes', [PaletteController::class, 'getAllPalettesByAuthUser']);
    Route::get('/palettes/liked', [PaletteController::class, 'getLikedPalettes']);
});
Route::get('/palettes/all', [PaletteController::class, 'getAllPalettes']);
Route::delete('/palettes/delete/{palette_id}', [PaletteController::class, 'softDeletePalette']);

