<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $admin = Admin::query()->where("email", $request->input("email"))->first();


        // cek admin
        if($admin == null) {
            return response()->json([
                "status" => "error",
                "message" => "admin not found",
                "data" => []
            ], 404);
        }

        // cek password
        if(!Hash::check($request->input("password"), $admin->password)) {
            return response()->json([
                "status" => "error",
                "message" => "Invalid password",
                "data" => []
            ], 400);
        }
        

        return response()->json([
            "status" => "success",
            "message" => "Login success",
            "data" => $admin,
        ],200)->withCookie('username', $admin->username);
    } 

    function logout(Request $request) {
        Cookie::expire('username');
        return response()->json([
            "status" => "success",
            "message" => "Logout success",
            "data" => []
        ],200);
    }
}
