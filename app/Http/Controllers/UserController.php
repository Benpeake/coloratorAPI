<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //REGISTER USER
    public function registerUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($user) {
            return response()->json([
                'message' => 'User registered successful',
            ]);
        }

        return response()->json([
            'message' => 'User registration failed',
        ]);
    }

    //UPDATE USER DETAILS
    public function updateUser(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'string|max:20',
            'email' => 'email|unique:users,email,'.$user->id,
            'password' => 'string|min:6',
        ]);

        $updated = $user->update([
            'name' => $request->input('name', $user->name),
            'email' => $request->input('email', $user->email),
            'password' => $request->has('password') ? Hash::make($request->password) : $user->password,
        ]);

        if ($updated) {
            return response()->json([
                'message' => 'User update successful',
            ]);
        }

        return response()->json([
            'message' => 'User update failed',
        ]);
    }

    //SOFT DELETE USER (and their palettes)
    public function softDeleteUser(Request $request)
    {
        $user = Auth::user();

        $user->palettes()->delete();
        if ($user->delete()) {
            return response()->json([
                'message' => 'User deleted successfully',
            ]);
        }

        return response()->json([
            'message' => 'error',
        ]);
    }

        // LOG IN USER
        public function loginUser(Request $request)
        {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);
    
            if (Auth::attempt($credentials)) {
                // Authentication passed
                $user = Auth::user();
                $token = $user->createToken('authToken')->accessToken;
    
                return response()->json([
                    'message' => 'Login successful',
                    'access_token' => $token,
                ]);
            }
    
            // Authentication failed
            return response()->json([
                'message' => 'Login failed',
            ], 401);
        }
}
