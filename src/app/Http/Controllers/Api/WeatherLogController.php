<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Models\WeatherLog;
use Illuminate\Http\JsonResponse;
use App\Services\OpenMeteoService;

class WeatherLogController extends Controller
{
    private readonly OpenMeteoService $openMeteoService;

    public function __construct(OpenMeteoService $openMeteoService)
    {
        $this->openMeteoService = $openMeteoService;
    }

    public function current(Request $request): JsonResponse
    {
        $locationId = $request->query('location_id');
        $now = now();

        if (is_null($locationId)) {
            return response()->json(['error' => 'location_id is required'], 400);
        }

        $weatherLog = WeatherLog::where('location_id', $locationId)
            ->where('forecast_time', '<=', $now)
            ->orderBy('forecast_time', 'desc')
            ->first();

        if (!$weatherLog) {
            return response()->json(['error' => 'Weather log not found for the specified location_id'], 404);
        }

        // 今日の最高・最低気温（hourlyレコードのMAX/MIN）
        $today = $now->toDateString();
        $temps = WeatherLog::where('location_id', $locationId)
            ->whereDate('forecast_time', $today)
            ->selectRaw('MAX(temperature_2m) as max_temp, MIN(temperature_2m) as min_temp')
            ->first();

            // 今日の24時間分グラフデータ
        $graphData = WeatherLog::where('location_id', $locationId)
            ->whereDate('forecast_time', $today)
            ->orderBy('forecast_time')
            ->get(['forecast_time', 'temperature_2m', 'precipitation', 'relative_humidity', 'weather_code'])
            ->map(fn($row) => [
                'time' => $row->forecast_time->format('H:i'),
                'temp' => $row->temperature_2m,
                'precipitation' => $row->precipitation,
                'humidity' => $row->relative_humidity,
                'weather_code' => $this->openMeteoService->getWeatherDescription($row->weather_code),
            ]);

        $location = Location::find($locationId);

        $weatherStatus = $this->openMeteoService->getWeatherDescription($weatherLog->weather_code);

        // return response()->json($weatherLog);
        return response()->json([
            'location' => $location ? $location->name : 'Unknown',
            'forecast_time' => $weatherLog->forecast_time,
            'current_temp' => $weatherLog->temperature_2m,
            'weather_code' => $weatherLog->weather_code,
            'weather_status' => $weatherStatus ?? 'Unknown',
            'max_temp' => $temps->max_temp,
            'min_temp' => $temps->min_temp,
            'graph_data' => $graphData,
            'last_updated' => $weatherLog->forecast_fetched_at,
        ]);
    }
}

// DB::table('weather_logs')
//     ->where('location_id', 1)
//     ->orderBy('forecast_time', 'desc')
//     ->limit(6)
//     ->get();
// forecast_time が1時間ごとに並んでいるか確認
