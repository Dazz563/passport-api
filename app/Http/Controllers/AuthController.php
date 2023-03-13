<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\ForgotPasswordEmail;
use App\Http\Requests\LoginRequest;
use App\Models\PasswordResetRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $req)
    {
        $data = $req->all();
        $data['password'] = Hash::make($data['password']);

        $newUser = User::create($data);
        $newUser->assignRole('user');
        $token = $newUser->createToken('auth_token')->accessToken;

        return response()->json(['message' => 'Success', 'data' => $newUser, 'token' => $token], 201);
    }

    public function login(LoginRequest $req)
    {
        $user = User::where('email', $req->email)->first();

        if (!$user) {
            return response()->json(["error" => "No user with that email exists"], 404);
        } else {
            $user->load('roles:name');

            if (!Hash::check($req->password, $user->password)) {
                return response()->json(["error" => "The provided credentials are incorrect"], 403);
            }

            $token = $user->createToken('auth_token')->accessToken;

            return response()->json(['message' => 'Success', 'data' => $user, 'token' => $token], 200);
        }
    }

    public function getUser()
    {
        $user = Auth::user()->load('roles:name');

        // remove all the other metadata
        $user->roles->map(function ($role) {
            unset($role->pivot);
            return $role;
        });

        $user->roles = $user->roles->pluck('name');

        return response()->json(['data' => $user]);
    }


    public function logout(Request $req)
    {
        $req->user()->tokens()->delete();
    }

    public function forgotPassword(Request $req)
    {

        $user = User::where([
            ["email", $req->email]
        ])->first();

        if ($user) {
            $resetToken = uniqid();
            PasswordResetRequest::create([
                'user_id' => $user->id,
                'reset_token' => $resetToken,
            ]);

            $redirectUrl = env('WEB_APP_URL') . 'reset-password/' . $resetToken;

            $data = [
                'recipientName' => $user->name,
                'textOne' => 'You have requested a password reset.',
                'textTwo' => 'Click the button below to reset your password.',
                'buttonText' => 'Reset password',
                'buttonLink' => $redirectUrl,
            ];

            Mail::to($user->email)->send(new ForgotPasswordEmail($data));

            return response()->json(['Success' => 'Email sent to user'], 200);
        } else {
            return response()->json(["error" => "No user found with that email"], 404);
        }
    }

    public function resetPasswordWithToken(Request $req)
    {

        // Fetch the password reset request by reset token from the database
        $passwordResetRequest = PasswordResetRequest::where('reset_token', $req->token)->first();

        // Check if password reset request exists
        if ($passwordResetRequest) {

            // Check if the reset request has not already been used
            if (!$passwordResetRequest->used) {

                // Check if the reset request was created within the last 2 hours
                $time = Carbon::now()->subHour(2);
                if ($passwordResetRequest->created_at >= $time) {

                    // Fetch the user associated with the password reset request
                    $user = User::find($passwordResetRequest->user_id);

                    // Check if user exists
                    if ($user) {

                        // Mark the password reset request as used
                        $passwordResetRequest->used = true;
                        $passwordResetRequest->save();

                        // Update user's password
                        $user->password = Hash::make($req->password);
                        $user->save();

                        // Return a JSON response with user data
                        return response()->json(["data" => $user], 200);
                    } else {
                        // Set error message for user not found
                        return response()->json(["error" => 'Could not find an account associated with this reset token.'], 404);
                    }
                } else {
                    // Set error message for expired token
                    return response()->json(["error" => 'Password reset token has expired, please try again.'], 419);
                }
            } else {
                // Set error message for already used token
                return response()->json(["error" => 'This reset token has already been used.'], 409);
            }
        } else {
            // Set error message for invalid request
            return response()->json(["error" => 'Could not validate this password reset request.'], 400);
        }
    }

    public function uploadRegisterAvatarImage(Request $req)
    {
        $req->validate([
            'file' => 'required|image',
        ]);

        $file = $req->file('file');
        $dt = Carbon::now();
        $filename = $dt->format('YmdHis') . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('public/avatars', $filename);

        return response()->json(["data" => $filename], 200);
    }
}
