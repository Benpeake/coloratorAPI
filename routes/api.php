<?php

use App\Http\Controllers\PaletteController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//PALETTE ROUTES
Route::middleware('auth:sanctum')->post('/palettes', [PaletteController::class, 'addPalette']);
Route::get('/palettes', [PaletteController::class, 'getAllPalettes']);
Route::get('/palettes/{user_id}', [PaletteController::class, 'getAllPalettesByUserID']);
Route::put('/palettes/vote/{palette_id}', [PaletteController::class, 'addVoteToPalette']);
Route::delete('/palettes/delete/{palette_id}', [PaletteController::class, 'softDeletePalette']);

//USER ROUTES
Route::post('/users/register', [UserController::class, 'registerUser']);
Route::put('users/update', [UserController::class, 'updateUser'])->middleware('auth:sanctum');
Route::delete('users/delete', [UserController::class, 'softDeleteUser'])->middleware('auth:sanctum');
