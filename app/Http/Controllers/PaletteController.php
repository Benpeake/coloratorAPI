<?php

namespace App\Http\Controllers;

use App\Models\Palette;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class PaletteController extends Controller
{
    //ADD PALETTE
    public function addPalette(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:14',
            'hex_colors' => ['required', 'array', 'between:2,5'],
            'public' => 'sometimes|boolean',
        ]);
    
        $user = Auth::user();
        $newPalette = new Palette();
        $newPalette->name = $request->name;
        $newPalette->hex_colors = $request->hex_colors;
        $newPalette->public = true; 
        $newPalette->votes = 0;
    
        if ($request->has('public')) {
            $newPalette->public = $request->input('public');
        }
    
        $user->palettes()->save($newPalette);
    
        return response()->json([
            'message' => 'Palette added',
        ]);
    }
    

    // GET ALL PALETTES
    public function getAllPalettes(Request $request)
    {
        $search = $request->search;
        $orderBy = $request->order_by;

        $palettes = Palette::where(function ($query) use ($search) {
            $query->where('name', 'LIKE', '%'.$search.'%');
        });

        // Order by most voted or newest
        if ($orderBy === 'most_voted') {
            $palettes->orderBy('votes', 'desc');
        } else {
            // Default to ordering by newest
            $palettes->latest();
        }

        $palettes = $palettes->get();

        return response()->json([
            'data' => $palettes,
            'message' => 'Palettes successfully retrieved',
        ]);
    }

    // GET A SINGLE USERS PALETTES
    public function getAllPalettesByUserID(Request $request, int $user_id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json([
                'message' => 'Invalid user ID',
            ], 404);
        }
    
        $search = $request->search;
        $userPalettes = Palette::where('user_id', $user_id);

        if ($search) {
            $userPalettes->where('name', 'LIKE', '%'.$search.'%');
        }

        $userPalettes = $userPalettes->latest()->get();

        return response()->json([
            'data' => $userPalettes,
            'message' => 'Palettes successfully retrieved',
        ]);
    }

    // ADD VOTE TO PALETTE
    public function addVoteToPalette(Request $request, int $palette_id)
    {
        $hasVoted = $request->session()->get("voted_palettes.$palette_id", false);

        if (! $hasVoted) {
            $palette_toEdit = Palette::find($palette_id);

            if ($palette_toEdit) {
                $palette_toEdit->votes++;

                if ($palette_toEdit->save()) {
                    // Marking the palette as voted in the session
                    $request->session()->put("voted_palettes.$palette_id", true);

                    return response()->json([
                        'message' => 'Palette successfully updated',
                    ]);
                }

                return response()->json([
                    'message' => 'Palette update not successful',
                ]);
            }

            return response()->json([
                'message' => 'Invalid palette ID',
            ]);
        }

        return response()->json([
            'message' => 'Already voted for this palette',
        ]);
    }

    //SOFT DELETE PALETTE
    public function softDeletePalette(int $palette_id)
    {
        $palette_toDelete = Palette::find($palette_id);

        if ($palette_toDelete->delete()) {
            return response()->json([
                'message' => "Palette $palette_id removed",
            ]);
        }

        return response()->json([
            'message' => 'error',
        ]);
    }
}
