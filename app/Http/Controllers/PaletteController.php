<?php

namespace App\Http\Controllers;

use App\Models\Palette;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaletteController extends Controller
{
    //ADD PALETTE
    public function addPalette(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:1|max:14',
            'hex_colors' => ['required', 'array', 'between:2,5'],
            'public' => 'sometimes|boolean',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $user = Auth::user();
        $newPalette = new Palette();
        $newPalette->name = $request->name;
        $newPalette->hex_colors = $request->hex_colors;
        $newPalette->public = true;
        $newPalette->likes = 0;

        if ($request->has('public')) {
            $newPalette->public = $request->input('public');
        }

        if ($user->palettes()->save($newPalette)) {
            return response()->json([
                'message' => 'Palette added',
            ], 201);
        }

        return response()->json([
            'message' => 'Palette not added, invalid data',
        ], 422);
    }

    //GET ALL PUBLIC PALETTES
    public function getAllPublicPalettes(Request $request)
    {
        $request->validate([
            'search' => 'string|max:500',
            'order_by' => 'string|in:newest,most_likes',
        ]);

        $search = $request->search;
        $orderBy = $request->order_by;

        $palettes = Palette::query()->where('public', true);

        if ($search) {
            $palettes->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', '%'.$search.'%')
                    ->orWhere('hex_colors', 'LIKE', '%"'.$search.'"%');
            });
        }

        if ($orderBy === 'most_likes') {
            $palettes->orderBy('likes', 'desc');
        } else {
            $palettes->latest();
        }

        $palettes = $palettes->get();

        return response()->json([
            'data' => $palettes,
            'message' => 'Public palettes successfully retrieved',
        ], 200);
    }

    //GET A SINGLE USERS PALETTES
    public function getAllPalettesByAuthUser(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'User not authenticated',
            ], 401);
        }

        $search = $request->search;
        $userPalettes = Palette::where('user_id', $user->id);

        if ($search) {
            $userPalettes->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', '%'.$search.'%')
                    ->orWhere('hex_colors', 'LIKE', '%"'.$search.'"%');
            });
        }

        $userPalettes = $userPalettes->latest()->get();

        return response()->json([
            'data' => $userPalettes,
            'message' => 'Palettes successfully retrieved',
        ], 200);
    }

    //GET A USERS LIKED PALETTES
    public function getLikedPalettes(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized. Invalid user.',
            ], 401);
        }

        $search = $request->search;
        $likedPalettes = $user->likedPalettes();

        if ($search) {
            $likedPalettes->where('name', 'LIKE', '%'.$search.'%')
                ->orWhereJsonContains('hex_colors', $search);
        }

        $likedPalettes = $likedPalettes->latest()->get();
        $likedPalettes = $likedPalettes->map(function ($palette) {
            unset($palette->pivot);

            return $palette;
        });

        return response()->json([
            'data' => $likedPalettes,
            'message' => 'Liked palettes successfully retrieved',
        ], 200);
    }

    // ADD LIKE TO PALETTE
    public function addLikeToPalette(Request $request, int $palette_id)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized. Invalid user.',
            ], 401);
        }

        $hasLiked = $user->likedPalettes()->where('palette_id', $palette_id)->exists();

        if (! $hasLiked) {
            $palette_toEdit = Palette::find($palette_id);

            if ($palette_toEdit) {
                $palette_toEdit->likes++;

                if ($palette_toEdit->save()) {
                    // Attach the palette to the user's liked palettes
                    $user->likedPalettes()->attach($palette_id);

                    return response()->json([
                        'message' => 'Palette successfully updated',
                    ], 200);
                }

                return response()->json([
                    'message' => 'Palette update not successful',
                ], 422);
            }

            return response()->json([
                'message' => 'Invalid palette ID',
            ], 401);
        }

        return response()->json([
            'message' => 'Palette already liked',
        ], 409);
    }

    // REMOVE LIKE FROM PALETTE
    public function removeLikeFromPalette(Request $request, int $palette_id)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized. Invalid user.',
            ], 401);
        }

        $hasLiked = $user->likedPalettes()->where('palette_id', $palette_id)->exists();

        if ($hasLiked) {
            $palette_toEdit = Palette::find($palette_id);

            if ($palette_toEdit) {
                $palette_toEdit->likes--;

                if ($palette_toEdit->save()) {
                    $user->likedPalettes()->detach($palette_id);

                    return response()->json([
                        'message' => 'Palette successfully updated (like removed)',
                    ], 200);
                }

                return response()->json([
                    'message' => 'Palette update not successful',
                ], 422);
            }

            return response()->json([
                'message' => 'Invalid palette ID',
            ], 401);
        }

        return response()->json([
            'message' => 'Palette not liked by the user',
        ], 409);
    }

    // SOFT DELETE PALETTE
    public function softDeletePalette(int $palette_id)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized. Invalid user.',
            ], 401);
        }

        $paletteToDelete = Palette::find($palette_id);

        if (! $paletteToDelete) {
            return response()->json([
                'message' => 'Palette not found',
            ], 404);
        }

        if ($paletteToDelete->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to delete this palette.',
            ], 403);
        }

        if ($paletteToDelete->delete()) {
            return response()->json([
                'message' => 'Palette removed',
            ], 200);
        }

        return response()->json([
            'message' => 'Error deleting palette',
        ], 500);
    }

    //MAKE PALETTE PRIVATE
    public function setPaletteToPrivate(Request $request, int $palette_id)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized. Invalid user.',
            ], 401);
        }

        $palette_toEdit = Palette::find($palette_id);

        if ($palette_toEdit->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to edit this palette.',
            ], 403);
        }

        if ($palette_toEdit->public == 0) {
            return response()->json([
                'message' => 'Palette is already private',
            ], 409);
        } elseif ($palette_toEdit->public == 1) {
            $palette_toEdit->public = 0;
        }

        if ($palette_toEdit->save()) {
            return response()->json([
                'message' => 'Palette set to private',
            ], 200);
        }

        return response()->json([
            'message' => 'Error saving palette status',
        ], 500);
    }

    //MAKE PALETTE PUBLIC
    public function setPaletteToPublic(Request $request, int $palette_id)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized. Invalid user.',
            ], 401);
        }

        $palette_toEdit = Palette::find($palette_id);

        if ($palette_toEdit->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to edit this palette.',
            ], 403);
        }

        if ($palette_toEdit->public == 1) {
            return response()->json([
                'message' => 'Palette is already public',
            ], 409);
        } elseif ($palette_toEdit->public == 0) {
            $palette_toEdit->public = 1;
        }

        if ($palette_toEdit->save()) {
            return response()->json([
                'message' => 'Palette set to public',
            ], 200);
        }

        return response()->json([
            'message' => 'Error saving palette status',
        ], 500);
    }
}
