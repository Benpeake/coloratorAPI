<?php

namespace App\Http\Controllers;

use App\Models\Palette;
use Illuminate\Http\Request;

class PaletteController extends Controller
{
    //ADD PALETTE
    public function addPalette(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:14',
            'hex_colors' => ['required', 'array', 'between:2,5'],
        ]);

        $newPalette = new Palette();
        $newPalette->name = $request->name;
        $newPalette->hex_colors = $request->hex_colors;

        if ($newPalette->save()) {
            return response()->json([
                'message' => 'Palette added',
            ]);
        }

        return response()->json([
            'message' => 'Palette not added',
        ]);
    }

    // GET ALL PALETTES (with search by name filter)
    public function getAllPalletes(Request $request)
    {
        $search = $request->search;
        $palettes = Palette::where(function ($query) use ($search) {
            $query->where('name', 'LIKE', '%'.$search.'%');
        })->get();

        return response()->json([
            'data' => $palettes,
            'message' => 'Palettes successfully retrieved',
        ]);
    }

    //EDIT PALETTE
    public function editPalleteById($id, Request $request)
    {
        $palette_toEdit = Palette::find($id);

        if ($palette_toEdit) {

            $request->validate([
                'name' => 'required|string|max:16',
                'hex_colors' => ['required', 'array', 'between:2,5'],
            ]);

            $palette_toEdit->name = $request->name;
            $palette_toEdit->hex_colors = $request->hex_colors;

            if ($palette_toEdit->save()) {
                return response()->json([
                    'message' => 'Palette succesfully updated',
                ]);
            }

            return response()->json([
                'message' => 'Palette updated not succesful',
            ]);

        }

        return response()->json([
            'message' => 'Invalid palette ID',
        ]);
    }

    //SOFT DELETE
    public function softDelete($id)
    {
        $palette_toDelete = Palette::find($id);

        if ($palette_toDelete->delete()) {
            return response()->json([
                'message' => "Palette $id removed",
            ]);
        }

        return response()->json([
            'message' => 'error',
        ]);
    }
}
