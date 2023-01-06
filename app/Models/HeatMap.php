<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class HeatMap extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $table = 'heatmap';

    public static function getHeatMap()
    {
        $heatMap = (new static)::get();
        $heatMap = collect($heatMap)->map(function ($data){
            $data['price'] = $data['harga'];
            $data['latitude'] = $data['lat'];
            $data['longitude'] = $data['long'];
            unset($data['harga']);
            unset($data['lat']);
            unset($data['long']);

        return $data;
        });
    
        return $heatMap;
    }

    public static function postHeatMap($id)
    {

        $resp = (new static)::where('id', $id)->first();
        $resp['price'] = $resp['harga'];
        $resp['latitude'] = $resp['lat'];
        $resp['longitude'] = $resp['long'];
        unset($resp['harga']);
        unset($resp['lat']);
        unset($resp['long']);

        return $resp;
    }
}
