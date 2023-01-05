<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeatMap extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $table = 'heatmap';

    public static function getHeatMap()
    {
        $heatMap = (new static)::get();
        $heatMap = collect($heatMap)->map(function ($data){
            $data['latitude'] = $data['lat'];
            $data['longitude'] = $data['long'];
            unset($data['lat']);
            unset($data['long']);

        return $data;
        });
    
        return $heatMap;
    }
}
