<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{

    public function getUsers()
    {
        $users = User::orderBy('created_at', 'desc')->withTrashed()->get()->load('roles:name');

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

    public function registerUser(RegisterRequest $req)
    {
        $data = $req->all();
        $data['password'] = Hash::make($data['password']);

        $newUser = User::create($data);

        // Check if the admin role is requested and assign it to the new user
        if ($req->has('admin') && $req->input('admin') == true) {
            $newUser->assignRole('admin');
        }

        // Check if the user role is requested and assign it to the new user
        if ($req->has('user') && $req->input('user') == true) {
            $newUser->assignRole('user');
        }

        // Check if the vendor role is requested and assign it to the new user
        if ($req->has('vendor') && $req->input('vendor') == true) {
            $newUser->assignRole('vendor');
        }

        return response()->json(['message' => 'Success user created', 'data' => $newUser], 201);
    }


    public function updateUser(Request $req, $id)
    {
        // Get the user with the given ID, including soft deleted users
        $user = User::withTrashed()->findOrFail($id);

        // Update users details 
        $user->name = $req->name;
        $user->email = $req->email;

        $oldAvatar = $user->avatar;

        $user->avatar = $req->avatar;
        $user->update();

        // deleteing old avatar
        if ($oldAvatar != "/fallback-avatar.png" && $oldAvatar !== $user->avatar) {
            Storage::delete(str_replace("/storage/", "public/", $oldAvatar));
        }

        // Create an array of all possible roles
        $roles = ['admin', 'vendor', 'user'];

        // Initialize a flag to check if all roles are false
        $allRolesFalse = true;

        // Check if the user is soft deleted
        if ($user->trashed()) {
            // If the user is soft deleted and there are true roles in the request, restore them
            foreach ($roles as $role) {
                if ($req->input($role)) {
                    $user->restore();
                    break;
                }
            }
        }

        // Iterate over each role and check if it was sent in the request
        foreach ($roles as $role) {
            if ($req->input($role)) {
                // If the role was sent in the request, add it to the new roles array and set the flag to false
                $newRoles[] = $role;
                $allRolesFalse = false;
            } else {
                // If the role was not sent in the request, remove it from the user
                $user->removeRole($role);
            }
        }

        // If all roles are false, soft delete the user and return a JSON response
        if ($allRolesFalse) {
            $user->delete();
            return response()->json(['message' => 'User deleted'], 200);
        }

        // Update the user's roles based on the new roles array
        $user->syncRoles($newRoles ?? []);

        // Return a JSON response indicating success and the updated user object
        return response()->json(['message' => 'Roles assigned successfully', 'data' => $user], 200);
    }

    public function deleteUser(User $user)
    {
        $user = User::where('id', $user->id)->delete();

        return response()->json(['message' => $user], 204);
    }

    public function restoreUser($id)
    {
        $user = User::withTrashed()->find($id);

        if ($user) {
            $user->restore();
            return response()->json(['message' => 'User restored successfully'], 200);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }
}
