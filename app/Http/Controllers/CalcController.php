<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CalcController extends Controller
{
    /*
     * Referensi Google Maps API : https://developers.google.com/maps/documentation/distance-matrix/intro
     * Function yang saya buat tidak menggunakan package,
     * jika di perlukan menggunakan package saya merekomendasikan menggunakan : https://alexpechkarev.github.io/google-maps/
     * contok code pengimplementasiannya pada function : getCalcWithPackage()
     * dan route url api : http://localhost:8000/api/distance/getCalcWithPackage
     *
     * */
    protected $cost = 0;

    /*fungsi ini untuk merubah value dari m ke km*/

    private function convertToKmeters($val){
        return round($val*0.001,1, PHP_ROUND_HALF_UP);
    }

    public function calc(Request $request){
        $origins        = $request->input('origin.0.latitude').','.$request->input('origin.0.longitude');
        $destinations   = $request->input('destination.0.latitude').','.$request->input('destination.0.longitude');
        $type           = $request->input('vehicle.0.type');
        $distance_per_litre = $request->input('vehicle.0.distance_per_litre');
        $price_per_litre = $request->input('vehicle.0.price_per_litre');

        $response = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?origins='.$origins.'&destinations='.$destinations.'&mode='.$type.'&language=id&key=AIzaSyCXvfNd4OW96prtmovJBZp5AFmRUmcDCbk');

        $res = json_decode($response);
        foreach ($res->rows as $key => $value){
            foreach($value->elements as $val){
                if($val->status=='OK'){
                    $this->distance = $this->convertToKmeters($val->distance->value);
                    $this->duration = $val->duration->value;
                    $this->cost = floor(($this->distance / $distance_per_litre) * $price_per_litre);
                }
            }
        }

        return response()->json([
            'distance'  => $val->distance->text,
            'duration'  => $val->duration->text,
            'cost'      => $this->cost
        ]);
    }


    /*
     * Ini ada contoh perhitungan menggunakan package : https://alexpechkarev.github.io/google-maps/
     * */

    public function getCalcWithPackage(Request $request){
        /*Get Value Origins*/
        foreach ($request->origin as $valOr){
            $origins = $valOr['latitude'].','.$valOr['longitude'];
        }

        /*Get Value destination*/
        foreach ($request->destination as $valDes){
            $destinations = $valDes['latitude'].','.$valDes['longitude'];
        }
        /*Get Value vehicle*/
        foreach ($request->vehicle as $valVes){
            $type = $valVes['type'];
            $distance_per_litre = $valVes['distance_per_litre'];
            $price_per_litre = $valVes['price_per_litre'];
        }

        $response = \GoogleMaps::load('distancematrix')
            ->setParam([
                'origins'          => $origins,
                'destinations'     => $destinations,
                'mode'             => $type ,
            ])->get();
        $res = json_decode($response);
        foreach ($res->rows as $key => $value){
            foreach($value->elements as $val){
                if($val->status=='OK'){
                    $this->distance = $this->convertToKmeters($val->distance->value);
                    $this->duration = $val->duration->value;
                    $this->cost = floor(($this->distance / $distance_per_litre) * $price_per_litre);
                }
            }
        }

        return response()->json([
            'distance'  => $val->distance->text,
            'duration'  => $val->duration->text,
            'cost'      => $this->cost
        ]);
    }
}
