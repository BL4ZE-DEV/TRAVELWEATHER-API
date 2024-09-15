<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\Null_;

use function Pest\Laravel\json;

class TravelWeatherController extends Controller
{
    public function tavelWeather(Request $request)
    {
        $request->validate([
            'location' => 'required',
            'start_date' => 'required|date_format:Y/m/d',
            'end_date' => 'required|date_format:Y/m/d'
        ]);

        $apiKey = env('OPENWEATHER_API_KEY');
        $client = new Client();
        $location = $request->location;
        $start_date = Carbon::parse($request->start_date);
        $end_date = Carbon::parse($request->end_date);

    try{
        $response = $client->get("http://api.openweathermap.org/data/2.5/forecast",[
                'query' => [
                    'q' => $location, 
                    'appid' => $apiKey, 
                    'units' => 'metric'
                ]
                ]);

            $weatherData = json_decode($response->getBody(),true);

            $recomendation = null;

                if($weatherData['temp'] > 31.8){
                    $recomendation = "it's hot";
                }else{
                    $recomendation = "it's cold";
                }
            $filtterdForcastList = [];

            foreach($weatherData['list'] as $forcast){

                $forcastDate = Carbon::parse($forcast['dt_txt']);

                if($forcastDate->between($start_date , $end_date)){
                    $filtterdForcastList[] = $forcast;
                }
            }
        

            return response()->json([
                'location' => $location,
                'recomnadtion' => $recomendation,
                'start_date' => $start_date->toDateString(),
                'end_date' => $end_date->toDateString(),
                'forecast' => $filtterdForcastList
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve weather data. ' . $e->getMessage()], 500);
        }
    }
}
