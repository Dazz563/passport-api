<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\ForgotPasswordEmail;
use App\Http\Requests\LoginRequest;
use App\Models\PasswordResetRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\RegisterRequest;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $req)
    {
        $data = $req->all();
        $data['password'] = Hash::make($data['password']);

        $newUser = User::create($data);
        $newUser->assignRole('viewer');
        $token = $newUser->createToken('auth_token')->accessToken;

        return response()->json(['message' => 'Success', 'data' => $newUser, 'token' => $token], 201);
    }


    public function login(LoginRequest $req)
    {
        $user = User::where('email', $req->email)->first();

        if (!$user || !Hash::check($req->password, $user->password)) {
            throw ValidationException::withMessages([
                'message' => ['The provided credentials are incorrect']
            ]);
        }

        $token = $user->createToken('auth_token')->accessToken;

        return response()->json(['message' => 'Success', 'data' => $user, 'token' => $token], 200);
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

    public function forgotPassword(Request $req)
    {
        $user = User::where([
            ["email", $req->email]
        ])->first();

        if ($user) {
            #Create password reset request
            $resetToken = Str::uuid()->toString();
            $passwordResetRequest = PasswordResetRequest::create([
                'user_id' => $user->id,
                'reset_token' => $resetToken,
            ]);

            try {
                $redirectUrl = env('WEB_APP_URL') . 'reset-password/' . $resetToken;

                $data = [
                    'recipientName' => $user->name,
                    'textOne' => 'You have requested a password reset.',
                    'textTwo' => 'Click the button below to reset your password.',
                    'buttonText' => 'Reset password',
                    'buttonLink' => $redirectUrl,
                ];

                Mail::to($user->email)->send(new ForgotPasswordEmail($data));

                return response()->json(['data' => $user], 200);
            } catch (\Exception $e) {
                // Log::debug($e);
                return response()->json(['error' => 'An error occurred sending your reset password email.'], 200);
            }

            return response()->json(['data' => $passwordResetRequest], 201);
        } else {
            return response()->json(["error" => "No user found with that email"], 404);
        }
    }

    public function resetPasswordWithToken(Request $req)
    {
        // Fetch the password reset request by reset token from the database
        $passwordResetRequest = PasswordResetRequest::where('reset_token', $req->token)->first();

        // Default error message
        $errorMessage = "An error occurred, please try again later.";

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
                        $errorMessage = "Could not find an account associated with this reset token.";
                    }
                } else {
                    // Set error message for expired token
                    $errorMessage = "Password reset token has expired, please try again.";
                }
            } else {
                // Set error message for already used token
                $errorMessage = "This reset token has already been used.";
            }
        } else {
            // Set error message for invalid request
            $errorMessage = "Could not validate this password reset request.";
        }

        // Return a JSON response with error message
        return response()->json(["error" => $errorMessage], 500);
    }
}
