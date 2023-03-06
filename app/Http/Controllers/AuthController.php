<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $req)
    {
        $data = $req->all();
        $data['password'] = Hash::make($data['password']);

        $newUser = User::create($data);
        return response()->json(['message' => 'Success', 'data' => $newUser]);
    }


    public function login(LoginRequest $req)
    {
        $user = User::where('email', $req->email)->first();

        if (!$user || !Hash::check($req->password, $user->password)) {
            throw ValidationException::withMessages([
                'message' => ['The provided credentials are incorrect']
            ]);
        }

        return response()->json($user->createToken('auth_token')->accessToken);
        // return $user->createToken('auth_token')->accessToken;
    }

    public function getUser()
    {
        $user = Auth::user();
        return response()->json(['data' => $user]);
    }

    public function logout(Request $req)
    {
        $req->user()->tokens()->delete();
    }
}
