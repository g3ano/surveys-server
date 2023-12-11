<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function Register(RegisterRequest $request)
    {
        $data = $request->validated();
        /** @var User $user */
        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $token = $user->createToken('access')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function Login(LoginRequest $request)
    {
        $data = $request->validated();
        if (!Auth::attempt($data)) {
            return response()->json([
                'errors' => [
                    'password' => "password is incorrect, try again"
                ],
            ], 422);
        }

        /** @var User $user */
        $user = Auth::user();
        $token = $user->createToken('access')->plainTextToken;
        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function Logout()
    {
        /** @var User $user */
        $user = Auth::user();
        $user->currentAccessToken()->delete();
        return response()->json([
            'message' => 'logout successfully',
        ]);
    }
}
