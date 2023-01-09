<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HeatMap;

class HeatMapController extends Controller
{

    /**
     * This function is to add new property data
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function store(Request $request)
    {
        $payload = $request->all();
        $request->validate([
            "harga" => ["required"],
            "lat" => ["required"],
            "long" => ["required"]
        ]);

        $result = HeatMap::query()->create($payload);
        $resp = HeatMap::getHeatMap($result->id);

        return response()->json([
            "status" => true,
            "message" => "success",
            "data" => $resp
        ], 201);
    }

    /**
     * This function is to see all existing property data
     * 
     * @return array
     */
    public function index()
    {
        $heatmap = HeatMap::all();
        if(!$heatmap) {
            return response()->json([
                "status" => false,
                "message" => "failed",
                "data" => $heatmap
            ],404);
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
        ],200);
    }

    /**
     * looking for average from each circle., 
     * and At the same time get every property that is 2km away from 
     * the point specified in the circle
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function averageInCircle(Request $request)
    {
        $result = [];
        $heatMap = HeatMap::getHeatMaps();
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
            'message' => 'success',
            'data' => $result
        ]);

    }


    /**
     * Determine whether a point is in a circle
     *
     * @param  integer a - Latitude coordinate
     * @param  integer b - Longitude coordinate
     * @param  integer x - Circle center latitude
     * @param  integer y - Circle center longitude
     * @param  integer r - Radius in meter
     * @return boolean
     */
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
