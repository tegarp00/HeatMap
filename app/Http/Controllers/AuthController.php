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
        $redirect = $request->input('redirect');
        $admin = Admin::query()->where("email", $request->input("email"))->first();
        $token = $request->input('token');


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
       
        if($redirect != null) {
            return redirect($redirect);
        }

        return response()->json([
            "status" => "success",
            "message" => "}Login success",
            "data" => [
                'token' => encrypt($token),
            ],
        ],200);
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
