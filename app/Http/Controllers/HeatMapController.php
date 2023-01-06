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

        $result = HeatMap::query()->create($payload);
        $resp = HeatMap::postHeatMap($result->id);

        return response()->json([
            "status" => true,
            "message" => "success",
            "data" => $resp
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
            $response['price'] = $res['harga'];
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
        $heatMap = HeatMap::getHeatMap();
        foreach($request->input('coords') as $coord) {
            $latitude = $coord['latitude'];
            $longitude = $coord['longitude'];
            $filter = $heatMap->filter(function($data) use ($latitude, $longitude){
                return $this->isInArea($latitude, $longitude, $data->latitude, $data->longitude, 1000);
            });
            array_push($result, $filter->values());
        }


        $result = collect($result)->map(function($data, $key) use ($request){
            $resp = [];
            $resp['coords'] = $data;
            $resp['center'] = $request->input('coords')[$key];
            if(sizeof($data) == 0) {
                $resp['average'] = 0;
            } else {
                $resp['average'] = collect($data)->map(fn($data)=>$data->price)->sum()/sizeof($data);
            }
            return $resp;
        });



        return response([
            'status' => true,
            'message' => '',
            'data' => $result
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

    public function testing(Request $request)
    {
        $result = [];
        $heatMap = HeatMap::all();
        foreach($request->input('coords') as $coord) {
            $latitude = $coord['latitude'];
            $longitude = $coord['longitude'];
            $filter = $heatMap->filter(function($data) use ($latitude, $longitude){
                return $this->isInArea($latitude, $longitude, $data->lat, $data->long, 1000);
            });
            array_push($result, $filter->values());
        }


        $result = collect($result)->map(function($data, $key) use ($request){
            $resp = [];
            $resp['coords'] = $data;
            $resp['center'] = $request->input('coords')[$key];
            if(sizeof($data) == 0) {
                $resp['average'] = 0;
            } else {
                $resp['average'] = collect($data)->map(fn($data)=>$data->harga)->sum()/sizeof($data);
            }
            return $resp;
        });



        return response([
            'status' => true,
            'message' => '',
            'data' => $result
        ]);

    }
}
