<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HeatMap;

class HeatMapController extends Controller
{
    public function store(Request $request)
    {
        $payload = $request->all();
        $request->validate([
            "harga" => ["required"],
            "lat" => ["required"],
            "long" => ["required"]
        ]);

        HeatMap::query()->create($payload);
        return response()->json([
            "status" => true,
            "message" => "success",
            "data" => $payload
        ], 201);
    }

    public function index()
    {
        $heatmap = HeatMap::all();
        if(!$heatmap) {
            return response()->json([
                "status" => false,
                "message" => "failed",
                "data" => $heatmap
            ]);
        }

       $collection = $heatmap->map(function ($res) {
            $response['harga'] = $res['harga'];
            $response['latitude'] = (float)$res['lat'];
            $response['longitude'] = (float)$res['long'];
            return $response;
        })->reject(function ($res) {
            return empty($res);
        }); 

        return response()->json([
            "status" => true,
            "message" => "success",
            "data" => $collection
        ]);
    }
}
