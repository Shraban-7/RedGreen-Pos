<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = ["id"];

    protected $hidden = ["password", "remember_token"];

    protected function casts(): array
    {
        return [
            "email_verified_at" => "datetime",
            "password" => "hashed",
        ];
    }

    public static function generateUsername($phone = null): string
    {
        $last4 = $phone ? substr($phone, -4) : rand(1000, 9999);

        $random = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 2);

        $username = "u" . $last4 . $random;

        while (self::where("username", $username)->exists()) {
            $random = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 2);
            $username = "u" . $last4 . $random;
        }

        return $username;
    }
}
