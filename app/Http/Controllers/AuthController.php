<?php

namespace App\Http\Controllers;

use Hash;
use Validator;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UserRegistrationRequest;

class AuthController extends Controller
{
    public function register(UserRegistrationRequest $request)
    {
        $data = $request->validated();
        $data["password"] = Hash::make($data["password"]);

        if (!isset($data["username"]) || empty($data["username"])) {
            $data["username"] = User::generateUsername(
                $data["fullname"] ?? $data["email"],
            );
        }

        $user = User::create($data);

        return apiResponse(
            [
                "user" => new UserResource($user),
                "token" => $user->createToken("API TOKEN")->plainTextToken,
            ],
            "Signup successful",
        );
    }

    public function login(LoginRequest $request)
    {
        $login = $request->login;

        $user = User::where(function ($query) use ($login) {
            $query->where('email', $login)
                ->orWhere('username', $login)
                ->orWhere('phone', $login);
        })->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return apiResponse(
                ['message' => 'Invalid credentials'],
                'Authentication failed',
                401
            );
        }

        return apiResponse(
            [
                'user' => new UserResource($user),
                'token' => $user->createToken('API TOKEN')->plainTextToken,
            ],
            'Login successful'
        );
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();

        return successResponse('Logged out successfully.');
    }

    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required'
        ]);

        $user = User::where('refresh_token', $request->refresh_token)->first();

        $user->tokens()->delete();
        $newToken = $user->createToken('auth_token')->plainTextToken;

        $newRefreshToken = Str::random(64);
        $user->refresh_token = $newRefreshToken;
        $user->save();

        return apiResponse(
            [
                'user' => new UserResource($user),
                'token' => $user->createToken('API TOKEN')->plainTextToken,
            ],
            'Login successful'
        );

    }

}
