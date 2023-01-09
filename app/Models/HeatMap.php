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

    /**
     * this function get all heatmap data ., at the same time change data response
     *
     * @return array
     */
    public static function getHeatMaps()
    {
        $heatMaps = (new static)::get();
        $heatMaps = collect($heatMaps)->map(function ($heatMap){
            $heatMap['price'] = $heatMap['harga'];
            $heatMap['latitude'] = $heatMap['lat'];
            $heatMap['longitude'] = $heatMap['long'];
            unset($heatMap['harga']);
            unset($heatMap['lat']);
            unset($heatMap['long']);

            return $heatMap;
        });
    
        return $heatMaps;
    }

    /**
     * This function changes data response when adding new property data
     *
     * @return array
     */
    public static function getHeatMap($id)
    {

        $heatMap = (new static)::where('id', $id)->first();
        $heatMap['price'] = $heatMap['harga'];
        $heatMap['latitude'] = $heatMap['lat'];
        $heatMap['longitude'] = $heatMap['long'];
        unset($heatMap['harga']);
        unset($heatMap['lat']);
        unset($heatMap['long']);

        return $heatMap;
    }
}
