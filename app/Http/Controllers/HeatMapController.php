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
            $response['id'] = $res['id'];
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

    public function averageInCircle(Request $request)
    {
        $result = [];
        $heatMap = HeatMap::all();
        foreach($request->input('coords') as $coord) {
            $latitude = $coord['latitude'];
            $longitude = $coord['longitude'];
            $filter = $heatMap->filter(function($data) use ($latitude, $longitude){
                return $this->isInArea($latitude, $longitude, $data->lat, $data->long, 1000);
            });
            array_push($result, $filter);
        }
        return response([
            'status' => true,
            'message' => '',
            'data' => $result,
        ]);

    }

    public function isInArea($a, $b, $x, $y, $r)
    {

        $earth = 6378.137;
        $pi = pi();
        $m = (1/((2 * $pi / 360) * $earth)) / 1000;

        $distPoint = ($a - $x) * ($a - $x) + ($b - $y) * ($b - $y);
        $r = $r * $m;
        $r *= $r;
        if($distPoint < $r) {
            return true;
        }
        return false;

    }
}
