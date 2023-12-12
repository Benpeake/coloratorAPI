<?php

use App\Http\Controllers\PaletteController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//USER ROUTES
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth:sanctum')->group(function () {
    Route::put('/users/update', [UserController::class, 'updateUser']);
    Route::put('/users/update/email', [UserController::class, 'updateUserEmail']);
    Route::put('/users/update/name', [UserController::class, 'updateUserName']);
    Route::put('/users/update/password', [UserController::class, 'changePassword']);
    Route::delete('/users/delete', [UserController::class, 'softDeleteUser']);
    Route::post('/users/logout', [UserController::class, 'logoutUser']);
});
Route::post('/users/register', [UserController::class, 'registerUser']);
Route::post('/users/login', [UserController::class, 'loginUser']);

//PALETTE ROUTES
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/palettes/add', [PaletteController::class, 'addPalette']);
    Route::put('/palettes/like/{palette_id}', [PaletteController::class, 'addLikeToPalette']);
    Route::delete('/palettes/like/{palette_id}', [PaletteController::class, 'removeLikeFromPalette']);
    Route::get('/palettes', [PaletteController::class, 'getAllPalettesByAuthUser']);
    Route::get('/palettes/liked', [PaletteController::class, 'getLikedPalettes']);
    Route::delete('/palettes/delete/{palette_id}', [PaletteController::class, 'softDeletePalette']);
    Route::put('/palettes/status/private/{palette_id}', [PaletteController::class, 'setPaletteToPrivate']);
    Route::put('/palettes/status/public/{palette_id}', [PaletteController::class, 'setPaletteToPublic']);
});
Route::get('/palettes/all', [PaletteController::class, 'getAllPublicPalettes']);
