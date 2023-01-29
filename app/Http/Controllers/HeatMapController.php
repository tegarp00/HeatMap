<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HeatMap;
use App\Helpers\HttpClient;
use App\Models\Currency;

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
        // catch all user input
        $payload = $request->all();
        // validate user input
        $request->validate([
            "harga" => ["required"],
            "lat" => ["required"],
            "long" => ["required"],
            "type" => ["required"],
            "area" => ["required"],
        ]);

        // insert and get validated data into DB
        $result = HeatMap::query()->create($payload);
        $resp = HeatMap::getHeatMap($result->id);

        
        // return response to client
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
        // get all property data in DB
        $heatmap = HeatMap::all();
        
        // if no data property
        if(!$heatmap) {
            return response()->json([
                "status" => false,
                "message" => "failed",
                "data" => $heatmap
            ],404);
        }

        // cast coordinate data type from string into float
       $collection = $heatmap->map(function ($res) {
            $response['id'] = $res['id'];
            $response['price'] = $res['harga'];
            $response['latitude'] = (float)$res['lat'];
            $response['longitude'] = (float)$res['long'];
            return $response;
        })->reject(function ($res) {
            return empty($res);
        }); 

        // return response to client
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

        // create array for collect data
        $result = [];

        // get all data property
        $heatMap = HeatMap::getHeatMaps();

        // loop user input
        foreach($request->input('coords') as $coord) {
            $latitude = $coord['latitude'];
            $longitude = $coord['longitude'];

            // filter property if in scope radius
            $filter = $heatMap->filter(function($data) use ($latitude, $longitude){
                return $this->isInArea($latitude, $longitude, $data->latitude, $data->longitude, 1000);
            });
            
            // append filtered property into result array
            array_push($result, $filter->values());
        }


        // map result to calculate average price
        $result = collect($result)->map(function($data, $key) use ($request){
            $resp = [];
            $resp['coords'] = $data;
            $resp['center'] = $request->input('coords')[$key];
        
            // if no data property in area average is 0
            if(sizeof($data) == 0) {
                $resp['average'] = 0;
            } else {
                // calculate total average price
                $resp['average'] = collect($data)->map(fn($data)=>$data->price)->sum()/sizeof($data);
            }
            // return result calculate
            return $resp;
        });


        // return response to client
        return response([
            'status' => true,
            'message' => 'success',
            'data' => $result
        ]);

    }

   /**
     * this function get detail area
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function areaDetail(Request $request)
    {
        $payload = [
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
        ];
        $heatMaps = HeatMap::getHeatMaps();
        $filter = $heatMaps->filter(function($heatMap) use ($payload){
            return $this->isInArea($payload['latitude'], $payload['longitude'], $heatMap->latitude, $heatMap->longitude, 1000);
        });

        return response([
            'status' => true,
            'message' => 'success',
            'data' => $filter->values()
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

        // Earth radius
        $earth = 6378.137;

        // initialize phi value 
        $pi = pi();

        // meter in map scale
        $m = (1/((2 * $pi / 360) * $earth)) / 1000;

        // distances beetwen two coordinates
        $distPoint = ($a - $x) * ($a - $x) + ($b - $y) * ($b - $y);
        
        // convert radius meter into map scale
        $r = $r * $m;
        
        // calculate squared radius
        $r *= $r;
        
        // if distances less than computed radius
        return $distPoint < $r;
    }

   /**
     * this function search geolocation information
     * By PlaceName
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function search(Request $request)
    {

        // catch user request query
        $query = [
            'q' => $request->input('q'),
            'format' => 'jsonv2',
        ];

        // initialize api url nominatin for search this area
        $url = url('https://nominatim.openstreetmap.org/search.php') . '?' . http_build_query($query, ', &');

        // request to nominatin api with user query
        $response = HttpClient::fetch(
            "GET",
            $url,
        );
        
        // return response to client
        return response([
            'status' => true,
            'message' => 'success',
            'data' => $response
        ]);

    }

   /**
     * this function search place information
     * By Coordinate
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function reverseArea(Request $request)
    {
        // catch user request query
        $queryN = [
            'lat' => $request->input('lat'),
            'lon' => $request->input('lon'),
            'format' => 'jsonv2',
        ];

        // catch user request query
        $queryOTM = [
            'apikey' => env('OPENTRIPMAP_TOKEN'),
            'lat' => $request->input('lat'),
            'lon' => $request->input('lon'),
            'radius' => $request->input('radius'),
        ];

        // initialize api url nominatim for reverse in area
        $urlNominatim = url('https://nominatim.openstreetmap.org/reverse.php') . '?' . http_build_query($queryN, ',&');

        // initialize api url open tripMap
        $urlOpenTripMap = url('https://api.opentripmap.com/0.1/en/places/radius') . '?' . http_build_query($queryOTM, ',&');

        // request to nominatim api with user query
        $response = HttpClient::fetch(
            "GET",
            $urlNominatim,
        );

        // request to open tripMap api with user query
        $responseOTM = HttpClient::fetch(
            "GET",
            $urlOpenTripMap,
        );

        $response['popular'] = $responseOTM;
        
        // return response to client 
        return response([
            'status' => true,
            'message' => 'success',
            'data' => $response
        ]);

    }

   /**
     * this function search place information
     * By Coordinate
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function reverseAreaPopular(Request $request)
    {
        // catch user request query
        $queryN = [
            'lat' => $request->input('lat'),
            'lon' => $request->input('lon'),
            'format' => 'jsonv2',
        ];

        // catch user request query
        $queryFSQ = [
            'll' => $request->input('lat') . ',' . $request->input('lon'),
            'sort' => 'distance',
            'limit' => 50,
            'radius' => $request->input('radius'),
        ];

        // initialize api url nominatim for reverse in area
        $urlNominatim = url('https://nominatim.openstreetmap.org/reverse.php') . '?' . http_build_query($queryN, ',&');

        // initialize api url open tripMap
        $headers = [];
        $urlFSQ = url('https://api.foursquare.com/v3/places/search') . '?' . http_build_query($queryFSQ, ',&');
        $headers['Authorization'] = env('FSQ_TOKEN');

        // request to nominatim api with user query
        $response = HttpClient::fetch(
            "GET",
            $urlNominatim,
        );

        // request to open tripMap api with user query
        $responseFSQ = HttpClient::fetch(
            "GET",
            $urlFSQ,
            $headers
        );

        $response['popular'] = $responseFSQ;
        
        // return response to client 
        return response([
            'status' => true,
            'message' => 'success',
            'data' => $response
        ]);

    }

  // =============== Experimental Feature ====================== //
    /**
     * this function search place information
     * By Coordinate
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function getCurrencies()
    {
      $currencies = Currency::query()->get();
      return response([
            'status' => true,
            'message' => 'success',
            'data' => $currencies
        ]);
    }

    public function updateCurrencies()
    {
         
        $query = [
            'base' => "idr",
        ];

        $url = url('https://api.exchangerate.host/latest') . '?' . http_build_query($query, ',&');

        $response = HttpClient::fetch(
            "GET",
            $url,
        );

        foreach($response['rates'] as $k => $v) {
          $getRates = Currency::query()->where('name', $k)->first();
          $getRates->fill([$k=>$v]);
          $getRates->save();
        }

        
        return response([
            'status' => true,
            'message' => 'success',
            'data' => Currency::query()->get(),
        ]);
    }

}
