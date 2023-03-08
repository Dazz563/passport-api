<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;

class AdminController extends Controller
{
    public function registerAdmin(RegisterRequest $req)
    {
        $data = $req->all();
        $data['password'] = Hash::make($data['password']);

        $newUser = User::create($data);
        $newUser->assignRole('admin');

        return response()->json(['message' => 'Success admin created', 'data' => $newUser], 201);
    }

    public function registerUser(RegisterRequest $req)
    {
        $data = $req->all();
        $data['password'] = Hash::make($data['password']);

        $newUser = User::create($data);
        $newUser->assignRole('user');

        return response()->json(['message' => 'Success user created', 'data' => $newUser], 201);
    }

    public function getUsers()
    {
        $users = User::get()->load('roles:name');

        $users->transform(function ($user) {
            $user->roles->transform(function ($role) {
                unset($role->pivot);
                return $role;
            });

            $user->roles = $user->roles->pluck('name');

            return $user;
        });

        return response()->json(['message' => 'Success', 'data' => $users], 200);
    }
}
