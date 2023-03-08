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

        return response()->json(['message' => 'Success admin created', 'data' => $newUser], 201);
    }

    public function getUsers()
    {
        $users = User::get()->load('roles');

        return response()->json(['message' => 'Success', 'data' => $users], 200);
    }
}
