<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRegistrationRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Validator;

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
}
